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

LSsession :: loadLSclass('LSlog_staticLoggerClass');
/**
 * URL request abstraction use by LSurl
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSurlRequest extends LSlog_staticLoggerClass {

  // The URL requested handler
  private $current_url = null;

  // The URL requested handler
  private $handler = null;

  // Request need authentication ?
  private $authenticated = true;

  // API mode enabled ?
  private $api_mode = false;

  // Parameters detected on requested URL
  private $url_params = array();

  public function __construct($current_url, $handler_infos, $url_params=array()) {
    $this -> current_url = $current_url;
    $this -> handler = $handler_infos['handler'];
    $this -> authenticated = (isset($handler_infos['authenticated'])?boolval($handler_infos['authenticated']):true);
    $this -> api_mode = (isset($handler_infos['api_mode'])?boolval($handler_infos['api_mode']):false);
    $this -> url_params = $url_params;
  }

  /**
   * Get request info
   *
   * @param[in] $key string The name of the info
   *
   * @retval mixed The value
   **/
  public function __get($key) {
    if ($key == 'current_url')
      return $this -> current_url;
    if ($key == 'handler')
      return $this -> handler;
    if ($key == 'authenticated')
      return $this -> authenticated;
    if ($key == 'api_mode')
      return $this -> api_mode;
    if ($key == 'referer')
      return $this -> get_referer();
    if ($key == 'http_method')
      return $_SERVER['REQUEST_METHOD'];
    if ($key == 'ajax')
      return isset($_REQUEST['ajax']) && boolval($_REQUEST['ajax']);
    if (array_key_exists($key, $this->url_params)) {
      return urldecode($this->url_params[$key]);
    }
    // Unknown key, log warning
    self :: log_warning("__get($key): invalid property requested\n".LSlog :: get_debug_backtrace_context());
  }

  /**
   * Check is request info is set
   *
   * @param[in] $key string The name of the info
   *
   * @retval boolval True is info is set, False otherwise
   **/
  public function __isset($key) {
    if (in_array($key, array('current_url', 'handler', 'authenticated')))
      return True;
    return array_key_exists($key, $this->url_params);
  }

  /*
   * Get request referer (if known)
   *
   * @retval string|null The request referer URL if known, null otherwise
   */
  public function get_referer() {
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'])
      return $_SERVER['HTTP_REFERER'];
    return null;
  }

}
