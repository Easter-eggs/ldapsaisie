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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA	02111-1307, USA.

******************************************************************************/

/**
 * Gestion de l'authentification d'un utilisateur via une authentification 
 * CAS
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthCAS extends LSauth {
	
	var $params = array (
		'displayLoginForm' => false,
		'displayLogoutBtn' => true
	);

 /**
  * Constructor
	*/
	public function LSauthCAS() {
		if (LSsession :: includeFile(PHP_CAS_PATH)) {
			if (defined('PHP_CAS_DEBUG_FILE')) {
				phpCAS::setDebug(PHP_CAS_DEBUG_FILE);
			}
			phpCAS::client(constant(LSAUTH_CAS_VERSION),LSAUTH_CAS_SERVER_HOSTNAME,LSAUTH_CAS_SERVER_PORT,LSAUTH_CAS_SERVER_URI,false);
			if (LSAUTH_CAS_SERVER_NO_SSL_VALIDATION) {
				phpCAS::setNoCasServerValidation();
			}

			if (defined(LSAUTH_CAS_SERVER_SSL_CERT)) {
				phpCAS::setCasServerCert(LSAUTH_CAS_SERVER_SSL_CERT);
			}

			if (defined(LSAUTH_CAS_SERVER_SSL_CACERT)) {
				phpCAS::setCasServerCACert(LSAUTH_CAS_SERVER_SSL_CACERT);
			}

			if (LSAUTH_CAS_DISABLE_LOGOUT) {
				$this -> params['displayLogoutBtn'] = false;
			}

			return true;
		}
		else {
			LSerror :: addErrorCode('LSauthCAS_01');
		}
		return false;
	}

	/**
	 * Check Post Data
	 * 
	 * @retval array|False Array of post data if exist or False
	 **/
	public function getPostData() {
		if (class_exists('phpCAS')) {
			// Launch Auth
			phpCAS::forceAuthentication();

			$this -> authData = array(
				'username' => phpCAS::getUser(),
				'password' => '',
				'ldapserver' => $_REQUEST['LSsession_ldapserver'],
				'topDn' => $_REQUEST['LSsession_topDn']
			);
			return true;
		}
		return;
	}
	
	/**
	 * Check user login
	 *
	 * @param[in] $username The username
	 * @param[in] $password The password
	 *
	 * @retval LSldapObject|false The LSldapObject of the user authificated or false 
	 */
	public function authenticate() {
		if (LSsession :: loadLSobject(LSsession :: $ldapServer['authObjectType'])) {
			$authobject = new LSsession :: $ldapServer['authObjectType']();
			$result = $authobject -> searchObject(
				$this ->	authData['username'],
				LSsession :: getTopDn(),
				LSsession :: $ldapServer['authObjectFilter']
			);
			$nbresult=count($result);
			
			if ($nbresult==0) {
				// identifiant incorrect
				LSdebug('identifiant incorrect');
				LSerror :: addErrorCode('LSauth_01');
			}
			else if ($nbresult>1) {
				// duplication d'authentité
				LSerror :: addErrorCode('LSauth_02');
			}
			else {
				// Authentication succeeded
				return $result[0];
			}
		}
		else {
			LSerror :: addErrorCode('LSauth_03');
		}
		return;
	}

	public function logout() {
		if(class_exists('phpCAS')) {
			if ($this -> params['displayLogoutBtn']) {
				phpCAS :: forceAuthentication();
				phpCAS :: logout();
			}
		}
	}
}
/*
 * Error Codes
 */
LSerror :: defineError('LSauthCAS_01',
_("LSauthCAS : Failed to load phpCAS.")
);
?>
