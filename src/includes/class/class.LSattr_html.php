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
 * Base d'un type d'attribut HTML
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_html extends LSlog_staticLoggerClass {

  var $name;
  var $config;
  var $attribute;
  var $LSformElement_type = false;

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
   * Allow conversion of LSattr_html to string
   *
   * @retval string The string representation of the LSattr_html
   */
  public function __toString() {
    return "<".get_class($this)." ".$this -> name.">";
  }

  /**
   * Retourne le label de l'attribut
   *
   * Retourne le label de l'attribut ou son nom si aucun label n'est défini
   * dans la configuration.
   *
   * @retval string Le label de l'attribut.
   */
  public function getLabel() {
    return __($this -> getConfig('label', $this -> name));
  }

  /**
   * Ajoute l'attribut au formualaire passer en paramètre
   *
   * @param[in] &$form LSform Le formulaire
   * @param[in] $idForm L'identifiant du formulaire
   * @param[in] $data Valeur du champs du formulaire
   *
   * @retval LSformElement L'element du formulaire ajouté
   */
  public function addToForm (&$form,$idForm,$data=NULL) {
    if (!$this -> LSformElement_type) {
      LSerror :: addErrorCode('LSattr_html_01',$this -> name);
      return;
    }
    $element=$form -> addElement($this -> LSformElement_type, $this -> name, $this -> getLabel(), $this -> config, $this);
    if(!$element) {
      LSerror :: addErrorCode('LSform_06',$this -> name);
      return;
    }
    if (!is_null($data))
      $element -> setValue($data);
    return $element;
  }

  /**
   * Effectue les tâches nécéssaires au moment du rafraichissement du formulaire
   *
   * @param[in] $data mixed La valeur de l'attribut
   *
   * @retval mixed La valeur formatée de l'attribut
   **/
  public function refreshForm($data) {
    return $data;
  }

  /**
   * Return the values to be displayed in the LSform
   *
   * @param[in] $data The values of attribute
   *
   * @retval array The values to be displayed in the LSform
   **/
  public function getFormVal($data) {
    return $this -> attribute -> getDisplayValue();
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

/*
 * Error Codes
 */
LSerror :: defineError('LSattr_html_01',
___("LSattr_html : The method addToForm() of the HTML type of the attribute %{attr} is not defined.")
);
// 02 : not yet used
LSerror :: defineError('LSattr_html_03',
___("LSattr_html_%{type} : Multiple data are not supported for this field type.")
);
