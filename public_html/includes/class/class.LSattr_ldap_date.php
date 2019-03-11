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
 * Type d'attribut Ldap date
 *
 */
class LSattr_ldap_date extends LSattr_ldap {

  /**
   * Retourne la valeur d'affichage de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur d'affichage de l'attribut
   */
  function getDisplayValue($data) {
    if(!is_array($data)) {
      $data=array($data);
    }
    if ($this -> getConfig('ldap_options.timestamp', false, 'bool')) {
      return $data;
    }
    $retval=array();
    foreach($data as $val) {
      $date = strptime($val,$this -> getFormat());
      if (is_array($date)) {
        $retval[] = mktime($date['tm_hour'],$date['tm_min'],$date['tm_sec'],$date['tm_mon']+1,$date['tm_mday'],$date['tm_year']+1900); 
      }
    }
    return $retval;
  }

  /**
   * Retourne la valeur de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur traitée de l'attribut
   */
  function getUpdateData($data) {
    if ($this -> getConfig('ldap_options.timestamp', false, 'bool')) {
      return $data;
    }
    $retval=array();
    if(is_array($data)) {
      foreach($data as $val) {
        $retval[] = strftime($this -> getFormat(),$val);
      }
    }
    return $retval;
  }
  
 /**
  * Retourne le format de stockage de la date
  * 
  * @retval string Le format de la date
  **/
  function getFormat() {
    return $this -> getConfig('ldap_options.format', '%Y%m%d%H%M%SZ');
  }

}

