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

/**
 * URL request abstraction use by LSurl
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSurlRequest {

  // The URL requested handler
  private $current_url = null;

  // The URL requested handler
  private $handler = null;

  // Request need authentication ?
  private $authenticated = true;

  // Parameters detected on requested URL
  private $url_params = array();

  public function __construct($current_url, $handler_infos, $url_params=array()) {
    $this -> current_url = $current_url;
    $this -> handler = $handler_infos['handler'];
    $this -> authenticated = (isset($handler_infos['authenticated'])?boolval($handler_infos['authenticated']):true);
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
    if (array_key_exists($key, $this->url_params)) {
      return urldecode($this->url_params[$key]);
    }
  }

}
