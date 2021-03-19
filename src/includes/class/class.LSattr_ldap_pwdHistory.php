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
 * Ldap attribute type for stanard pwdHistory attribute (see draft-behera-ldap-password-policy-10)
 *
 * Convert pwdHistory attribute value :
 * From :
 *
 *   20201202144718Z#1.3.6.1.4.1.1466.115.121.1.40#105#{SSHA512}XDSiR6Sh6W7gyVIk6Rr2OUv8rNPr+0rHF99d9lcirE/TnnEdkjkncIi5iPubErL5lpfgh8gXLgSfmqvmFcMqXLToC25xIqyk
 * To:
 *
 *   {"time":1606920438,"syntaxOID":"1.3.6.1.4.1.1466.115.121.1.40","length":105,"hashed_password":"{SSHA512}XDSiR6Sh6W7gyVIk6Rr2OUv8rNPr+0rHF99d9lcirE/TnnEdkjkncIi5iPubErL5lpfgh8gXLgSfmqvmFcMqXLToC25xIqyk"}
 */
class LSattr_ldap_pwdHistory extends LSattr_ldap {

  /**
   * Return the values as JSON encoded string
   *
   * @param[in] $data mixed LDAP attribute value
   *
   * @retval mixed Array of JSON encoded string
   */
  public function getDisplayValue($data) {
    $ret = array();
    foreach(ensureIsArray($data) as $key => $val)
      $ret[$key] = json_encode($this -> parseValue($val));
    return $ret;
  }

  /**
   * Return the values for saving
   *
   * @param[in] $data mixed Array of timestamp
   *
   * @retval mixed LDAP attribute values
   */
  public function getUpdateData($data) {
    $ret = array();
    foreach(ensureIsArray($data) as $key => $val)
      $ret[$key] = $this -> encodeValue(json_decode($val, true));
    return $ret;
  }

  public function parseValue($value) {
    $parts = explode('#', $value);
    if (!is_array($parts) || count($parts) != 4) {
      self :: log_warning($this."->parseValue($value): Invalid value (parts count != 4).");
      return;
    }
    $datetime = date_create_from_format('YmdHisO', $parts[0]);
    if ($datetime instanceof DateTime) {
      $datetime -> setTimezone(timezone_open(date_default_timezone_get()));
      $time = $datetime -> format($this -> getFormat());
    }
    else {
      self :: log_warning($this."->parseValue($value): Fail to parse time '".$parts[0]."'.");
      $time = getFData(_('Unknown (%{raw_value})'), $parts[0]);
    }
    return array(
      "time" => $time,
      "syntaxOID" => $parts[1],
      "length" => intval($parts[2]),
      "hashed_password" => $parts[3],
    );
  }

  public function encodeValue($value) {
    if (!is_array($value)) {
      self :: log_warning($this."->encodeValue($value): Provided value is not an array.");
      return;
    }
    $datetime = date_create_from_format('YmdHisO', $value['time']);
    if (!is_a($datetime, 'DateTime')) {
      self :: log_warning($this."->encodeValue($value): Fail to create DateTime object from timestamp '".varDump($value['time'])."'.");
      return;
    }
    $datetime -> setTimezone('UTC');
    $datetime_string = $datetime -> format('YmdHisO');
    $datetime_string = preg_replace('/[\+\-]0000$/', 'Z', $datetime_string);
    return implode(
      "#",
      array (
        $datetime_string,
        $value['syntaxOID'],
        $value['length'],
        $value['hashed_password'],
      )
    );
  }

 /**
  * Return date format
  *
  * @retval string The date format (as accept by Datetime :: format() and date_create_from_format())
  **/
  public function getFormat() {
    return $this -> getConfig('ldap_options.date_format', 'Y/m/d H:i:s');
  }

}
