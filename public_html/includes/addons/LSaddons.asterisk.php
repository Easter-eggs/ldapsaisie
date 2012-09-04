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

// Error messages

// Support
LSerror :: defineError('ASTERISK_SUPPORT_01',
  __("Asterisk Support : The constant %{const} is not defined.")
);
LSerror :: defineError('ASTERISK_01',
  __("Asterisk : The function %{function} only work with %{objectName}.")
);
LSerror :: defineError('ASTERISK_02',
  __("Asterisk : The attribute %{dependency} is missing. Unable to generate MD5 hashed password.")
);
LSerror :: defineError('ASTERISK_03',
  __("Asterisk : Clear password not availlable. Unable to generate MD5 hashed password.")
);

 /**
  * Check support of Asterisk by LdapSaisie
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true if Asterisk is totally supported, false in other case
  */
  function LSaddon_asterisk_support() {
    $retval=true;

    $MUST_DEFINE_CONST= array(
      'LS_ASTERISK_HASH_PWD_FORMAT',
      'LS_ASTERISK_USERPASSWORD_ATTR',
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( (!defined($const)) || (constant($const) == "")) {
        LSerror :: addErrorCode('ASTERISK_SUPPORT_01',$const);
        $retval=false;
      }
    }

    return $retval;
  }



 /**
  * Make asterisk password hash
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  * 
  * Hash password in MD5 respecting the LSformat LS_ASTERISK_HASH_PWD_FORMAT.
  *
  * This function can be used as encode_function of LSattr_ldap :: password.
  * 
  * @param[in] $ldapObject LSldapObject The LSldapObject use to build the hashed password
  * @param[in] $clearPassword string The password in clear text
  *
  * @retval string The hashed password
  */
  function hashAsteriskPassword($ldapObject,$clearPassword) {
    if (!is_a($ldapObject,'LSldapObject')) {
      LSerror :: addErrorCode('ASTERISK_01',array('function' => 'hashAsteriskPassword', 'objectName' => 'LSldapObject'));
      return;
    }
    if (!is_string($clearPassword)) {
      return;
    }
    $ldapObject -> registerOtherValue('clearPassword',$clearPassword);
    return md5($ldapObject->getFData(LS_ASTERISK_HASH_PWD_FORMAT));
 }

 /**
  * Generate asterisk MD5 hashed password
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject The LSldapObject
  *
  * @retval string asterisk MD5 hashed password or False
  */
  function generate_asteriskMD5HashedPassword($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_ASTERISK_USERPASSWORD_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('ASTERISK_02',array(LS_ASTERISK_USERPASSWORD_ATTR));
      return;
    }

    $password = $ldapObject -> attrs[ LS_ASTERISK_USERPASSWORD_ATTR ] -> ldap -> getClearPassword();
    if (!$password or empty($password)) {
      LSerror :: addErrorCode('ASTERISK_03');
      return;
    }
    return hashAsteriskPassword($ldapObject,(string)$password);
  }
  
  
