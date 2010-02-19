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
 * Type d'attribut Ldap postalAddress
 * RFC4519 : les retours a la ligne sont remplace par des '$'
 */
class LSattr_ldap_postalAddress extends LSattr_ldap {

  /**
   * Retourne la valeur d'affichage de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur d'affichage de l'attribut
   */
  function getDisplayValue($data) {
    return str_replace("$","\n",$data);
  }

  /**
   * Retourne la valeur de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur traitée de l'attribut
   */
  function getUpdateData($data) {
    return str_replace("\n","$",$data);
  }
}

?>
