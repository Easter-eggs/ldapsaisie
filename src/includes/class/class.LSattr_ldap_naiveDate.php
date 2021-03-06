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
 * LDAP attribute type naiveDate
 *
 * Convert LDAP generalized time string as timestamp without handling LDAP timezone
 *
 */
class LSattr_ldap_naiveDate extends LSattr_ldap {

  /**
   * Return the display value of the attribute after handling is LDAP type
   *
   * @param[in] $data mixed The LDAP attribute value
   *
   * @retval mixed The display value ot the attribute
   */
  public function getDisplayValue($data) {
    $retval = array();
    foreach(ensureIsArray($data) as $val) {
      $date = strptime($val, $this -> getFormat());
      if (is_array($date)) {
        $retval[] = mktime(
          $date['tm_hour'],
          $date['tm_min'],
          $date['tm_sec'],
          $date['tm_mon']+1,
          $date['tm_mday'],
          $date['tm_year']+1900
        );
      }
    }
    return $retval;
  }

  /**
   * Return the value of the LDAP attribute after handling is LDAP type
   *
   * @param[in] $data mixed The value of the attribute
   *
   * @retval mixed The LDAP value of the attribute
   */
  public function getUpdateData($data) {
    $retval = array();
    foreach(ensureIsArray($data) as $val) {
      $retval[] = strftime($this -> getFormat(), $val);
    }
    return $retval;
  }


  /**
  * Return the storage format of the date (as accept by strptime()/strftime())
  *
  * @retval string the storage format of the date
  **/
  public function getFormat() {
    return $this -> getConfig('ldap_options.format', "%Y%m%d%H%M%SZ");
  }

}
