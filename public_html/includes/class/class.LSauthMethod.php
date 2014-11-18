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
 * Base of a authentication provider for LSauth
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthMethod {

  var $authData = array();
  
  function LSauthMethod() {
		// Load config
		LSsession :: includeFile(LS_CONF_DIR."LSauth/config.".get_class($this).".php");
		LSdebug(LS_CONF_DIR."LSauth/config.".get_class($this).".php");
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
    if (LSsession :: loadLSobject(LSsession :: $ldapServer['authObjectType'])) {
      $authobject = new LSsession :: $ldapServer['authObjectType']();
			$result = $authobject -> searchObject(
				$this -> authData['username'],
				LSsession :: getTopDn(),
				(isset(LSsession :: $ldapServer['authObjectFilter'])?LSsession :: $ldapServer['authObjectFilter']:NULL),
				array('withoutCache' => true)
			);
			$nbresult=count($result);
			
			if ($nbresult==0) {
				// incorrect login
				LSdebug('identifiant incorrect');
				LSerror :: addErrorCode('LSauth_01');
			}
			else if ($nbresult>1) {
				// duplication of identity
				LSerror :: addErrorCode('LSauth_02');
			}
			else {
				return $result[0];
			}
    }
    else {
      LSerror :: addErrorCode('LSauth_03');
    }
    return;
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

?>
