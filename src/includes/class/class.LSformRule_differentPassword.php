<?php
/*******************************************************************************
 * Copyright (C) 2019 Easter-eggs
 * http://ldapsaisie.org
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
 * Check that password is different of another attribute of this user
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_differentPassword extends LSformRule {

  // CLI parameters autocompleters
  protected static $cli_params_autocompleters = array(
    'otherPasswordAttributes' => null,
  );

  /**
   * Check the value
   *
   * @param string $values Value to check
   * @param array $options Validation options :
   *                              - Other attribute : $options['params']['otherPasswordAttributes']
   * @param object $formElement The linked LSformElement object
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  public static function validate($value, $options=array(), &$formElement) {
    $otherPasswordAttributes = LSconfig :: get('params.otherPasswordAttributes', null, null, $options);
    if (!is_null($otherPasswordAttributes)) {
      // Load LSattr_ldap_password
      if (!LSsession :: loadLSclass("LSattr_ldap_password")) {
        LSerror :: addErrorCode('LSformRule_differentPassword_02');
        return false;
      }

      // Iter on otherPasswordAttributes to check password does not match
      foreach(ensureIsArray($otherPasswordAttributes) as $attr) {
        // Check attribute exist
        if (!isset($formElement -> attr_html -> attribute -> ldapObject -> attrs[$attr])) {
          LSerror :: addErrorCode('LSformRule_differentPassword_03', $attr);
          return false;
        }

        // Check is not the same attribute of the current one
        if ($formElement -> attr_html -> attribute -> name == $attr) {
          LSerror :: addErrorCode('LSformRule_differentPassword_04');
          return false;
        }

        // Check attribute use LSldap_attr :: password type
        if (!$formElement -> attr_html -> attribute -> ldapObject -> attrs[$attr] -> ldap instanceof LSattr_ldap_password) {
          LSerror :: addErrorCode('LSformRule_differentPassword_05', $attr);
          return false;
        }

        if ($formElement -> attr_html -> attribute -> ldapObject -> attrs[$attr] -> ldap -> verify($value, $formElement -> form -> getValue($attr))) {
          LSdebug($formElement -> name . " : Password matched with attribute $attr");
          return false;
        }
        else
          LSdebug($formElement -> name . " : Password does not match with $attr");
      }
    }
    else {
      LSerror :: addErrorCode('LSformRule_differentPassword_01');
      return false;
    }
    return true;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSformRule_differentPassword_01',
___("LSformRule_differentPassword : Other password attribute is not configured.")
);
LSerror :: defineError('LSformRule_differentPassword_02',
___("LSformRule_differentPassword : Fail to load LSattr_ldap :: password class.")
);
LSerror :: defineError('LSformRule_differentPassword_03',
___("LSformRule_differentPassword : The other password attribute %{attr} does not exist.")
);
LSerror :: defineError('LSformRule_differentPassword_04',
___("LSformRule_differentPassword : The other password attribute could not be the same of the current one.")
);
LSerror :: defineError('LSformRule_differentPassword_05',
___("LSformRule_differentPassword : The other password attributes must use LSattr_ldap :: password. It's not the case of the attribure %{attr}.")
);
