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

/**
 * Basic authentication provider for LSauth
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthMethod_basic extends LSauthMethod {

  /**
   * Check Auth Data
   *
   * Return authentication data or false
   *
   * @retval Array|false Array of authentication data or False
   **/
  public function getAuthData() {
    if (isset($_POST['LSauth_user']) && !empty($_POST['LSauth_user'])) {
      $this -> authData = array(
        'username' => $_POST['LSauth_user'],
        'password' => (isset($_POST['LSauth_pwd'])?$_POST['LSauth_pwd']:'')
      );
      return $this -> authData;
    }
    return;
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
      self :: log_debug('No user found with username="'.$this -> authData['username'].'" => Invalid username');
      return false;
    }
    self :: log_debug('Username "'.$this -> authData['username'].'" matched with following user(s): "'.implode('", "', array_keys($authobjects)).'"');
    $matched = array();
    foreach(array_keys($authobjects) as $dn)
      if ( LSldap :: checkBind($dn, $this -> authData['password']) )
        $matched[] = $dn;
      else
        self :: log_trace("Invalid password provided for '$dn'");
		if (!$matched) {
      LSerror :: addErrorCode('LSauth_01');
      self :: log_debug('Invalid password provided');
      return false;
    }
    elseif (count($matched) > 1) {
      self :: log_debug('Multiple users match with provided username and password: '.implode(', ', $matched));
      LSerror :: addErrorCode('LSauth_02');
      return false;
    }
		// Authentication succeeded
		self :: log_debug('Authentication succeeded for username "'.$this -> authData['username'].'" ("'.$matched[0].'")');
		return $authobjects[$matched[0]];
  }

}
