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
 * Element quota d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments quota des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_quota extends LSformElement {

  var $fieldTemplate = 'LSformElement_quota_field.tpl';

  var $sizeFacts = array(
    1     => 'o',
    1024   => 'Ko',
    1048576  => 'Mo',
    1073741824 => 'Go',
    1099511627776 => 'To',
  );

  /**
   * Parse one value
   *
   * @param[in] $value string The value to parse
   * @param[in] $details boolean Enable/disable details return (optional, default: true)
   *
   * @retval array Parsed value
   */
  public function parseValue($value, $details=true) {
    if (preg_match('/^([0-9]+)$/', $value, $regs)) {
      $infos = array(
        'size' => ceil($regs[1]/$this -> getFactor()),
      );
      if (!$details)
        return $infos['size'];
      if ($infos['size'] == 0) {
        return array(
          'size' => 0,
          'valueSize' => 0,
          'valueSizeFact' => 1,
          'valueTxt' => "0",
        );
      }
      krsort($this -> sizeFacts, SORT_NUMERIC);
      foreach($this -> sizeFacts as $fact => $unit) {
        if ($infos['size'] >= $fact) {
          $infos['valueSize'] = round($infos['size'] / $fact, 2);
          $infos['valueSizeFact'] = $fact;
          $infos['valueTxt'] = $infos['valueSize'].$unit;
          break;
        }
      }
      ksort($this -> sizeFacts, SORT_NUMERIC);
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

    $quotas=array();

    foreach ($this -> values as $value) {
      $parsed_value = $this -> parseValue($value);
      if ($parsed_value) {
        $quotas[$value] = $parsed_value;
      }
      else {
        $quotas[$value] = array(
          'unknown' => _('Incorrect value')
        );
      }
    }

    LStemplate :: addCssFile('LSformElement_quota.css');

    $return['html'] = $this -> fetchTemplate(
      NULL,
      array(
        'quotas' => $quotas,
        'sizeFacts' => $this -> sizeFacts
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
        'sizeFacts' => $this -> sizeFacts,
      )
    );
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
    $values = array();
    if ($this -> form -> api_mode) {
      if (isset($_POST[$this -> name])) {
        foreach(ensureIsArray($_POST[$this -> name]) as $value) {
          $values[] = ceil($value*$this -> getFactor());
        }
      }
    }
    elseif (isset($_POST[$this -> name.'_size'])) {
      $_POST[$this -> name.'_size'] = ensureIsArray($_POST[$this -> name.'_size']);
      if(isset($_POST[$this -> name.'_sizeFact']) && !is_array($_POST[$this -> name.'_sizeFact'])) {
        $_POST[$this -> name.'_sizeFact'] = array($_POST[$this -> name.'_sizeFact']);
      }
      foreach($_POST[$this -> name.'_size'] as $key => $val) {
        if (empty($val))
          continue;
        $f = 1;
        if (isset($_POST[$this -> name.'_sizeFact'][$key]) && ($_POST[$this -> name.'_sizeFact'][$key]!=1)) {
          $f = $_POST[$this -> name.'_sizeFact'][$key];
        }
        $val = preg_replace('/,/', '.', $val);
        $values[$key] = ceil(ceil(($val*$f)*$this -> getFactor()));
      }
    }

    if ($values) {
      $return[$this -> name] = $values;
    }
    elseif ($onlyIfPresent) {
      self :: log_debug($this -> name.": not in POST data => ignore it");
    }
    else {
      $return[$this -> name] = array();
    }
    return true;
  }

  /**
   * Retreive factor value
   *
   * @retval integer Factor value
   */
  private function getFactor() {
    return $this -> getParam('html_options.factor', 1, 'int');
  }

}
