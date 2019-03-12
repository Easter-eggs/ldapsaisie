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
 * CAS Authentication provider for LSauth
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthMethod_CAS extends LSauthMethod {

  public function __construct() {
		LSauth :: disableLoginForm();
		
		if (!parent :: LSauthMethod())
			return;

		if (LSsession :: includeFile(PHP_CAS_PATH)) {
			if (defined('PHP_CAS_DEBUG_FILE')) {
				phpCAS::setDebug(PHP_CAS_DEBUG_FILE);
			}
			phpCAS::client(constant(LSAUTH_CAS_VERSION),LSAUTH_CAS_SERVER_HOSTNAME,LSAUTH_CAS_SERVER_PORT,LSAUTH_CAS_SERVER_URI,false);
			if (LSAUTH_CAS_SERVER_NO_SSL_VALIDATION) {
				phpCAS::setNoCasServerValidation();
			}

			if (defined('LSAUTH_CAS_SERVER_SSL_CERT')) {
				phpCAS::setCasServerCert(LSAUTH_CAS_SERVER_SSL_CERT);
			}

			if (defined('LSAUTH_CAS_SERVER_SSL_CACERT')) {
				phpCAS::setCasServerCACert(LSAUTH_CAS_SERVER_SSL_CACERT);
			}

			if (defined('LSAUTH_CAS_CURL_SSLVERION')) {
				phpCAS::setExtraCurlOption(CURLOPT_SSLVERSION,LSAUTH_CAS_CURL_SSLVERION);
			}

			if (LSAUTH_CAS_DISABLE_LOGOUT) {
				LSauth :: disableLogoutBtn();
			}

			return true;
		}
		else {
			LSerror :: addErrorCode('LSauthMethod_CAS_01');
		}
		return false;
	}

  /**
   * Check Auth Data
   * 
   * Return authentication data or false
   * 
   * @retval Array|false Array of authentication data or False
   **/
  public function getAuthData() {
		
		if (class_exists('phpCAS')) {
			
			// Launch Auth
			phpCAS::forceAuthentication();

			$this -> authData = array(
				'username' => phpCAS::getUser()
			);
			return $this -> authData;
		}
		return;
	}
	
 /**
  * Logout
  * 
  * @retval boolean True on success or False
  **/
	public function logout() {
		if(class_exists('phpCAS')) {
			if (LSauth :: displayLogoutBtn()) {
				phpCAS :: forceAuthentication();
				phpCAS :: logout();
				return true;
			}
		}
		return;
	}

}

/*
 * Error Codes
 */
LSerror :: defineError('LSauthMethod_CAS_01',
_("LSauthMethod_CAS : Failed to load phpCAS.")
);

