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

  // Configured flag
  private $configured = false;

  public function __construct() {
		LSauth :: disableLoginForm();

		if (!parent :: __construct())
			return;

		if (LSsession :: includeFile(PHP_CAS_PATH, true)) {
			if (defined('PHP_CAS_DEBUG_FILE')) {
				self :: log_debug('LSauthMethod_CAS : enable debug file '.PHP_CAS_DEBUG_FILE);
				phpCAS::setDebug(PHP_CAS_DEBUG_FILE);
			}
			self :: log_debug('LSauthMethod_CAS : initialise phpCAS :: client with CAS server URL https://'.LSAUTH_CAS_SERVER_HOSTNAME.':'.LSAUTH_CAS_SERVER_PORT.(defined('LSAUTH_CAS_SERVER_URI')?LSAUTH_CAS_SERVER_URI: ''));
			phpCAS::client (
				constant(LSAUTH_CAS_VERSION),
				LSAUTH_CAS_SERVER_HOSTNAME,
				LSAUTH_CAS_SERVER_PORT,
				(defined('LSAUTH_CAS_SERVER_URI')?LSAUTH_CAS_SERVER_URI: ''),
				false
			);

			// Configure CAS server SSL validation
			$cas_server_ssl_validation_configured = false;
			if (defined('LSAUTH_CAS_SERVER_NO_SSL_VALIDATION') && LSAUTH_CAS_SERVER_NO_SSL_VALIDATION) {
				self :: log_debug('LSauthMethod_CAS : disable CAS server SSL validation => /!\ NOT RECOMMENDED IN PRODUCTION ENVIRONMENT /!\\');
				phpCAS::setNoCasServerValidation();
				$cas_server_ssl_validation_configured = true;
			}

			if (defined('LSAUTH_CAS_SERVER_SSL_CACERT')) {
				self :: log_debug('LSauthMethod_CAS : validate CAS server SSL certificate using '.LSAUTH_CAS_SERVER_SSL_CACERT.' CA certificate file.');
				phpCAS::setCasServerCACert(LSAUTH_CAS_SERVER_SSL_CACERT);
				$cas_server_ssl_validation_configured = true;
			}

			// Check CAS server SSL validation is now configured
			if (!$cas_server_ssl_validation_configured) {
				LSerror :: addErrorCode('LSauthMethod_CAS_02');
				return false;
			}

			if (defined('LSAUTH_CAS_CURL_SSLVERION')) {
				self :: log_debug('LSauthMethod_CAS : use specific SSL version '.LSAUTH_CAS_CURL_SSLVERION);
				phpCAS::setExtraCurlOption(CURLOPT_SSLVERSION,LSAUTH_CAS_CURL_SSLVERION);
			}

			if (LSAUTH_CAS_DISABLE_LOGOUT) {
				self :: log_debug('LSauthMethod_CAS : disable logout');
				LSauth :: disableLogoutBtn();
			}

			// Set configured flag
			$this -> configured = true;
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
		if ($this -> configured) {
			// Launch Auth
			self :: log_debug('LSauthMethod_CAS : force authentication');
			phpCAS::forceAuthentication();

			$this -> authData = array(
				'username' => phpCAS::getUser()
			);
			self :: log_debug('LSauthMethod_CAS : auth data : '.varDump($this -> authData));
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
		if($this -> configured) {
			if (LSauth :: displayLogoutBtn()) {
				phpCAS :: forceAuthentication();
				self :: log_debug("LSauthMethod_CAS :: logout() : trigger CAS logout");
				phpCAS :: logout();
				return true;
			}
			else
				self :: log_warning("LSauthMethod_CAS :: logout() : logout is disabled");
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
LSerror :: defineError('LSauthMethod_CAS_02',
_("LSauthMethod_CAS : Please check your configuration : you must configure CAS server SSL certificate validation using one of the following constant : LSAUTH_CAS_SERVER_SSL_CACERT or LSAUTH_CAS_SERVER_NO_SSL_VALIDATION")
);
