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
 * Type d'attribut Ldap password
 *
 */
class LSattr_ldap_password extends LSattr_ldap {

  var $clearPassword = NULL;

  /**
   * Retourne la valeur d'affichage de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur d'affichage de l'attribut
   */
  function getDisplayValue($data) {
    return '********';
  }

  /**
   * Retourne la valeur de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur traitée de l'attribut
   */
  function getUpdateData($data) {
    $this -> clearPassword = $data[0];
    return '{CRYPT}'.crypt($data[0],'$1$'.$this -> getSalt().'$');
  }
 
  /**
   * Retourne une salt (chaine de caractère aléatoire) de la longueur passée en paramètre
   *
   * @param[in] integer La longueur de la salt (par defaut : 8)
   *
   * @retval string La salt
   */
  function getSalt($length=8) {
    $pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
    $key  = $pattern{rand(0,35)};
    for($i=1;$i<$length;$i++)
    {
        $key .= $pattern{rand(0,35)};
    }
    return $key;
  }

  /**
   * Retourne le mot de passe en texte clair
   *
   * @retval string Le mot de passe en texte clair
   */
  function getClearPassword() {
    return $this -> clearPassword;
  }
}

?>
