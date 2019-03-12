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
 * Boolean LDAP attribute type
 **/
class LSattr_ldap_boolean extends LSattr_ldap {

  /**
   * Return display value of attribute after treatment related to LDAP type
   *
   * @param[in] $data mixed Attribute data
   *
   * @retval mixed Attribute display value
   **/
  public function getDisplayValue($data) {
    if ($this -> isTrue($data))
      return 'yes';
    if ($this -> isFalse($data))
      return 'no';
    return;
  }

  /**
   * Return attribute value after treatment related to LDAP type
   *
   * @param[in] $data mixed Attribute data
   *
   * @retval mixed Attribute data
   **/
  public function getUpdateData($data) {
    if ($data[0]=='yes') {
      return array($this -> getTrue());
    }
    if ($data[0]=='no') {
      return array($this -> getFalse());
    }
    return array();
  }
 
  /**
   * Check if a value corresponding to True
   *
   * @param[in] $data Attribute data
   *
   * @retval boolean True or False
   **/
  public function isTrue($data) {
    if (!is_array($data)) {
      $data=array($data);
    }
    if ($data[0] == $this -> getTrue()) {
      return true;
    }
    return;
  }
  
  /**
   * Check if a value corresponding to False
   *
   * @param[in] $data Attribute data
   *
   * @retval boolean True or False
   **/
  public function isFalse($data) {
    if (!is_array($data)) {
      $data=array($data);
    }
    if ($data[0] == $this -> getFalse()) {
      return true;
    }
    return;
  }

  /**
   * Return True value
   *
   * @retval string The True value
   **/
  public function getTrue() {
    return $this -> getConfig('ldap_options.true_value', 'TRUE', 'string');
  }

  /**
   * Return False value
   *
   * @retval string The False value
   **/
  public function getFalse() {
    return $this -> getConfig('ldap_options.false_value', 'FALSE', 'string');
  }

}

