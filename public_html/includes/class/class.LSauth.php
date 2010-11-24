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
  static private $authObject=NULL;
  static private $config=array();
  static private $provider=NULL;
  
  static private $params = array (
    'displayLoginForm' => true,
    'displayLogoutBtn' => true
  );

  function start() {
		LSdebug('LSauth :: start()');
    // Load Config
    if (isset(LSsession :: $ldapServer['LSauth']) && is_array(LSsession :: $ldapServer['LSauth'])) {
      self :: $config = LSsession :: $ldapServer['LSauth'];
    }
    if (!LSsession :: loadLSclass('LSauthMethod')) {
			LSdebug('LSauth :: Failed to load LSauthMethod');
			return;
		}
    if (!isset(self :: $config['method'])) {
      self :: $config['method']='basic';
    }
    $class='LSauthMethod_'.self :: $config['method'];
    LSdebug('LSauth : provider -> '.$class);
    if (LSsession :: loadLSclass($class)) {
      self :: $provider = new $class();
      if (!self :: $provider) {
        LSerror :: addErrorCode('LSauth_05',self :: $config['method']);
      }
      LSdebug('LSauth : Provider Started !');
      return true;
    }
    else {
      LSerror :: addErrorCode('LSauth_04',self :: $config['method']);
      return;
    }
  }
  
  function forceAuthentication() {
		LSdebug('LSauth :: forceAuthentication()');
		if (!is_null(self :: $provider)) {
			self :: $authData = self :: $provider -> getAuthData();
			if (self :: $authData) {
				self :: $authObject = self :: $provider -> authenticate();
				return self :: $authObject;
			}
			// No data : user has not filled the login form
			LSdebug('LSauth : No data -> user has not filled the login form');
			return;
		}
		LSerror :: addErrorCode('LSauth_06');
		return;
	}

 /**
  * Logout
  * 
  * @retval void
  **/
  public function logout() {
     if (!is_null(self :: $provider)) {
			return self :: $provider -> logout();
		}
		LSerror :: addErrorCode('LSauth_06');
		return;
  }

 /**
  * Disable logout button in LSauth parameters
  * 
  * @retval void
  **/
  public function disableLogoutBtn() {
		self :: $params['displayLogoutBtn'] = false;
	}

 /**
  * Can display or not logout button in LSauth parameters
  * 
  * @retval boolean
  **/	
	public function displayLogoutBtn() {
		return self :: $params['displayLogoutBtn'];
	}
  
  /*
   * For compatibillity until loginForm is migrated in LSauth
   */
  public function disableLoginForm() {
		self :: $params['displayLoginForm'] = false;
	}
	
	public function displayLoginForm() {
		return self :: $params['displayLoginForm'];
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
_("LSauth : Could not load type of identifiable objects.")
);
LSerror :: defineError('LSauth_04',
_("LSauth : Can't load authentication method %{method}.")
);
LSerror :: defineError('LSauth_05',
_("LSauth : Failed to build the authentication provider %{method}.")
);
LSerror :: defineError('LSauth_06',
_("LSauth : Not correctly initialized.")
);
LSerror :: defineError('LSauth_07',
_("LSauth : Failed to get authentication informations from provider.")
);

?>
