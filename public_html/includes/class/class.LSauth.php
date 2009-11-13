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
 * Gestion de l'authentification d'un utilisateur
 *
 * Cette classe gere l'authentification des utilisateurs � l'interface
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauth {
  
  static private $authData=NULL;
  
  var $params = array (
    'displayLoginForm' => true,
    'displayLogoutBtn' => true
  );
  
  /**
   * Check Post Data
   * 
   * @retval boolean True if post data permit the authentification or False
   **/
  public function getPostData() {
    if (isset($_POST['LSsession_user']) && !empty($_POST['LSsession_user'])) {
      $this -> authData = array(
        'username' => $_POST['LSsession_user'],
        'password' => $_POST['LSsession_pwd'],
        'ldapserver' => $_POST['LSsession_ldapserver'],
        'topDn' => $_POST['LSsession_topDn']
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
        $this -> authData['username'],
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
      elseif ( $this -> checkUserPwd($result[0],$this -> authData['password']) ) {
        // Authentication succeeded
        return $result[0];
      }
      else {
        LSerror :: addErrorCode('LSauth_01');
        LSdebug('mdp incorrect');
      }
    }
    else {
      LSerror :: addErrorCode('LSauth_03');
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
  * @retval boolean True si l'authentification à réussi, false sinon.
  */
  public static function checkUserPwd($object,$pwd) {
    return LSldap :: checkBind($object -> getValue('dn'),$pwd);
  }
  
  /**
   * Define if login form can be displayed or not
   * 
   * @retval boolean
   **/
  public function __get($key) {
    if ($key=='params') {
      return $this -> params;
    }
    return;
  }
  
}

/*
 * Error Codes
 */
LSerror :: defineError('LSauth_01',
_("LSauth : Login or password incorrect.")
);
LSerror :: defineError('LSauth_02',
_("LSauth : Impossible to identify you : Duplication of identities.")
);
LSerror :: defineError('LSauth_03',
_("LSsession : Could not load type of identifiable objects.")
);
?>
