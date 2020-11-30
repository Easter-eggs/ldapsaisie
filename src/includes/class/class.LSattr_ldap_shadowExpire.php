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
 * LDAP Attribute shadowExpire
 *
 * This class permit to manage shadowExpire attribute as a timestamp, and consequently
 * permit to use LSattr_html_date to manage it value in the interface.
 */
class LSattr_ldap_shadowExpire extends LSattr_ldap {

  /**
   * Return the values as timestamps
   *
   * @param[in] $data mixed LDAP attribute value
   *
   * @retval mixed Array of timestamp
   */
  public function getDisplayValue($data) {
    $ret = array();
    foreach(ensureIsArray($data) as $val)
      $ret[] = intval($val)*86400;
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
    $ret=array();
    foreach(ensureIsArray($data) as $val)
      $ret[] = strval(round(intval($val) / 86400));
    return $ret;
  }

}
