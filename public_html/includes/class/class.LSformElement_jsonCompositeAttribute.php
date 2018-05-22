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
 * Element jsonCompositeAttribute for LSform
 *
 * This classe permit to handle compostie attributes encoded with JSON.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_jsonCompositeAttribute extends LSformElement {

  var $template = 'LSformElement_jsonCompositeAttribute.tpl';
  var $fieldTemplate = 'LSformElement_jsonCompositeAttribute_field.tpl';

  function LSformElement_jsonCompositeAttribute (&$form, $name, $label, $params,&$attr_html){
    parent :: LSformElement($form, $name, $label, $params,$attr_html);
    if (is_array($this -> params['html_options']['components'])) {
      $this -> components = $this -> params['html_options']['components'];
    }
  }

  /*
   * Value components :
   *
   * Format :
   *   array (
   *     '[component1_key]' => array (
   *       'label' => '[component label]',
   *       'type' => '[component type]',
   *       'required' => '[booléen]',
   *       'check_data' =>  array([config LSform_rule])
   *     ),
   *     '[component2_key]' => array (
   *       'label' => 'label2',
   *       'type'  => 'select_list',
   *       'options' => array([config as LSattr_html_select_list html_options]),
   *     ),
   *     [...]
   *   )
   * Types :
   *   - 'select_list' => Component feed by a list of valeur configured like an
   *                      atribute LSattr_html :: select_list.
   *   - 'text'        => manual entry
   *
   */
  var $components = array();

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
    $invalidValues=array();
    foreach($this -> values as $val) {
      $decodedValue=json_decode($val, true);
      if (is_array($decodedValue)) {
        $parseValue=array('value' => $val);
        foreach($decodedValue as $c => $cvalue) {
          $parseValue[$c]=$this -> translateComponentValue($c,$cvalue);
        }
        $parseValues[]=$parseValue;
      }
      else {
        $invalidValues[]=$val;
      }
    }

    $return['html'] = $this -> fetchTemplate(NULL,
      array(
        'parseValues' => $parseValues,
      )
    );
    LSsession :: addCssFile('LSformElement_jsonCompositeAttribute.css');
    if (!$this -> isFreeze()) {
        LSsession :: addJSconfigParam(
            $this -> name,
            array (
                'components' => $this -> components,
            )
        );
        LSsession :: addJSscript('LSformElement_jsonCompositeAttribute_field_value_component_text_value.js');
        LSsession :: addJSscript('LSformElement_jsonCompositeAttribute_field_value_component.js');
        LSsession :: addJSscript('LSformElement_jsonCompositeAttribute_field_value.js');
        LSsession :: addJSscript('LSformElement_jsonCompositeAttribute_field.js');
        LSsession :: addJSscript('LSformElement_jsonCompositeAttribute.js');
    }

    return $return;
  }

 /**
  * Retournne un template Smarty compilé dans le contexte d'un LSformElement
  *
  * @param[in] string $template Le template à retourner
  * @param[in] array $variables Variables Smarty à assigner avant l'affichage
  *
  * @retval string Le HTML compilé du template
  */
  function fetchTemplate($template=NULL,$variables=array()) {
    $components = $this -> components;
    foreach($components as $c => $cconf) {
      if ($cconf['type']=='select_list') {
        $components[$c]['possible_values']=$this -> getSelectListComponentPossibleValues($c);
      }
    }
    $variables['components'] = $components;

    return parent::fetchTemplate($template, $variables);
  }

  /**
   * Translate componant value
   *
   * Return an array containing :
   *  - value : untranslated value
   *  - translated : translated value
   *
   * @param[in] $c string The component name
   * @param[in] $value string The value
   * @param[in] $inLoop boolean Internal param to control recursion
   *
   * @retval array
   **/
  function translateComponentValue($c,$value,$inLoop=false) {
    if (!$inLoop && isset($this -> components[$c]['multiple']) && $this -> components[$c]['multiple']) {
      $retval = array();
      if (!is_array($value))
        $value = array($value);
      foreach($value as $val)
        $retval[] = $this -> translateComponentValue($c, $val, true);
    }
    else {
      $retval = array (
        'translated' => $value,
        'value' => $value,
      );
      if (isset($this -> components[$c])) {
        if ($this -> components[$c]['type']=='select_list') {
          $retval['translated'] = $this -> getSelectListComponentValueLabel($c,$value);
        }
        //elseif type == 'text' => no transformation
      }
    }
    return $retval;
  }

  /**
   * Retreive possible values of an select_list component
   *
   * @param[in] $c string The component name
   *
   * @retval array
   **/
  protected $_cache_getSelectListComponentPossibleValues=array();
  protected function getSelectListComponentPossibleValues($c) {
    if (!isset($this -> _cache_getSelectListComponentPossibleValues[$c])) {
      if (!LSsession :: loadLSclass('LSattr_html_select_list')) return;
      $this -> _cache_getSelectListComponentPossibleValues[$c]=LSattr_html_select_list :: getPossibleValues($this -> components[$c]['options'], $this -> name, $this->attr_html->attribute->ldapObject);
    }
    return $this -> _cache_getSelectListComponentPossibleValues[$c];
  }

  /**
   * Retreive value's label of an select_list component
   *
   * @param[in] $c string The component name
   * @param[in] $value string The value
   *
   * @retval array
   **/
  protected function getSelectListComponentValueLabel($c,$value) {
    if ($this -> getSelectListComponentPossibleValues($c)) {
      foreach ($this -> _cache_getSelectListComponentPossibleValues[$c] as $v => $label) {
        if (is_array($label)) {
          if (!isset($label['possible_values'])) continue;
          foreach ($label['possible_values'] as $vk => $vl)
            if ($vk == $$value) return $vl;
        }
        if ($v == $value) return $label;
      }
    }
    return;
  }

  /**
   * Retreive LSformElement value from POST data
   *
   * This method check present of this element's value in POST data and retreive
   * it to feed the array passed in paramater.
   *
   * @param[] array Reference of the array for retreived values
   *
   * @retval boolean true if value is in POST data, false instead
   */
  function getPostData(&$return) {
    if($this -> isFreeze()) {
      return true;
    }

    $return[$this -> name]=array();
    if (is_array($_POST[$this -> name.'__values_uuid'])) {
      foreach ($_POST[$this -> name.'__values_uuid'] as $uuid) {
        $value=array();
        $parseValue=array();
        $errors=array();
        $unemptyComponents=array();

        foreach ($this -> components as $c => $cconf) {
          if (isset($_POST[$this -> name.'__'.$c.'__'.$uuid])) {
            if (!is_array($_POST[$this -> name.'__'.$c.'__'.$uuid]))
              $_POST[$this -> name.'__'.$c.'__'.$uuid] = array($_POST[$this -> name.'__'.$c.'__'.$uuid]);

            $parseValue[$c]=array();
            foreach($_POST[$this -> name.'__'.$c.'__'.$uuid] as $val) {
              if (empty($val))
                continue;
              $parseValue[$c][] = $val;
              if ($cconf['type']=='select_list') {
                if (!$this -> getSelectListComponentValueLabel($c, $val)) {
                  $errors[]=getFData(_('Invalid value "%{value}" for component %{component}.'),array('value' => $val, 'component' => __($cconf['label'])));
                }
              }
              if (is_array($cconf['check_data'])) {
                foreach($cconf['check_data'] as $ruleType => $rconf) {
                  $className='LSformRule_'.$ruleType;
                  if (LSsession::loadLSclass($className)) {
                    $r=new $className();
                    if (!$r -> validate($val,$rconf,$this)) {
                      if (isset($rconf['msg'])) {
                        $errors[]=getFData(__($rconf['msg']),__($cconf['label']));
                      }
                      else {
                        $errors[]=getFData(_('Invalid value "%{value}" for component %{component}.'),array('value' => $val, 'component' => __($cconf['label'])));
                      }
                    }
                  }
                  else {
                    $errors[]=getFData(_("Can't validate value of component %{c}."),__($cconf['label']));
                  }
                }
              }
            }

            if (!isset($cconf['multiple']) || !$cconf['multiple']) {
              if (count($parseValue[$c])>=1)
                $parseValue[$c] = $parseValue[$c][0];
              else
                $parseValue[$c] = '';
            }

            if ($cconf['required'] && empty($parseValue[$c])) {
              $errors[]=getFData(_('Component %{c} must be defined'),__($cconf['label']));
              continue;
            }
            $unemptyComponents[]=$c;

            $value[$c]=$parseValue[$c];
          }
        }

        if (!empty($unemptyComponents)) {
          foreach($errors as $e) {
            $this -> form -> setElementError($this -> attr_html,$e);
          }
          $return[$this -> name][]=json_encode($value);
        }
      }
    }
    return true;
  }

}
