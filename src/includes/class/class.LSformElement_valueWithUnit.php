<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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
 * Element valueWithUnit d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments valueWithUnit des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_valueWithUnit extends LSformElement {

  var $fieldTemplate = 'LSformElement_valueWithUnit_field.tpl';

 /**
  * Retourne les unites de l'attribut
  *
  * @retval array|False Le tableau contenant en cle les seuils et en valeur les labels des unites.
  *                     Si le parametre units n'est pas defini, cette fonction retournera False
  **/
  public function getUnits() {
    $units = $this -> getParam('html_options.units');
    if (is_array($units)) {
      if ($this -> getParam('html_options.translate_labels', true)) {
        foreach($units as $sill => $label) {
          $units[$sill] = __($label);
        }
      }
      krsort($units);
      return $units;
    }
    LSerror :: addErrorCode('LSformElement_valueWithUnit_01', $this -> name);
    return;
  }

 /**
  * Return formatted number
  *
  * This method return take a number as paremeter and
  * return it after formatting.
  *
  * @param[in] int|float $number The number
  *
  * @retbal string Formatted number
  */
  public function formatNumber($number) {
    if ((int)$number==$number) return $number;
    return number_format($number,
      $this -> getParam('html_options.nb_decimals', 2, 'int'),
      $this -> getParam('html_options.dec_point', ',', 'string'),
      $this -> getParam('html_options.thousands_sep', ' ', 'string')
    );
  }

  /**
   * Parse one value
   *
   * @param[in] $value string The value to parse
   * @param[in] $details boolean Enable/disable details return (optional, default: true)
   *
   * @retval array Parsed value
   */
  public function parseValue($value, $details=true) {
    if (preg_match('/^([0-9]*)$/' ,$value, $regs)) {
      $infos = array(
        'value' => intval($regs[1]),
      );
      if (!$details)
        return $infos['value'];
      $units = $this -> getUnits();
      if (!$units)
        return $infos;
      if ($infos['value'] == 0) {
        ksort($units);
        $infos['valueWithUnit'] = $this -> formatNumber(0);
        $infos['unitSill'] = key($units);
        $infos['unitLabel'] = $units[$infos['unitSill']];
        return $infos;
      }

      foreach($units as $sill => $label) {
        if ($infos['value'] >= $sill) {
          $infos['valueWithUnit'] = $this -> formatNumber($infos['value']/$sill);
          $infos['unitSill'] = $sill;
          $infos['unitLabel'] = $label;
          break;
        }
      }

      return $infos;
    }
    return false;
  }

 /**
  * Retourne les infos d'affichage de l'élément
  *
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  public function getDisplay(){
    $return = $this -> getLabelInfos();

    $values_and_units=array();
    $units=$this -> getUnits();

    if ($units) {
      foreach ($this -> values as $value) {
        $parsedValue = $this -> parseValue($value);
        if ($parsedValue === false) {
          $values_and_units[$value] = array(
            'unknown' => _('Incorrect value')
          );
        }
        else {
          $values_and_units[$value] = $parsedValue;
        }
      }
    }

    LStemplate :: addCssFile('LSformElement_valueWithUnit.css');

    $return['html']=$this -> fetchTemplate(
      NULL,
      array(
        'values_and_units' => $values_and_units,
        'units' => $units
      )
    );
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
        'units' => $this -> getUnits(),
      )
    );
  }

  /**
   * Recupère la valeur de l'élement passée en POST
   *
   * Cette méthode vérifie la présence en POST de la valeur de l'élément et la récupère
   * pour la mettre dans le tableau passer en paramètre avec en clef le nom de l'élément
   *
   * @param[in] &$return array Reference of the array for retrieved values
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  public function getPostData(&$return, $onlyIfPresent=false) {
    if($this -> isFreeze()) {
      return true;
    }

    if ($this -> form -> api_mode) {
      if (isset($_POST[$this -> name]) && $_POST[$this -> name]) {
        $return[$this -> name] = array();
        foreach(ensureIsArray($_POST[$this -> name]) as $value) {
          if ($this -> getParam('html_options.store_integer')) {
            $value = ($this -> getParam('html_options.round_down')?floor($value):ceil($value));
          }
          $return[$this -> name][] = $value;
        }
      }
    }
    else {
      $return[$this -> name] = array();
      if (isset($_POST[$this -> name.'_valueWithUnit'])) {
        $_POST[$this -> name.'_valueWithUnit'] = ensureIsArray($_POST[$this -> name.'_valueWithUnit']);
        if(isset($_POST[$this -> name.'_unitFact']) && !is_array($_POST[$this -> name.'_unitFact'])) {
          $_POST[$this -> name.'_unitFact'] = array($_POST[$this -> name.'_unitFact']);
        }
        foreach($_POST[$this -> name.'_valueWithUnit'] as $key => $val) {
          if (empty($val))
            continue;
          $f = 1;
          if (isset($_POST[$this -> name.'_unitFact'][$key]) && ($_POST[$this -> name.'_unitFact'][$key]!=1)) {
            $f = $_POST[$this -> name.'_unitFact'][$key];
          }
          if ($this -> getParam('html_options.store_integer')) {
            $return[$this -> name][$key] = (
              $this -> getParam('html_options.round_down')?
              floor($val*$f):
              ceil($val*$f)
            );
          }
          else {
            $return[$this -> name][$key] = ($val*$f);
          }
        }
      }

      if (isset($_POST[$this -> name.'_value'])) {
        $_POST[$this -> name.'_value'] = ensureIsArray($_POST[$this -> name.'_value']);
        $return[$this -> name] = array_merge($return[$this -> name], $_POST[$this -> name.'_value']);
      }
    }
    return true;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSformElement_valueWithUnit_01',
___("LSformElement_valueWithUnit : Units configuration data are missing for the attribute %{attr}.")
);
