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
 * Element select d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments select des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_select extends LSformElement {

  var $template = 'LSformElement_select.tpl';
  var $fieldTemplate = 'LSformElement_select.tpl';

 /**
  * Return display data of this element
  *
  * This method return display data of this element
  *
  * @retval array
  */
  public function getDisplay(){
    $return = $this -> getLabelInfos();
    $params = array();
    if (!$this -> isFreeze()) {
      LStemplate :: addHelpInfo(
        'LSformElement_select',
        array(
          'clear' => _("Reset selection.")
        )
      );
      LStemplate :: addJSscript('LSformElement_select.js');
    }
    $params['possible_values'] = $this -> params['text_possible_values'];
    $params['unrecognized_value_label_format'] = _("%{value} (unrecognized value)");
    $return['html'] = $this -> fetchTemplate(NULL,$params);
    return $return;
  }

 /**
  * Check if a value is valid for the current form element
  *
  * This method check if a value is correct, that mean if it's one
  * of the possible values.
  *
  * @param[in] $value The value to check
  *
  * @retval string or False The value's label or False if this value is incorrect
  */
  public function isValidValue($value) {
    return self :: _isValidValue($value, $this -> getParam('text_possible_values'));
  }

 /**
  * Check if a value is valid against specified possible values
  *
  * This method check if a value is correct, that mean if it's one
  * of the possible values.
  *
  * @param[in] $value The value to check
  * @param[in] $possible_values The possible values
  *
  * @retval string or False The value's label or False if this value is incorrect
  */
  public static function _isValidValue($value, $possible_values) {
    if (!is_array($possible_values)) {
      return False;
    }

    $ret=False;
    if (is_array($possible_values) && isset($value)) {
      foreach($possible_values as $key => $name) {
        if (is_array($name)) {
          if (!is_array($name['possible_values'])) continue;
          foreach($name['possible_values'] as $k => $v) {
            if ($k==$value) {
              $ret=$v;
              break;
            }
          }
          if ($ret) break;
        }
        elseif ($key==$value) {
          $ret=$name;
          break;
        }
        if ($ret) break;
      }
    }
    return $ret;
  }

}

/**
 * LSformElement_select_checkIsValidValue template function
 *
 * This function permit to check during template processing
 * if a value is valid. This function get as parameters
 * (in $params) :
 * - $value : the value to check
 * - $possible_values : the possible values of the element
 * As return, this function assign two template variables :
 * - LSformElement_select_isValidValue :
 *     Boolean defining if the value is valid
 * - LSformElement_select_isValidValue_label :
 *     The value's label
 *
 * @param[in] $params The template function parameters
 * @param[in] $template Smarty object
 *
 * @retval void
 **/
function LSformElement_select_checkIsValidValue($params,$template) {
  extract($params);

  $ret = LSformElement_select :: _isValidValue($value, $possible_values);

  if ($ret===False) {
    $label='';
    $ret=false;
  }
  else {
    $label=$ret;
    $ret=true;
  }

  $template -> assign('LSformElement_select_isValidValue',$ret);
  $template -> assign('LSformElement_select_isValidValue_label',$label);
}
LStemplate :: registerFunction('LSformElement_select_checkIsValidValue','LSformElement_select_checkIsValidValue');
