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
  public function getDisplayValue($data) {
    if ($this -> getConfig('ldap_options.displayClearValue', false, 'bool')) {
      if (is_array($data)) {
        $ret=array();
        $wildcardPassword = $this -> getConfig('ldap_options.wildcardPassword');
        $encodedWildcardPassword = $this -> getConfig('ldap_options.encodedWildcardPassword');
        foreach($data as $p) {
          if ($p == $wildcardPassword || $p == $encodedWildcardPassword) {
            continue;
          }
          $ret[]=$p;
        }
        return $ret;
      }
      else {
        return $data;
      }
    }
    else {
      return '********';
    }
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
  public function getUpdateData($data) {
    $this -> clearPassword = $data[0];
    $data=array();

    $data[]=$this -> encodePassword($this -> clearPassword);

    // Wildcard Password
    $wildcardPassword = $this -> getConfig('ldap_options.wildcardPassword');
    if ($wildcardPassword) {
      if (!is_array($wildcardPassword)) {
        $data[] = $this -> encodePassword($wildcardPassword);
      }
      else {
        foreach($wildcardPassword as $pwd) {
          $data[] = $this -> encodePassword($pwd);
        }
      }
    }

    // Wildcard Password already encoded
    $encodedWildcardPassword = $this -> getConfig('ldap_options.encodedWildcardPassword');
    if ($encodedWildcardPassword) {
      if (!is_array($encodedWildcardPassword)) {
        $data[] = $encodedWildcardPassword;
      }
      else {
        $data = array_merge($data, $encodedWildcardPassword);
      }
    }

    return $data;
  }

  /**
   * Encode the password
   *
   * Note : Password encoding was strongly inspired of the project phpLdapAdmin.
   * URL : http://phpldapadmin.sourceforge.net/
   *
   * @param[in] $clearPassword string The clear password
   *
   * @retval strinf The encode password
   */
  public function encodePassword($clearPassword, $encode=null, $encode_function=null, $salt=null) {
    if (is_null($encode))
      $encode = $this -> getConfig('ldap_options.encode', 'md5crypt', 'string');
    if (is_null($encode_function))
      $encode_function = $this -> getConfig('ldap_options.encode_function');
    if ($encode_function || $encode == 'function') {
      if ( (!$encode_function) || (!is_callable($encode_function)) ) {
        $encode = 'clear';
        $encode_function = null;
        LSerror :: addErrorCode('LSattr_ldap_password_02', ($encode_function?$encode_function:__('undefined')));
      }
      else {
        $encode = 'function';
      }
    }
    switch($encode) {
      case 'crypt':
        if ($this -> getConfig('ldap_options.no_random_crypt_salt')) {
          return '{CRYPT}' . crypt($clearPassword,substr($clearPassword,0,2));
        }
        else {
          if (is_null($salt))
            $salt = $this -> getSalt(2);
          return '{CRYPT}' . crypt($clearPassword, $salt);
        }
        break;
      case 'ext_des':
        if ( ! defined( 'CRYPT_EXT_DES' ) || CRYPT_EXT_DES == 0 ) {
          LSerror :: addErrorCode('LSattr_ldap_password_01','ext_des');
        }
        else {
          if (is_null($salt))
            $salt = $this -> getSalt(8);
          return '{CRYPT}' . crypt( $clearPassword, '_' . $salt );
        }
        break;
      case 'blowfish':
        if( ! defined( 'CRYPT_BLOWFISH' ) || CRYPT_BLOWFISH == 0 ) {
          LSerror :: addErrorCode('LSattr_ldap_password_01','blowfish');
        }
        else {
          if (is_null($salt))
            $salt = '$2y$12$' . $this -> getSalt(22);
          return '{CRYPT}' . crypt( $clearPassword, $salt );
        }
        break;
      case 'sha':
        if( function_exists('sha1') ) {
          return '{SHA}' . base64_encode( pack( 'H*' , sha1( $clearPassword ) ) );
        }
        elseif( function_exists( 'mhash' ) ) {
          return '{SHA}' . base64_encode( mhash( MHASH_SHA1, $clearPassword ) );
        } else {
          LSerror :: addErrorCode('LSattr_ldap_password_01','sha');
        }
        break;
      case 'sha256':
      case 'sha512':
        switch($encode) {
          case 'sha256':
            $mhash_type = MHASH_SHA256;
            break;
          case 'sha512':
            $mhash_type = MHASH_SHA512;
            break;
        }
        if( function_exists( 'mhash' ) ) {
          return '{'.strtoupper($encode).'}' . base64_encode( mhash( $mhash_type, $clearPassword ) );
        } else {
          LSerror :: addErrorCode('LSattr_ldap_password_01', $encode);
        }
        break;
      case 'ssha':
      case 'ssha256':
      case 'ssha512':
        switch($encode) {
          case 'ssha':
            $mhash_type = MHASH_SHA1;
            break;
          case 'ssha256':
            $mhash_type = MHASH_SHA256;
            break;
          case 'ssha512':
            $mhash_type = MHASH_SHA512;
            break;
        }
        if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
          mt_srand( (double) microtime() * 1000000 );
          if (is_null($salt))
            $salt = mhash_keygen_s2k( $mhash_type, $clearPassword, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
          return "{".strtoupper($encode)."}".base64_encode( mhash( $mhash_type, $clearPassword.$salt ).$salt );
        }
        else {
          LSerror :: addErrorCode('LSattr_ldap_password_01', $encode);
        }
        break;
      case 'smd5':
        if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
          mt_srand( (double) microtime() * 1000000 );
          if (is_null($salt))
            $salt = mhash_keygen_s2k( MHASH_MD5, $password_clear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
          return "{SMD5}".base64_encode( mhash( MHASH_MD5, $clearPassword.$salt ).$salt );
        }
        else {
          LSerror :: addErrorCode('LSattr_ldap_password_01','smd5');
        }
        break;
      case 'md5':
        return '{MD5}' . base64_encode( pack( 'H*' , md5( $clearPassword ) ) );
        break;
      case 'md5crypt':
        if( ! defined( 'CRYPT_MD5' ) || CRYPT_MD5 == 0 ) {
          LSerror :: addErrorCode('LSattr_ldap_password_01','md5crypt');
        }
        else {
          if (is_null($salt))
            $salt = $this -> getSalt();
          return '{CRYPT}'.crypt($clearPassword,'$1$'.$salt.'$');
        }
        break;
      case 'clear':
        return $clearPassword;
        break;
      case 'function':
        return call_user_func_array($encode_function, array(&$this -> attribute -> ldapObject, $clearPassword));
        break;
    }
    LSerror :: addErrorCode('LSattr_ldap_password_01', $encode);
    return $clearPassword;
  }

  function verify($clearPassword, $hashedPassword=null) {
    // If $hashedPassword is not provided, use attribute values
    if (is_null($hashedPassword))
      $hashedPassword = $this -> attribute -> getValue();

    // If $hashedPassword is array, iter to find valid password
    if (is_array($hashedPassword)) {
      foreach($hashedPassword as $pwd)
        if ($this -> verify($clearPassword, $pwd))
          return true;
      return false;
    }
    // Verify $hashedPassword is a string
    elseif (!is_string($hashedPassword))
      return false;

    // Custom verify function configured ? If yes, use it
    $verifyFunction = $this -> getConfig('ldap_options.verify_function', null);
    if (!is_null($verifyFunction) && is_callable($verifyFunction))
      return call_user_func_array($verifyFunction, array(&$this -> attribute -> ldapObject, $clearPassword, $hashedPassword));

    // Custom encode function configured ? If yes, use it
    $encodeFunction = $this -> getConfig('ldap_options.encode_function', null);
    if (!is_null($encodeFunction) && is_callable($encodeFunction))
      return (strcasecmp(call_user_func_array($encodeFunction, array(&$this -> attribute -> ldapObject, $clearPassword)), $hashedPassword) == 0);

    // Extract cipher
    if (preg_match('/{([^}]+)}(.*)/',$hashedPassword,$matches)) {
      $hashedPasswordData = $matches[2];
      $cypher = strtolower($matches[1]);

    } else {
      $cypher = null;
    }

    // Verify password according on cypher
    switch($cypher) {
      # SSHA crypted passwords
      case 'ssha':
      case 'ssha256':
      case 'ssha512':
      case 'smd5':
        $data = base64_decode($hashedPasswordData);
        # Salt = last 4 bytes
        $salt = substr($data, -4);
        $new_hash = $this -> encodePassword($clearPassword, $cypher, null, $salt);
        return (strcmp($hashedPassword,$new_hash) == 0);
        break;

      # Non-salted cyphers
      case 'sha':
      case 'sha256':
      case 'sha512':
      case 'md5':
        $new_hash = $this -> encodePassword($clearPassword, $cypher);
        return (strcasecmp($new_hash, $hashedPassword) == 0);
        break;

      # Crypt passwords
      case 'crypt':
        # Check if it's blowfish crypt
        if (preg_match('/^\\$2+/',$hashedPasswordData)) {
          list($dummy, $version, $rounds, $salt_hash) = explode('$',$hashedPasswordData);
          $salt = '$'.$version.'$'.$rounds.'$'.substr($salt_hash, 0, 22);
          $new_hash = $this -> encodePassword($clearPassword, 'blowfish', null, $salt);
          return (strcasecmp($new_hash, $hashedPassword) == 0);
        }

        # Check if it's an md5crypt
        elseif (strstr($hashedPasswordData,'$1$')) {
          list($dummy,$type,$salt,$hash) = explode('$',$hashedPasswordData);
          $new_hash = $this -> encodePassword($clearPassword, 'md5crypt', null, $salt);
          return (strcasecmp($new_hash, $hashedPassword) == 0);
        }

        # Check if it's ext_des crypt
        elseif (strstr($hashedPasswordData,'_')) {
          return (crypt($clearPassword,$hashedPasswordData) == $hashedPasswordData);
        }

        # Password is plain crypt
        else {
          return (crypt($clearPassword,$hashedPasswordData) == $hashedPasswordData);
        }

        break;

      # No crypt is given
      default:
        # Assume is a plaintext password
        return (strcasecmp($clearPassword, $hashedPassword) == 0);
    }
    // It's supposed to never append, but just in case, return false
    return false;
  }

  /**
   * Return salt (random string)
   *
   * @param[in] integer Number of caracters in this salt
   *
   * @retval string A salt
   */
  public static function getSalt($length=8) {
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
  public function getClearPassword() {
    return $this -> clearPassword;
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSattr_ldap_password_01',
___("LSattr_ldap_password : Encoding type %{type} is not supported. This password will be stored in clear text.")
);
LSerror :: defineError('LSattr_ldap_password_02',
___("LSattr_ldap_password : Encoding function %{function} is not callable. This password will be stored in clear text.")
);
