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

LSsession :: loadLSclass('LSformElement');
LSsession :: loadLSaddon('supann');

/**
 * Element supannLabeledValue d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannLabeledValue des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannLabeledValue extends LSformElement {

  var $template = 'LSformElement_supannLabeledValue.tpl';
  var $fieldTemplate = 'LSformElement_supannLabeledValue_field.tpl';

  var $supannNomenclatureTable = null;
  var $supannLabelNomenclatureTable = null;

  // HTML field type: text or textarea (only for field without nomenclature table)
  var $valueFieldType = 'text';

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
    $possibleLabels = (
      $this -> supannLabelNomenclatureTable?
      supannGetNomenclaturePossibleValues($this -> supannLabelNomenclatureTable, false):
      null
    );
    $return['html'] = $this -> fetchTemplate(
      NULL, array(
        'parseValues' => $parseValues,
        'nomenclatureTable' => $this -> supannNomenclatureTable,
        'possibleLabels' => $possibleLabels,
        'valueFieldType' => $this -> valueFieldType,
      )
    );
    LStemplate :: addCssFile('LSformElement_supannLabeledValue.css');
    if (!$this -> isFreeze()) {
      LStemplate :: addJSconfigParam(
        $this -> name,
        array(
          'nomenclatureTable' => boolval($this -> supannNomenclatureTable),
          'searchBtn' => _('Modify'),
          'noValueLabel' => _('No set value'),
          'noResultLabel' => _('No result'),
        )
      );
      LStemplate :: addJSscript('LSformElement_supannLabeledValue_field_value.js');
      LStemplate :: addJSscript('LSformElement_supannLabeledValue_field.js');
      LStemplate :: addJSscript('LSformElement_supannLabeledValue.js');
    }
    return $return;
  }

 /**
  * Parse une valeur
  *
  * @param[in] $value La valeur
  *
  * @retval array Un tableau cle->valeur contenant value, translated et label
  **/
  public function parseValue($value) {
    $retval = array(
      'value' => $value,
    );
    $pv = supannParseLabeledValue($value);
    if ($pv) {
      $retval['label'] = $pv['label'];
      if ($this -> supannLabelNomenclatureTable)
        $retval['translated_label'] = supannGetNomenclatureLabel($this -> supannLabelNomenclatureTable, null, $pv['label']);
      if ($this -> supannNomenclatureTable)
        $retval['translated'] = supannGetNomenclatureLabel($this -> supannNomenclatureTable,$pv['label'],$pv['value']);
      else
        $retval['translated'] = $pv['value'];
    }
    else {
      $retval['label'] = 'no';
      $retval['translated'] = getFData(__('%{value} (Unparsable value)'), $value);
    }
    return $retval;
  }


  /**
   * This ajax method is used by the searchPossibleValues function of the form element.
   *
   * @param[in] $data The address to the array of data witch will be return by the ajax request
   *
   * @retval boolean True on success, False otherwise
   **/
  public static function ajax_searchPossibleValues(&$data) {
    // Check all parameters is provided
    foreach(array('attribute', 'objecttype', 'pattern', 'idform') as $parameter)
      if (!isset($_REQUEST[$parameter]))
        return;
    if (!LSsession ::loadLSobject($_REQUEST['objecttype']))
      return;
    $object = new $_REQUEST['objecttype']();
    $form = $object -> getForm($_REQUEST['idform']);
    $field = $form -> getElement($_REQUEST['attribute']);
    $data['possibleValues'] = $field -> searchPossibleValues($_REQUEST['pattern']);
    return true;
  }

  /**
   * Real private method to search possible values from pattern.
   *
   * @param[in] $pattern The search pattern
   *
   * @retval boolean|array Array of possible values, or False is case of error
   **/
  private function searchPossibleValues($pattern) {
    if (!$this -> supannNomenclatureTable)
      return false;
    $pattern=withoutAccents(strtolower($pattern));
    $retval=array();
    $table=supannGetNomenclatureTable($this -> supannNomenclatureTable);
    foreach($table as $label => $values) {
      foreach($values as $v => $txt) {
        if (strpos(withoutAccents(strtolower($txt)),$pattern)!==false) {
          $retval[]=array(
            'label' => $label,
            'value' => "{".$label."}".$v,
            'translated' => $txt
          );
        }
      }
    }
    return $retval;
  }

}
