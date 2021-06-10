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

/**
 * LDAP attribute type for sambaAcctFlags
 **/
class LSattr_ldap_sambaAcctFlags extends LSattr_ldap {

  /**
   * Return display value of attribute after treatment related to LDAP type
   *
   * @param[in] $data mixed Attribute data
   *
   * @retval mixed Attribute display value
   **/
  public function getDisplayValue($data) {
    $values = self :: parse_flags($data);
    if (is_array($values))
      return $values;
    return array();
  }

  /**
   * Return attribute value after treatment related to LDAP type
   *
   * @param[in] $data mixed Attribute data
   *
   * @retval mixed Attribute data
   **/
  public function getUpdateData($data) {
    $values = self :: format_flags($data);
    if (is_array($values))
      return $values;
    return array();
  }

  /**
   * Parse flags
   *
   * @param[in] $data Attribute data
   *
   * @retval array Array of enabled flags
   **/
  public static function parse_flags($data) {
    if (!$data)
      return array();
    $data = ensureIsArray($data);
    if (count($data) > 1) {
      LSerror :: addErrorCode('LSattr_ldap_sambaAcctFlags_01');
      return;
    }
    $value = $data[0];
    $preg_pattern = "/^\[([ ";
    foreach(self :: get_available_flags() as $group_label => $flags)
      foreach($flags as $flag => $label)
        $preg_pattern .= $flag;
    $preg_pattern .= "]{0,16})\]$/";
    self :: log_trace("parse($value): PREG composed pattern = '$preg_pattern'");
    if (!preg_match($preg_pattern, $value, $m)) {
      self :: log_error("parse($value): fail to parse value.");
      LSerror :: addErrorCode('LSattr_ldap_sambaAcctFlags_02');
      return;
    }
    $flags = array();
    foreach(str_split($m[1]) as $flag) {
      if ($flag == ' ')
        continue;
      if (in_array($flag, $flags))
        continue;
      $flags[] = $flag;
    }
    return $flags;
  }

  /**
   * Format flags as one LDAP attribute value
   *
   * @param[in] $flags array of string Flags
   *
   * @retval array Array of LDAP attribute value
   **/
  public static function format_flags($flags) {
    $flags = ensureIsArray($flags);
    foreach($flags as $flag) {
      if (!self :: check_flag($flag)) {
        LSerror :: addErrorCode('LSattr_ldap_sambaAcctFlags_03', $flag);
        return;
      }
    }
    // Add some space if need
    for ($i=count($flags); $i <= 11; $i++)
      $flags[] = ' ';
    return array(
      "[".implode("", $flags)."]"
    );
  }

  /**
   * Check if a flag is valid
   *
   * @param[in] $flag string The flag
   *
   * @retval boolean True if flag is valid, False otherwise
   **/
  public static function check_flag($flag) {
    foreach(self :: get_available_flags() as $group_label => $flags)
      if (array_key_exists($flag, $flags))
        return true;
    return false;
  }

  /**
   * Get list of available flags grouped by type
   *
   * @return array List of available flags grouped by type
   */
  public static function get_available_flags() {
    return array(
      ___('Account types') => array(
        'U' => ___('Regular user account'),
        'W' => ___('Workstation Trust Account'),
        'S' => ___('Server Trust Account'),
        'I' => ___('Domain Trust Account'),
        'M' => ___('Majority Node Set (MNS) logon account'),
      ),
      ___('Account settings') => array(
        'H' => ___('Home directory required'),
        'N' => ___('Account without password'),
        'X' => ___('Password does not expire'),
        'D' => ___('Account disabled'),
        'T' => ___('Temporary duplicate of other account'),
        'L' => ___('Account automatically locked'),
      ),
    );
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSattr_ldap_sambaAcctFlags_01',
___("LSattr_ldap_sambaAcctFlags: invalid attribute values count. This attribute type could only handle single value attribute.")
);
LSerror :: defineError('LSattr_ldap_sambaAcctFlags_02',
___("LSattr_ldap_sambaAcctFlags: invalid attribute value. Fail to parse current flags set.")
);
LSerror :: defineError('LSattr_ldap_sambaAcctFlags_03',
___("LSattr_ldap_sambaAcctFlags: invalid flag '%{flag}'. Can't format the LDAP attribute value.")
);
