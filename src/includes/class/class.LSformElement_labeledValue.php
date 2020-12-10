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
  public function getDisplay(){
    $return = $this -> getLabelInfos();

    $parseValues=array();
    foreach($this -> values as $val) {
      $parseValues[]=$this -> parseValue($val);
    }

    // Translate labels
    $labels = $this -> getParam('html_options.labels', array());
    if ($this -> getParam('html_options.translate_labels', true, 'bool')) {
      foreach($labels as $value => $label)
        $labels[$value] = __($label);
    }

    $return['html'] = $this -> fetchTemplate(NULL,array(
      'labels' => $labels,
      'parseValues' => $parseValues,
      'unrecognizedValueTxt' => __('(unrecognized value)'),
      'unrecognizedLabelTxt' => __('(unrecognized label)'),
    ));
    return $return;
  }

  /**
   * Return HTML code of an empty form field
   *
   * @param[in] $value_idx integer|null The value index (optional, default: null == 0)
   *
   * @retval string The HTML code of an empty field
   */
  public function getEmptyField($value_idx=null) {
    return $this -> fetchTemplate(
      $this -> fieldTemplate,
      array(
        'value' => null,
        'value_idx' => intval($value_idx),
        'labels' => $this -> getParam('html_options.labels'),
      )
    );
  }

 /**
  * Parse une valeur
  *
  * @param[in] $value La valeur
  *
  * @retval array Un tableau cle->valeur contenant value et label
  **/
  public function parseValue($value) {
    $ret=array('raw_value' => $value);
    if (preg_match('/^\[([^\]]*)\](.*)$/',$value,$m)) {
      $ret['label'] = $m[1];
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
   * @param[in] &$return array Reference of the array for retreived values
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  public function getPostData(&$return, $onlyIfPresent=false) {
    if($this -> isFreeze()) {
      return true;
    }
    if (isset($_POST[$this -> name."_labels"]) && isset($_POST[$this -> name."_values"])) {
      $return[$this -> name] = array();
      $_POST[$this -> name."_labels"] = ensureIsArray($_POST[$this -> name."_labels"]);
      $_POST[$this -> name."_values"] = ensureIsArray($_POST[$this -> name."_values"]);
      foreach($_POST[$this -> name."_labels"] as $key => $label) {
        $val = $_POST[$this -> name."_values"][$key];
        if (!empty($label) && !is_empty($val)) {
          $return[$this -> name][$key] = "[$label]$val";
        }
      }
      return true;
    }
    elseif ($onlyIfPresent) {
      self :: log_debug($this -> name.": not in POST data => ignore it");
      return true;
    }
    else {
      $return[$this -> name] = array();
      return true;
    }
  }

}
