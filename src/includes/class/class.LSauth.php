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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Gestion de l'authentification d'un utilisateur
 *
 * Cette classe gere l'authentification des utilisateurs � l'interface
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauth extends LSlog_staticLoggerClass {

  static private $authData=NULL;
  static private $authObject=NULL;
  static private $config=array();
  static private $method=NULL;
  static private $provider=NULL;

  static private $params = array (
    'displayLoginForm' => true,
    'displayLogoutBtn' => true,
    'displaySelfAccess' => true
  );

  public static function start() {
    self :: log_debug('start()');
    // Load Config
    if (isset(LSsession :: $ldapServer['LSauth']) && is_array(LSsession :: $ldapServer['LSauth'])) {
      self :: $config = LSsession :: $ldapServer['LSauth'];
    }
    if (!LSsession :: loadLSclass('LSauthMethod')) {
      self :: log_debug('Failed to load LSauthMethod class');
      return;
    }
    $api_mode = LSsession :: get('api_mode');
    if ($api_mode)
      self :: $method = self :: getConfig('api_method', 'HTTP');
    else
      self :: $method = self :: getConfig('method', 'basic');
    $class = "LSauthMethod_".self :: $method;
    self :: log_debug('provider -> '.$class);
    if (LSsession :: loadLSclass($class)) {
      if ($api_mode && !$class :: apiModeSupported()) {
        LSerror :: addErrorCode('LSauth_08', self :: $method);
        return;
      }
      self :: $provider = new $class();
      if (!self :: $provider) {
        LSerror :: addErrorCode('LSauth_05', self :: $method);
        return;
      }
      self :: log_debug('Provider Started !');
      return true;
    }
    else {
      LSerror :: addErrorCode('LSauth_04', self :: $method);
      return;
    }
  }

  public static function forceAuthentication() {
    self :: log_debug('LSauth :: forceAuthentication()');
    if (!is_null(self :: $provider)) {
      self :: $authData = self :: $provider -> getAuthData();
      if (self :: $authData) {
        self :: $authObject = self :: $provider -> authenticate();
        return self :: $authObject;
      }
      // No data : user has not filled the login form
      self :: log_debug('No data -> user has not filled the login form');
      return;
    }
    LSerror :: addErrorCode('LSauth_06');
    return;
  }

  /**
   * Return a configuration parameter (or default value)
   *
   * @param[] $param      The configuration parameter
   * @param[] $default    The default value (default : null)
   * @param[] $cast       Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  private static function getConfig($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, self :: $config);
  }

  /**
   * Retreive auth object types info
   * @return array Array of auth object type with type as key and type's parameters as value
   */
  public static function getAuthObjectTypes() {
    $objTypes = array();
    foreach(self :: getConfig('LSobjects', array()) as $objType => $objParams) {
      if (!self :: checkAuthObjectTypeAccess($objType))
        continue;
      if (is_int($objType) && is_string($objParams)) {
        $objTypes[$objParams] = array('filter' => null, 'password_attribute' => 'userPassword');
        continue;
      }

      $objTypes[$objType] = array(
        'filter' => self :: getConfig("LSobjects.$objType.filter", null, 'string'),
        'password_attribute' => self :: getConfig("LSobjects.$objType.password_attribute", 'userPassword', 'string'),
      );
    }
    // For retro-compatibility, also retreived old parameters (excepted in API mode):
    $oldAuthObjectType = LSconfig :: get('authObjectType', null, 'string', LSsession :: $ldapServer);
    if ($oldAuthObjectType && !array_key_exists($oldAuthObjectType, $objTypes) && self :: checkAuthObjectTypeAccess($oldAuthObjectType)) {
      $objTypes[$oldAuthObjectType] = array(
        'filter' => LSconfig :: get('authObjectFilter', null, 'string', LSsession :: $ldapServer),
        'password_attribute' => LSconfig :: get('authObjectTypeAttrPwd', 'userPassword', 'string', LSsession :: $ldapServer),
      );
    }
    return $objTypes;
  }


  /**
   * Check if the specified auth object type have acces to LdapSaisie (on the current mode)
   *
   * @param[in] $objType string The LSobject type
   *
   * @return boolean True if specified auth object type have acces to LdapSaisie, False otherwise
   */
  public static function checkAuthObjectTypeAccess($objType) {
    // Check Web/API access rights
    if (LSsession :: get('api_mode')) {
      return self :: getConfig("LSobjects.$objType.api_access", false, 'bool');
    }
    return self :: getConfig("LSobjects.$objType.web_access", true, 'bool');
  }

  /**
   * Retreived LSobjects corresponding to a username
   *
   * @retval array|false Array of corresponding LSldapObject with object DN as key, or false in case of error
   */
  public static function username2LSobjects($username) {
    $user_objects = array();
    foreach (self :: getAuthObjectTypes() as $objType => $objParams) {
      if (!LSsession :: loadLSobject($objType)) {
        LSerror :: addErrorCode('LSauth_03', $objType);
        return false;
      }
      $authobject = new $objType();
      $result = $authobject -> searchObject(
        $username,
        LSsession :: getTopDn(),
        $objParams['filter'],
        array('withoutCache' => true, 'onlyAccessible' => false)
      );
      for($i=0; $i<count($result); $i++)
        $user_objects[$result[$i] -> getDn()] = $result[$i];
    }

    $nbresult = count($user_objects);
    if ($nbresult == 0) {
      // incorrect login
      self :: log_debug('Invalid username');
      LSerror :: addErrorCode('LSauth_01');
      return false;
    }
    else if ($nbresult > 1) {
      // duplication of identity
      self :: log_debug("More than one user detected for username '$username': ".implode(', ', array_keys($user_objects)));
      if (!self :: getConfig('allow_multi_match', false, 'bool')) {
        LSerror :: addErrorCode('LSauth_02');
        return false;
      }
    }
    return $user_objects;
  }

  /**
   * Get user password attribute name
   *
   * @param[in] &object LSldapObject The user object
   *
   * @retval string|false The user password attribute name or false if not configured
   */
  public static function getUserPasswordAttribute(&$object) {
    $authObjectTypes = self :: getAuthObjectTypes();
    $objType = $object -> getType();
    if (array_key_exists($objType, $authObjectTypes))
      return $authObjectTypes[$objType]['password_attribute'];
    return false;
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
  public static function getLDAPcredentials($user) {
    return self :: $provider -> getLDAPcredentials($user);
  }

 /**
  * Logout
  *
  * @retval void
  **/
  public static function logout() {
    if (!is_null(self :: $provider)) {
      return self :: $provider -> logout();
    }
    LSerror :: addErrorCode('LSauth_06');
    return;
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
    if (!is_null(self :: $provider)) {
      return self :: $provider -> afterLogout();
    }
    LSerror :: addErrorCode('LSauth_06');
    return;
  }

 /**
  * Disable logout button in LSauth parameters
  *
  * @retval void
  **/
  public static function disableLogoutBtn() {
    self :: $params['displayLogoutBtn'] = false;
  }

 /**
  * Can display or not logout button in LSauth parameters
  *
  * @retval boolean
  **/
  public static function displayLogoutBtn() {
    return self :: $params['displayLogoutBtn'];
  }

 /**
  * Disable self access
  *
  * @retval void
  **/
  public static function disableSelfAccess() {
    self :: $params['displaySelfAccess'] = false;
  }

 /**
  * Can display or not self access
  *
  * @retval boolean
  **/
  public static function displaySelfAccess() {
    return self :: $params['displaySelfAccess'];
  }

  /*
   * For compatibillity until loginForm is migrated in LSauth
   */
  public static function disableLoginForm() {
    self :: $params['displayLoginForm'] = false;
  }

  public static function displayLoginForm() {
    return self :: $params['displayLoginForm'];
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSauth_01',
___("LSauth : Login or password incorrect.")
);
LSerror :: defineError('LSauth_02',
___("LSauth : Impossible to identify you : Duplication of identities.")
);
LSerror :: defineError('LSauth_03',
___("LSauth : Could not load type of identifiable objects %{type}.")
);
LSerror :: defineError('LSauth_04',
___("LSauth : Can't load authentication method %{method}.")
);
LSerror :: defineError('LSauth_05',
___("LSauth : Failed to build the authentication provider %{method}.")
);
LSerror :: defineError('LSauth_06',
___("LSauth : Not correctly initialized.")
);
LSerror :: defineError('LSauth_07',
___("LSauth : Failed to get authentication informations from provider.")
);
LSerror :: defineError('LSauth_08',
___("LSauth : Method %{method} configured doesn't support API mode.")
);
