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

LSsession :: loadLSclass('LSauthMethod_basic');

/**
 * HTTP Authentication provider for LSauth
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthMethod_HTTP extends LSauthMethod_basic {

  public function __construct() {
    LSauth :: disableLoginForm();
    if (!defined('LSAUTHMETHOD_HTTP_LOGOUT_REMOTE_URL'))
      LSauth :: disableLogoutBtn();
    return parent :: __construct();
  }

  /**
   * Check Auth Data
   * 
   * Return authentication data or false
   * 
   * @retval Array|false Array of authentication data or False
   **/
  public function getAuthData() {
    if (!defined('LSAUTHMETHOD_HTTP_METHOD'))
      define('LSAUTHMETHOD_HTTP_METHOD', 'PHP_AUTH');

    switch(constant('LSAUTHMETHOD_HTTP_METHOD')) {
      case 'AUTHORIZATION':
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
          $authData = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
          if (is_array($authData) && count($authData) == 2) {
            $this -> authData = array(
              'username' => $authData[0],
              'password' => $authData[1],
            );
          }
          return $this -> authData;
        }
        else
          LSerror :: addErrorCode('LSauthMethod_HTTP_01', 'HTTP_AUTHORIZATION');
        break;
      case 'REMOTE_USER':
        if (isset($_SERVER['REMOTE_USER']) && !empty($_SERVER['REMOTE_USER'])) {
          $this -> authData = array(
            'username' => $_SERVER['REMOTE_USER'],
            'password' => false,
          );
          return $this -> authData;
        }
        else
          LSerror :: addErrorCode('LSauthMethod_HTTP_01', 'REMOTE_USER');
        break;
      case 'PHP_AUTH':
      default:
        if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER'])) {
          $this -> authData = array(
            'username' => $_SERVER['PHP_AUTH_USER'],
            'password' => $_SERVER['PHP_AUTH_PW'],
          );
          return $this -> authData;
        }
        else
          LSerror :: addErrorCode('LSauthMethod_HTTP_01', 'PHP_AUTH_USER');
    }
    return;
  }

  /**
   * Check authentication
   *
   * @retval LSldapObject|false The LSldapObject of the user authificated or false
   */
  public function authenticate() {
    if ( (defined('LSAUTHMETHOD_HTTP_TRUST_WITHOUT_PASSWORD_CHALLENGE')) && (constant('LSAUTHMETHOD_HTTP_TRUST_WITHOUT_PASSWORD_CHALLENGE') === True)) {
      // Return authObject without checking login/password by LDAP auth challenge
      return LSauthMethod :: authenticate();
    }
    else {
      return parent :: authenticate();
    }
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
    if (defined('LSAUTHMETHOD_HTTP_LOGOUT_REMOTE_URL')) {
      LSsession :: redirect(LSAUTHMETHOD_HTTP_LOGOUT_REMOTE_URL);
    }
    return true;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSauthMethod_HTTP_01',
_("LSauthMethod_HTTP : the %{var} environnement variable is missing.")
);

