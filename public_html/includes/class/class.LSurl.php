<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * http://ldapsaisie.labs.libre-entreprise.org
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

/**
 * URL Routing Manager for LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSurl {

  /*
   * Configured URL patterns :
   *
   *   array (
   *     '[URL pattern]' => '[handler]',
   *     [...]
   *   )
   *
   *  Example :
   *
   *   array (
   *     '|get/(?P<name>[a-zA-Z0-9]+)$|' => array (
   *       'handler' => 'get',
   *       'authenticated' => true,
   *     ),
   *     '|get/all$|' => => array (
   *       'handler' => 'get_all',
   *       'authenticated' => true,
   *     ),
   *   )
   *
   */
  private static $patterns = array();

  // Rewrited request param
  const REWRITED_REQUEST_PARAM = 'REQUESTED_URL';

  /**
   * Add an URL pattern
   *
   * @param[in] $pattern        string        The URL pattern (required)
   * @param[in] $handler        callable      The URL pattern handler (must be callable, required)
   * @param[in] $authenticated  boolean       Permit to define if this URL is accessible only for authenticated users (optional, default: true)
   * @param[in] $override       boolean       Allow override if a command already exists with the same name (optional, default: false)
   **/
  public static function add_handler($pattern, $handler=null, $authenticated=true, $override=true) {
    if (is_array($pattern)) {
      if (is_null($handler))
        foreach($pattern as $p => $h)
          self :: add_handlers($p, $h, $override);
      else
        foreach($pattern as $p)
          self :: add_handlers($p, $handler, $override);
    }
    else {
      if (!isset(self :: $patterns[$pattern])) {
        self :: $patterns[$pattern] = array(
          'handler' => $handler,
          'authenticated' => $authenticated,
        );
      }
      elseif ($override) {
        LSlog :: debug("URL : override pattern '$pattern' with handler '$handler' (old handler = '".self :: $patterns[$pattern]."')");
        self :: $patterns[$pattern] = array(
          'handler' => $handler,
          'authenticated' => $authenticated,
        );
      }
      else {
        LSlog :: debug("URL : pattern '$pattern' already defined : do not override.");
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
      LSlog :: fatal(_("Fail to determine the requested URL."));
      exit();
    }
    if (!is_array(self :: $patterns)) {
      LSlog :: fatal('No URL patterns configured !');
      exit();
    }

    LSlog :: debug("URL : current url = '$current_url'");
    LSlog :: debug("URL : check current url with the following URL patterns :\n - ".implode("\n - ", array_keys(self :: $patterns)));
    foreach (self :: $patterns as $pattern => $handler_infos) {
      $m = self :: url_match($pattern, $current_url);
      if (is_array($m)) {
        $request = new LSurlRequest($current_url, $handler_infos, $m);
        // Reset last redirect
        if (isset($_SESSION['last_redirect']))
          unset($_SESSION['last_redirect']);
        LSlog :: debug("URL : result :\n".varDump($request, 1));
        return $request;
      }
    }
    if (!is_null($default_url)) {
      LSlog :: debug("Current URL match with no pattern. Redirect to default URL ('$default_url')");
      self :: redirect($default_url);
      exit();
    }
    LSlog :: debug("Current URL match with no pattern. Use error 404 handler.");
    return new LSurlRequest(
      $current_url,
      array(
        'handler' => array('LSurl', 'error_404'),
        'authenticated' => false,
      )
    );
  }

  /**
   * Check if the current requested URL match with a specific pattern
   *
   * @param[in] $pattern string The URL pattern
   * @param[in] $current_url string|false The current URL (optional)
   *
   * @retval array|false The URL info if pattern matched, false otherwise.
   **/
  private static function url_match($pattern, $current_url=false) {
    if ($current_url === false) {
      $current_url = self :: get_current_url();
      if (!$current_url) return False;
    }
    if (preg_match($pattern, $current_url, $m)) {
      LSlog :: debug("URL : Match found with pattern '$pattern' :\n\t".str_replace("\n", "\n\t", print_r($m, 1)));
      return $m;
    }
    return False;
  }

  /**
   * Get the current requested URL
   *
   * @retval string The current requested URL
   **/
  public static function get_current_url() {
    if (array_key_exists(self :: REWRITED_REQUEST_PARAM, $_REQUEST))
      return $_REQUEST[self :: REWRITED_REQUEST_PARAM];
    LSlog :: warning('LSurl : Rewrite request param not present, try to detect current URL.');
    return self :: detect_current_url();
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

    if (preg_match('#^https?://#',$go)) {
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
      LSlog :: fatal(_("Fail to determine the requested URL (loop detected)."));
    else
      $_SESSION['last_redirect'] = $url;

    LSlog :: debug("redirect($go) => Redirect to : <$url>");
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
    LSsession :: setTemplate('error_404.tpl');
    LSsession :: displayTemplate();
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
    $request = self :: get_request($default_url);

    if (!is_callable($request -> handler)) {
      LSlog :: error("URL handler function ".$request -> handler."() does not exists !");
      LSlog :: fatal("This request could not be handled.");
    }

    if (class_exists('LStemplate'))
      LStemplate :: assign('request', $request);

    // Check authentication (if need)
    if($request -> authenticated && ! LSsession :: startLSsession()) {
      LSsession :: displayTemplate();
      return true;
    }

    try {
      return call_user_func($request -> handler, $request);
    }
    catch (Exception $e) {
      LSlog :: exception($e, "An exception occured running URL handler function ".$request -> handler."()");
      LSlog :: fatal("This request could not be processed correctly.");
    }
  }

  /**
   * Helpers to detect current requested URL
   */

  /*
   * Try to detect current requested URL
   *
   * @retval string|false The current request URL or false if detection fail
   **/
  private static function detect_current_url() {
    LSlog :: debug("URL : request URI = '".$_SERVER['REQUEST_URI']."'");

    $base = self :: get_rewrite_base();
    LSlog :: debug("URL : rewrite base = '$base'");

    if ($_SERVER['REQUEST_URI'] == $base)
      return '';

    if (substr($_SERVER['REQUEST_URI'], 0, strlen($base)) != $base) {
      LSlog :: error("URL : request URI (".$_SERVER['REQUEST_URI'].") does not start with rewrite base ($base)");
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
    if (preg_match('|^https?://[^/]+/(.*)$|', $public_root_url, $m))
      return '/'.self :: remove_trailing_slash($m[1]).'/';
    elseif (preg_match('|^/(.+)$|', $public_root_url, $m))
      return '/'.self :: remove_trailing_slash($m[1]).'/';
    return '/';
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
