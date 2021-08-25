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

LSsession :: loadLSclass('LSauthMethod_basic');

/**
 * HTTP Authentication provider for LSauth
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthMethod_HTTP extends LSauthMethod_basic {

  // Boolean flag to specify if this LSauthMethod support API mode
  protected static $api_mode_supported = true;

  public function __construct() {
    parent :: __construct();
    LSauth :: disableLoginForm();
    if (!defined('LSAUTHMETHOD_HTTP_LOGOUT_REMOTE_URL'))
      LSauth :: disableLogoutBtn();
    return True;
  }

  /**
   * Check Auth Data
   *
   * Return authentication data or false
   *
   * @retval Array|false Array of authentication data or False
   **/
  public function getAuthData() {
    if (!defined('LSAUTHMETHOD_HTTP_METHOD')) {
      self :: log_debug('No HTTP method defined: use PHP_AUTH as default.');
      define('LSAUTHMETHOD_HTTP_METHOD', 'PHP_AUTH');
    }
    else {
      self :: log_debug('HTTP method to retreive auth data is "'.LSAUTHMETHOD_HTTP_METHOD.'"');
    }

    $missing_info = null;
    switch(constant('LSAUTHMETHOD_HTTP_METHOD')) {
      case 'AUTHORIZATION':
        $missing_info = 'HTTP_AUTHORIZATION';
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
          $authData = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
          if (is_array($authData) && count($authData) == 2) {
            $this -> authData = array(
              'username' => $authData[0],
              'password' => $authData[1],
            );
            return $this -> authData;
          }
          else
            self :: log_warning("Fail to decode and parse $missing_info environment variable.");
        }
        break;
      case 'REMOTE_USER':
        if (isset($_SERVER['REMOTE_USER']) && !empty($_SERVER['REMOTE_USER'])) {
          $this -> authData = array(
            'username' => $_SERVER['REMOTE_USER'],
            'password' => false,
          );
          return $this -> authData;
        }
        $missing_info = 'REMOTE_USER';
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
        $missing_info = 'PHP_AUTH_USER';
    }
    self :: log_warning("$missing_info variable not available in environment, trigger 403 error.");

    // Auth data not available, trigger HTTP 403 error
    if (defined('LSAUTHMETHOD_HTTP_REALM') && constant('LSAUTHMETHOD_HTTP_REALM'))
      $realm = __(LSAUTHMETHOD_HTTP_REALM);
    else
      $realm = _('LdapSaisie - Authentication required');
    header('WWW-Authenticate: Basic realm="'.$realm.'", charset="UTF-8"');
    header('HTTP/1.0 401 Unauthorized');
    LSerror :: addErrorCode(null, $realm);
    LSsession :: displayAjaxReturn();
    exit();
  }

  /**
   * Check authentication
   *
   * @retval LSldapObject|false The LSldapObject of the user authificated or false
   */
  public function authenticate() {
    if ( (defined('LSAUTHMETHOD_HTTP_TRUST_WITHOUT_PASSWORD_CHALLENGE')) && (constant('LSAUTHMETHOD_HTTP_TRUST_WITHOUT_PASSWORD_CHALLENGE') === True)) {
      // Return authObject without checking login/password by LDAP auth challenge
      self :: log_debug('Trust HTTP authenticated user without password challenge');
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
      self :: log_debug("Logout remote URL configured => redirect user to '".LSAUTHMETHOD_HTTP_LOGOUT_REMOTE_URL."'.");
      LSurl :: redirect(LSAUTHMETHOD_HTTP_LOGOUT_REMOTE_URL);
    }
    self :: log_debug('No logout remote URL configured');
    return true;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSauthMethod_HTTP_01',
___("LSauthMethod_HTTP : the %{var} environnement variable is missing.")
);
