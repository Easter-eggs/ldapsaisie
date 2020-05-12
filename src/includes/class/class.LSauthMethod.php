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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Base of a authentication provider for LSauth
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthMethod extends LSlog_staticLoggerClass {

  var $authData = array();

  public function __construct() {
    // Load config (without warning if not found)
    $conf_file = LS_CONF_DIR."LSauth/config.".get_class($this).".php";
    if (LSsession :: includeFile($conf_file, false, false))
      self :: log_debug(get_class($this)." :: __construct(): config file ($conf_file) loaded");
    else
      self :: log_debug(get_class($this)." :: __construct(): config file ($conf_file) not found");
    return true;
  }

  /**
   * Check Auth Data
   *
   * Return authentication data or false
   *
   * @retval Array|false Array of authentication data or False
   **/
  public function getAuthData() {
    // Do nothing in the standard LSauthMethod class
    // This method have to define $this -> authData['username']
    return false;
  }

  /**
   * Check authentication
   *
   * @retval LSldapObject|false The LSldapObject of the user authificated or false
   */
  public function authenticate() {
    $authobjects = LSauth :: username2LSobjects($this -> authData['username']);
    if (!$authobjects) {
      LSerror :: addErrorCode('LSauth_01');
      self :: log_debug("No user found for provided username '".$this -> authData['username']."'");
    }
    elseif (count($authobjects) > 1) {
      self :: log_debug('Multiple users match with provided username: '.implode(', ', array_keys($authobjects)));
      LSerror :: addErrorCode('LSauth_02');
      return false;
    }
    // Authentication succeeded
    return array_pop($authobjects);
  }

 /**
  * Logout
  *
  * @retval boolean True on success or False
  **/
  public function logout() {
     // Do nothing in the standard LSauthMethod class
     return true;
  }

 /**
  * After logout
  *
  * This method is run by LSsession after the local session was
  * was successfully destroyed.
  *
  * @retval void
  **/
  public static function afterLogout() {
    return true;
  }

  /**
   * Get LDAP credentials
   *
   * Return LDAP credentials or false
   *
   * @params[in] $user The LSldapObject of the user authificated
   *
   * @retval Array|false Array of LDAP credentials array('dn','pwd') or False
   **/
  public function getLDAPcredentials($user) {
    if (isset($this -> authData['password'])) {
      return array(
        'dn' => $user -> getDn(),
        'pwd' => $this -> authData['password']
      );
    }
    return false;
  }

}
