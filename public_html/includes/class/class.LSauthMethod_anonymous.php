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
 * Anonymous authentication provider for LSauth
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSauthMethod_anonymous extends LSauthMethod {

  function LSauthMethod_anonymous() {
		LSauth :: disableLoginForm();
		LSauth :: disableLogoutBtn();
		LSauth :: disableSelfAccess();
		
		if (!parent :: LSauthMethod())
			return;
			
		if ( (!defined('LSAUTHMETHOD_ANONYMOUS_USER')) || (constant('LSAUTHMETHOD_ANONYMOUS_USER') == "")) {
			LSerror :: addErrorCode('LSauthMethod_anonymous_01');
			return;
		}
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
		$this -> authData = array(
			'username' => LSAUTHMETHOD_ANONYMOUS_USER
		);
    return $this -> authData;
  }
  
}
/*
 * Error Codes
 */
LSerror :: defineError('LSauthMethod_anonymous_01',
_("LSauthMethod_anonymous : You must define the LSAUTHMETHOD_ANONYMOUS_USER contant in the configuration file.")
);
?>
