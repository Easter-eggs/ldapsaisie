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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Base d'un type d'attribut Ldap
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_ldap extends LSlog_staticLoggerClass {

  var $name;
  var $config;
  var $attribute;

  /**
   * Constructeur
   *
   * Cette methode construit l'objet et définis la configuration.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $name string Nom de l'attribut ldap
   * @param[in] $config array Configuration de l'objet
   * @param[in] &$attribute LSattribute L'objet LSattribut parent
   *
   * @retval boolean Retourne true.
   */
  public function __construct($name, $config, &$attribute) {
    $this -> name = $name;
    $this -> config = $config;
    $this -> attribute =& $attribute;
    return true;
  }

  /**
   * Allow conversion of LSattr_ldap to string
   *
   * @retval string The string representation of the LSattr_ldap
   */
  public function __toString() {
    return "<".get_class($this)." ".$this -> name.">";
  }

  /**
   * Retourne la valeur de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur traitée de l'attribut
   */
  public function getUpdateData($data) {
    return $data;
  }

  /**
   * Retourne la valeur d'affichage de l'attribut après traitement lié à son type ldap
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur d'affichage de l'attribut
   */
  public function getDisplayValue($data) {
    return $data;
  }

  /**
   * Retourne vrai si la valeur passé en paramètre n'était pas la même que la
   * valeur passer au formulaire
   *
   * @param[in] $data mixed La valeur a tester
   *
   * @retval boolean True uniquement si la valeur passer en paramètre différe de l'actuelle
   */
  public function isUpdated($data) {
    $data=$this -> getUpdateData($data);
    if ($this -> attribute -> data != $data) {
      return true;
    }
    return;
  }

  /**
   * Return a configuration parameter (or default value)
   *
   * @param[] $param	The configuration parameter
   * @param[] $default	The default value (default : null)
   * @param[] $cast	Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  public function getConfig($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, $this -> config);
  }

}
