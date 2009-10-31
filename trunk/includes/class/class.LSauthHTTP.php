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
 * Gestion de l'authentification d'un utilisateur suite à une authentification 
 * HTTP
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthHTTP extends LSauth {
  
  var $params = array (
    'displayLoginForm' => false,
    'displayLogoutBtn' => false
  );
  
  /**
   * Check Post Data
   * 
   * @retval array|False Array of post data if exist or False
   **/
  public function getPostData() {
    if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER'])) {
      $this -> authData = array(
        'username' => $_SERVER['PHP_AUTH_USER'],
        'password' => $_SERVER['PHP_AUTH_PW'],
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
        $this ->  authData['username'],
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
        // duplication d'authentitÃ©
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
  
}
?>
