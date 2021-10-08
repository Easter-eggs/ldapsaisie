<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
 *
 * Author: See AUTHORS file in top-level directory.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

******************************************************************************/

LSsession :: loadLSclass('LSurlRequest');
LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * URL Routing Manager for LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSurl extends LSlog_staticLoggerClass {

  // Current request (defined at least current URL have been analyse by LSurl :: handle_request())
  public static $request = null;

  /*
   * Configured URL patterns :
   *
   * Example :
   *
   *   array (
   *     '|get/(?P<name>[a-zA-Z0-9]+)$|' => array (
   *       'handler' => 'get',
   *       'authenticated' => true,
   *       'api_mode' => false,
   *       'methods' => array('GET'),
   *     ),
   *     '|get/all$|' => => array (
   *       'handler' => 'get_all',
   *       'authenticated' => true,
   *       'api_mode' => false,
   *       'methods' => array('GET', 'POST'),
   *     ),
   *   )
   *
   */
  private static $patterns = array();

  /**
   * Add an URL pattern
   *
   * @param[in] $pattern        string        The URL pattern (required)
   * @param[in] $handler        callable      The URL pattern handler (must be callable, required)
   * @param[in] $authenticated  boolean       Permit to define if this URL is accessible only for authenticated users (optional, default: true)
   * @param[in] $override       boolean       Allow override if a command already exists with the same name (optional, default: false)
   * @param[in] $api_mode       boolean       Enable API mode (optional, default: false)
   * @param[in] $methods        array|null    HTTP method (optional, default: array('GET', 'POST'))
   **/
  public static function add_handler($pattern, $handler=null, $authenticated=true, $override=true, $api_mode=false, $methods=null) {
    if (is_null($methods))
      $methods = array('GET', 'POST');
    else
      $methods = ensureIsArray($methods);
    if (is_array($pattern)) {
      if (is_null($handler))
        foreach($pattern as $p => $h)
          self :: add_handler($p, $h, $authenticated, $override, $api_mode, $methods);
      else
        foreach($pattern as $p)
          self :: add_handler($p, $handler, $authenticated, $override, $api_mode, $methods);
    }
    else {
      if (!isset(self :: $patterns[$pattern])) {
        self :: $patterns[$pattern] = array(
          'handler' => $handler,
          'authenticated' => $authenticated,
          'api_mode' => $api_mode,
          'methods' => $methods,
        );
      }
      elseif ($override) {
        self :: log_debug("URL : override pattern '$pattern' with handler '$handler' (old handler = '".self :: $patterns[$pattern]."')");
        self :: $patterns[$pattern] = array(
          'handler' => $handler,
          'authenticated' => $authenticated,
          'api_mode' => $api_mode,
          'methods' => $methods,
        );
      }
      else {
        self :: log_debug("URL : pattern '$pattern' already defined : do not override.");
      }
    }
  }

  /**
   * Interprets the requested URL and return the corresponding LSurlRequest object
   *
   * @param[in] $default_url string|null The default URL if current one does not
   *                                     match with any configured pattern.
   *
   * @retval LSurlRequest The LSurlRequest object corresponding to the the requested URL.
   **/
  private static function get_request($default_url=null) {
    $current_url = self :: get_current_url();
    if (is_null($current_url)) {
      self :: log_fatal(_("Fail to determine the requested URL."));
      exit();
    }
    if (!is_array(self :: $patterns)) {
      self :: log_fatal(_("No URL patterns configured !"));
      exit();
    }

    self :: log_debug("URL : current url = '$current_url'");
    self :: log_debug("URL : check current url with the following URL patterns :\n - ".implode("\n - ", array_keys(self :: $patterns)));
    foreach (self :: $patterns as $pattern => $handler_infos) {
      $m = self :: url_match($pattern, $current_url, $handler_infos['methods']);
      if (is_array($m)) {
        $request = new LSurlRequest($current_url, $handler_infos, $m);
        // Reset last redirect
        if (isset($_SESSION['last_redirect']))
          unset($_SESSION['last_redirect']);
        self :: log_debug("URL : result :\n".varDump($request, 1));
        return $request;
      }
    }
    if (!is_null($default_url)) {
      self :: log_debug("Current URL match with no pattern. Redirect to default URL ('$default_url')");
      self :: redirect($default_url);
      exit();
    }
    // Error 404
    $api_mode = (strpos($current_url, 'api/') === 0);
    self :: log_debug("Current URL match with no pattern. Use error 404 handler (API mode=$api_mode).");
    return new LSurlRequest(
      $current_url,
      array(
        'handler' => array('LSurl', 'error_404'),
        'authenticated' => false,
        'api_mode' => $api_mode,
      )
    );
  }

  /**
   * Check if the current requested URL match with a specific pattern
   *
   * @param[in] $pattern string The URL pattern
   * @param[in] $current_url string|false The current URL (optional)
   * @param[in] $methods array|null HTTP method (optional, default: no check)
   *
   * @retval array|false The URL info if pattern matched, false otherwise.
   **/
  private static function url_match($pattern, $current_url=false, $methods=null) {
    if ($methods && !in_array($_SERVER['REQUEST_METHOD'], $methods))
      return false;
    if ($current_url === false) {
      $current_url = self :: get_current_url();
      if (!$current_url) return False;
    }
    if (preg_match($pattern, $current_url, $m)) {
      self :: log_debug("URL : Match found with pattern '$pattern' :\n\t".str_replace("\n", "\n\t", print_r($m, 1)));
      return $m;
    }
    return False;
  }

  /**
   * Get the public absolute URL
   *
   * @param[in] $relative_url string|null Relative URL to convert (Default: current URL)
   *
   * @retval string The public absolute URL
   **/
  public static function get_public_absolute_url($relative_url=null) {
    if (!is_string($relative_url))
      $relative_url = self :: get_current_url();

    $public_root_url = LSconfig :: get('public_root_url', '/', 'string');

    if ($public_root_url[0] == '/') {
      self :: log_debug("LSurl :: get_public_absolute_url($relative_url): configured public root URL is relative ($public_root_url) => try to detect it from current request infos.");
      $public_root_url = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'?'s':'').'://'.$_SERVER['HTTP_HOST'].$public_root_url;
      self :: log_debug("LSurl :: get_public_absolute_url($relative_url): detected public_root_url: $public_root_url");
    }

    $url = self :: remove_trailing_slash($public_root_url)."/$relative_url";
    self :: log_debug("LSurl :: get_public_absolute_url($relative_url): result = $url");
    return $url;
  }

  /**
   * Trigger redirect to specified URL (or homepage if omited)
   *
   * @param[in] $go string|false The destination URL
   *
   * @retval void
   **/
  public static function redirect($go=false) {
    $public_root_url = LSconfig :: get('public_root_url', '/', 'string');
    if ($go===false)
      $go = "";

    if (preg_match('#^(https?:)?//#',$go)) {
      $url = $go;
    }
    else {
      // Check $public_root_url end
      if (substr($public_root_url, -1)=='/') {
        $public_root_url=substr($public_root_url, 0, -1);
      }
      $url="$public_root_url/$go";
    }

    // Prevent loop
    if (isset($_SESSION['last_redirect']) && $_SESSION['last_redirect'] == $url)
      self :: log_fatal(_("Fail to determine the requested URL (loop detected)."));
    else
      $_SESSION['last_redirect'] = $url;

    self :: log_debug("redirect($go) => Redirect to : <$url>");
    header("Location: $url");

    // Set & display template
    LStemplate :: assign('url', $url);
    LStemplate :: display('redirect.tpl');
    exit();
  }

  /**
   * Error 404 handler
   *
   * @param[in] $request LSurlRequest|null The request (optional, default: null)
   *
   * @retval void
   **/
  public static function error_404($request=null) {
    http_response_code(404);
    $error = _("The requested page was not found.");
    if (LSsession :: getAjaxDisplay() || ($request && $request->api_mode)) {
      LSerror :: addErrorCode(null, $error);
      LSsession :: displayAjaxReturn();
    }
    else {
      LStemplate :: assign('error', $error);
      LSsession :: setTemplate('error.tpl');
      LSsession :: displayTemplate();
    }
  }

  /**
   * Handle the current requested URL
   *
   * @param[in] $default_url string|null The default URL if current one does not
   *                                     match with any configured pattern.
   *
   * @retval void
   **/
  public static function handle_request($default_url=null) {
    self :: $request = self :: get_request($default_url);

    if (!is_callable(self :: $request -> handler)) {
      self :: log_error(
        "URL handler function ".self :: $request -> handler."() does not exists !"
      );
      self :: log_fatal(_("This request could not be handled."));
    }

    if (self :: $request -> api_mode)
      LSsession :: setApiMode();
    elseif (class_exists('LStemplate'))
      LStemplate :: assign('request', self :: $request);

    // Check authentication (if need)
    if(self :: $request -> authenticated && ! LSsession :: startLSsession()) {
      LSsession :: displayTemplate();
      return true;
    }

    try {
      return call_user_func(self :: $request -> handler, self :: $request);
    }
    catch (Exception $e) {
      self :: log_exception(
        $e, "An exception occured running URL handler function ".
        getCallableName(self :: $request -> handler)
      );
      self :: log_fatal(_("This request could not be processed correctly."));
    }
  }

  /**
   * Helpers to retrieve current requested URL
   */

  /*
   * Try to detect current requested URL and return it
   *
   * @retval string|false The current request URL or false if detection fail
   **/
  private static function get_current_url() {
    self :: log_debug("URL : request URI = '".$_SERVER['REQUEST_URI']."'");

    $base = self :: get_rewrite_base();
    self :: log_debug("URL : rewrite base = '$base'");

    if ($_SERVER['REQUEST_URI'] == $base)
      return '';

    if (substr($_SERVER['REQUEST_URI'], 0, strlen($base)) != $base) {
      self :: log_error("URL : request URI (".$_SERVER['REQUEST_URI'].") does not start with rewrite base ($base)");
      return False;
    }

    $current_url = substr($_SERVER['REQUEST_URI'], strlen($base));

    // URL contain params ?
    $params_start = strpos($current_url, '?');
    if ($params_start !== false) {
      // Params detected, remove it

      // No url / currrent url start by '?' ?
      if ($params_start == 0)
              return '';
      else
        return substr($current_url, 0, $params_start);
    }

    return $current_url;
  }

  /**
   * Try to detect rewrite base from public root URL
   *
   * @retval string The detected rewrite base
   **/
  private static function get_rewrite_base() {
    $public_root_url = LSconfig :: get('public_root_url', '/', 'string');
    $rewrite_base = '/';
    if (preg_match('|^https?://[^/]+(/.*)$|', $public_root_url, $m))
      $rewrite_base = $m[1];
    elseif (preg_match('|^(/.+)$|', $public_root_url, $m))
      $rewrite_base = $m[1];
    if ($rewrite_base != '/')
      return self :: remove_trailing_slash($rewrite_base).'/';
    return $rewrite_base;
  }

  /**
   * Remove trailing slash in specified URL
   *
   * @param[in] $url string The URL
   *
   * @retval string The specified URL without trailing slash
   **/
  private static function remove_trailing_slash($url) {
    if ($url == '/')
      return $url;
    elseif (substr($url, -1) == '/')
      return substr($url, 0, -1);
    return $url;
  }

}
