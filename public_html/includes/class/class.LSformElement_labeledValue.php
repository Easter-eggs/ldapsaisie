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

LSsession :: loadLSclass('LSformElement');

/**
 * Element labeledValue d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments labeledValue des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_labeledValue extends LSformElement {

  var $template = 'LSformElement_labeledValue.tpl';
  var $fieldTemplate = 'LSformElement_labeledValue_field.tpl';
  
 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();

    $parseValues=array();
    foreach($this -> values as $val) {
      $parseValues[]=$this -> parseValue($val);
    }
    $return['html'] = $this -> fetchTemplate(NULL,array(
      'labels' => $this -> params['html_options']['labels'],
      'parseValues' => $parseValues,
      'unrecognizedValueTxt' => __('(unrecognized value)'),
      'unrecognizedLabelTxt' => __('(unrecognized label)'),
    ));
    return $return;
  }

 /**
  * Retourne le code HTML d'un champ vide
  *
  * @retval string Code HTML d'un champ vide.
  */
  function getEmptyField() {
    return $this -> fetchTemplate($this -> fieldTemplate,array(
      'labels' => $this -> params['html_options']['labels'],
    ));
  }


 /**
  * Parse une valeur
  *
  * @param[in] $value La valeur
  *
  * @retval array Un tableau cle->valeur contenant value et label
  **/
  function parseValue($value) {
    $ret=array('raw_value' => $value);
    if (preg_match('/^\[([^\]]*)\](.*)$/',$value,$m)) {
      $ret['label'] = $m[1];
      if (isset($this -> params['html_options']['labels'][$ret['label']]))
	      $ret['translated_label'] = $this -> params['html_options']['labels'][$ret['label']];
      $ret['value'] = $m[2];
    }
    return $ret;
  }

  /**
   * Recupère la valeur de l'élement passée en POST
   *
   * Cette méthode vérifie la présence en POST de la valeur de l'élément et la récupère
   * pour la mettre dans le tableau passer en paramètre avec en clef le nom de l'élément
   *
   * @param[] array Pointeur sur le tableau qui recupèrera la valeur.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  function getPostData(&$return) {
    if($this -> isFreeze()) {
      return true;
    }
    if (isset($_POST[$this -> name."_labels"]) && isset($_POST[$this -> name."_values"])) {
      $return[$this -> name]=array();
      if(!is_array($_POST[$this -> name."_labels"])) {
        $_POST[$this -> name."_labels"] = array($_POST[$this -> name."_labels"]);
      }
      if(!is_array($_POST[$this -> name."_values"])) {
        $_POST[$this -> name."_values"] = array($_POST[$this -> name."_values"]);
      }
      foreach($_POST[$this -> name."_labels"] as $key => $label) {
        $val=$_POST[$this -> name."_values"][$key];
        if (!empty($label) && (!empty($val)||(is_string($val)&&($val=="0")))) {
          $return[$this -> name][$key] = "[$label]$val";
        }
      }
      return true;
    }
    else {
      $return[$this -> name] = array();
      return true;
    }
  }

}
