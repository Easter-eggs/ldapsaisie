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
 * Base d'un type d'attribut HTML
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_html {
  
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
  function LSattr_html ($name,$config,&$attribute) {
    $this -> name = $name;
    $this -> config = $config;
    $this -> attribute = $attribute;
    return true;
  }
  
  /**
   * Retourne le label de l'attribut
   *
   * Retourne le label de l'attribut ou son nom si aucun label n'est défini
   * dans la configuration.
   *
   * @retval string Le label de l'attribut.
   */
  function getLabel() {
    if ( $this -> config['label'] != '' ) {
      return $this -> config['label'];
    }
    else {
      return $this -> name;
    }
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
  function addToForm (&$form,$idForm,$data=NULL) {
    if (!$this -> LSformElement_type) {
      LSerror :: addErrorCode('LSattr_html_01',$this -> name);
      return;
    }
    $element=$form -> addElement($this -> LSformElement_type, $this -> name, $this -> config['label'],$this -> config, $this);
    if(!$element) {
      LSerror :: addErrorCode('LSform_06',$this -> name);
      return;
    }
    if ($data) {
      $element -> setValue($data);
    }
    return $element; 
  }
  
  /**
   * Effectue les tâches nécéssaires au moment du rafraichissement du formulaire
   * 
   * @param[in] $data mixed La valeur de l'attribut
   * 
   * @retval mixed La valeur formatée de l'attribut
   **/
  function refreshForm($data) {
    return $data;
  }
}

/*
 * Error Codes
 */
LSerror :: defineError('LSattr_html_01',
_("LSattr_html : The method addToForm() of the HTML type of the attribute %{attr} is not defined.")
);
// 02 : not yet used
LSerror :: defineError('LSattr_html_03',
_("LSattr_html_%{type} : Multiple data are not supported for this field type.")
);

?>
