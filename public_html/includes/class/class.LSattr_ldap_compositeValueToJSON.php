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
 * Type d'attribut Ldap compositeValueToJSON
 * Convertit les attributes composite du format suivant :
 *   [key1=value1][key2=value2]..
 * Au format JSON (utilisable par le LSformElement_jsonCompositeAttribute ) :
 *   {"key1":"value1","key2":"value2"}
 */
class LSattr_ldap_compositeValueToJSON extends LSattr_ldap {

  /**
   * Retourne la valeur d'affichage de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur d'affichage de l'attribut
   */
  public function getDisplayValue($data) {
    if ($data) {
      if (!is_array($data))
        $data = array($data);
      $ret = array();
      foreach($data as $key => $val)
        $ret[$key] = json_encode(self :: parseValue($val));
      return $ret;
    }
    return $data;
  }

  /**
   * Retourne la valeur de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur traitée de l'attribut
   */
  public function getUpdateData($data) {
    if ($data) {
      if (!is_array($data))
        $data = array($data);
      $ret = array();
      foreach($data as $key => $val)
        $ret[$key] = self :: encodeValue(json_decode($val, true));
      return $ret;
    }
    return $data;
  }

  public static function parseValue($value) {
    if (preg_match_all('/\[([^=]*)=([^\]]*)\]/',$value,$matches)) {
      $parseValue=array();
      for($i=0;$i<count($matches[0]);$i++) {
        $parseValue[$matches[1][$i]]=$matches[2][$i];
      }
      return $parseValue;
    }
    return;
  }

  public static function encodeValue($value) {
    if (is_array($value)) {
      $ret="";
      foreach($value as $key => $val)
        $ret.="[$key=$val]";
      return $ret;
    }
    return False;
  }

}

