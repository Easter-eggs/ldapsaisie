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
 * Ldap attribute type password
 *
 */
class LSattr_ldap_password extends LSattr_ldap {

  var $clearPassword = NULL;

  /**
   * Return the display value of this attribute
   *
   * @param[in] $data mixed The value of this attribute
   *
   * @retval mixed The display value of this attribute
   */
  function getDisplayValue($data) {
    return '********';
  }

  /**
   * Return the value of this attribute to be stocked
   *
   * Note : Password encoding was strongly inspired of the project phpLdapAdmin.
   * URL : http://phpldapadmin.sourceforge.net/
   * 
   * @param[in] $data mixed The attribute value
   *
   * @retval mixed The value of this attribute to be stocked
   */
  function getUpdateData($data) {
    $this -> clearPassword = $data[0];
    if (!$this -> config['ldap_options']['encode']) {
      $this -> config['ldap_options']['encode'] = 'md5crypt';
    }
    switch($this -> config['ldap_options']['encode']) {
      case 'crypt':
        if ($this -> config['ldap_options']['no_random_crypt_salt']) {
          return '{CRYPT}' . crypt($this -> clearPassword,substr($this -> clearPassword,0,2));
        }
        else {
          return '{CRYPT}' . crypt($this -> clearPassword,$this -> getSalt(2));
        }
        break;
      case 'ext_des':
        if ( ! defined( 'CRYPT_EXT_DES' ) || CRYPT_EXT_DES == 0 ) {
          LSerror :: addErrorCode('LSattr_ldap_password_01','ext_des');
        }
        else {
          return '{CRYPT}' . crypt( $this -> clearPassword, '_' . $this -> getSalt(8) );
        }
        break;
      case 'blowfish':
        if( ! defined( 'CRYPT_BLOWFISH' ) || CRYPT_BLOWFISH == 0 ) {
          LSerror :: addErrorCode('LSattr_ldap_password_01','blowfish');
        }
        else {
          return '{CRYPT}' . crypt( $this -> clearPassword, '$2a$12$' . $this -> getSalt(13) );
        }
        break;
      case 'sha':
        if( function_exists('sha1') ) {
          return '{SHA}' . base64_encode( pack( 'H*' , sha1( $this -> clearPassword ) ) );
        }
        elseif( function_exists( 'mhash' ) ) {
          return '{SHA}' . base64_encode( mhash( MHASH_SHA1, $this -> clearPassword ) );
        } else {
          LSerror :: addErrorCode('LSattr_ldap_password_01','sha');
        }
        break;
      case 'ssha':
        if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
          mt_srand( (double) microtime() * 1000000 );
          $salt = mhash_keygen_s2k( MHASH_SHA1, $this -> clearPassword, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
          return "{SSHA}".base64_encode( mhash( MHASH_SHA1, $this -> clearPassword.$salt ).$salt );
        }
        else {
          LSerror :: addErrorCode('LSattr_ldap_password_01','ssha');
        }
        break;
      case 'smd5':
        if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
          mt_srand( (double) microtime() * 1000000 );
          $salt = mhash_keygen_s2k( MHASH_MD5, $password_clear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
          return "{SMD5}".base64_encode( mhash( MHASH_MD5, $password_clear.$salt ).$salt );
        }
        else {
          LSerror :: addErrorCode('LSattr_ldap_password_01','smd5');
        }
        break;
      case 'md5':
        return '{MD5}' . base64_encode( pack( 'H*' , md5( $this -> clearPassword ) ) );
        break;
      case 'md5crypt':
        if( ! defined( 'CRYPT_MD5' ) || CRYPT_MD5 == 0 ) {
          LSerror :: addErrorCode('LSattr_ldap_password_01','md5crypt');
        }
        else {
          return '{CRYPT}'.crypt($this -> clearPassword,'$1$'.$this -> getSalt().'$');
        }
        break;
    }
    return $this -> clearPassword;
  }
 
  /**
   * Return salt (random string)
   *
   * @param[in] integer Number of caracters in this salt
   *
   * @retval string A salt
   */
  function getSalt($length=8) {
    $pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
    $key  = $pattern{rand(0,35)};
    for($i=1;$i<$length;$i++)
    {
        $key .= $pattern{rand(0,35)};
    }
    return $key;
  }

  /**
   * Return the password in clear text
   *
   * @retval string The password in clear text
   */
  function getClearPassword() {
    return $this -> clearPassword;
  }
}

/**
 * Error Codes
 **/
LSerror :: defineError('LSattr_ldap_password_01',
_("LSattr_ldap_password : Encoding type %{type} is not supported. This password will be stocked in clear text.")
);
?>
