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
 * Type d'attribut Ldap boolean
 *
 */
class LSattr_ldap_boolean extends LSattr_ldap {

  /**
   * Retourne la valeur d'affichage de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur d'affichage de l'attribut
   */
  function getDisplayValue($data) {
    if ($this -> isTrue($data))
      return 'yes';
    if ($this -> isFalse($data))
      return 'no';
    return;
  }

  /**
   * Retourne la valeur de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur traitée de l'attribut
   */
  function getUpdateData($data) {
    if ($data[0]=='yes') {
      return array($this -> config['ldap_options']['true_value']);
    }
    if ($data[0]=='no') {
      return array($this -> config['ldap_options']['false_value']);
    }
    return array();
  }
 
  /**
   * Determine si la valeur passé en paramètre correspond a True ou non
   *
   * @param[in] $data La valeur de l'attribut
   *
   * @retval boolean True ou False
   */
  function isTrue($data) {
    if (!is_array($data)) {
      $data=array($data);
    }
    if ($data[0] == $this -> config['ldap_options']['true_value']) {
      return true;
    }
    return;
  }
  
  /**
   * Determine si la valeur passé en paramètre correspond a False ou non
   *
   * @param[in] $data La valeur de l'attribut
   *
   * @retval boolean True ou False
   */
  function isFalse($data) {
    if (!is_array($data)) {
      $data=array($data);
    }
    if ($data[0] == $this -> config['ldap_options']['false_value']) {
      return true;
    }
    return;
  }
}

?>
