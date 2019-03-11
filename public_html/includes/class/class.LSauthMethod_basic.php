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
		$authobject = parent :: authenticate();
		if ($authobject) {
			if ( $this -> checkUserPwd($authobject,$this -> authData['password']) ) {
				// Authentication succeeded
				return $authobject;
			}
			else {
				LSerror :: addErrorCode('LSauth_01');
				LSdebug('mdp incorrect');
			}
		}
		return;
  }
  
 /**
	* Test un couple LSobject/pwd
	*
	* Test un bind sur le serveur avec le dn de l'objet et le mot de passe fourni.
	*
	* @param[in] LSobject L'object "user" pour l'authentification
	* @param[in] string Le mot de passe à tester
	*
	* @retval boolean True si l'authentification a reussi, false sinon.
	**/
  public static function checkUserPwd($object,$pwd) {
    return LSldap :: checkBind($object -> getValue('dn'),$pwd);
  }
  
}

