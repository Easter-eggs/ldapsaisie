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

  public function __construct(&$form, $name, $label, $params, &$attr_html){
    parent :: __construct($form, $name, $label, $params,$attr_html);
    $this -> components = $this -> getParam('html_options.components', array());
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
  * Parse values
  *
  * @retval array Parsed values
  */
  private function parseValues() {
    self :: log_trace('values: '.varDump($this -> values));
    $parseValues=array();
    foreach($this -> values as $val) {
      $decodedValue = json_decode($val, true);
      self :: log_trace('decoded value: '.varDump($decodedValue));
      if (is_array($decodedValue)) {
        $parseValue = array('value' => $val);
        foreach($decodedValue as $c => $cvalue) {
          $parseValue[$c] = $this -> translateComponentValue($c,$cvalue);
        }
        $parseValues[] = $parseValue;
      }
    }
    self :: log_trace('parsed values: '.varDump($parseValues));
    return $parseValues;
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

    $return['html'] = $this -> fetchTemplate(NULL,
      array(
        'parseValues' => $this -> parseValues(),
        'fullWidth' => $this -> getParam('html_options.fullWidth', false, 'bool'),
      )
    );
    LStemplate :: addCssFile('LSformElement_jsonCompositeAttribute.css');
    if (!$this -> isFreeze()) {
        LStemplate :: addJSconfigParam(
            $this -> name,
            array (
                'components' => $this -> components,
            )
        );
        LStemplate :: addJSscript('LSformElement_jsonCompositeAttribute_field_value_component_text_value.js');
        LStemplate :: addJSscript('LSformElement_jsonCompositeAttribute_field_value_component.js');
        LStemplate :: addJSscript('LSformElement_jsonCompositeAttribute_field_value.js');
        LStemplate :: addJSscript('LSformElement_jsonCompositeAttribute_field.js');
        LStemplate :: addJSscript('LSformElement_jsonCompositeAttribute.js');
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
  public function fetchTemplate($template=NULL,$variables=array()) {
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
  protected function translateComponentValue($c,$value,$inLoop=false) {
    if (!$inLoop && isset($this -> components[$c]['multiple']) && $this -> components[$c]['multiple']) {
      $retval = array();
      foreach(ensureIsArray($value) as $val)
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
      $this -> _cache_getSelectListComponentPossibleValues[$c] = LSattr_html_select_list :: _getPossibleValues(
        $this -> components[$c]['options'],
        $this -> name,
        $this->attr_html->attribute->ldapObject
      );
      self :: log_trace(
        "Component $c possible values: ".varDump($this -> _cache_getSelectListComponentPossibleValues[$c])
      );
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
  protected function getSelectListComponentValueLabel($c, $value) {
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
    self :: log_trace("No label found for value '$value'");
    return;
  }

  /**
   * Retreive LSformElement value from POST data
   *
   * This method check present of this element's value in POST data and retreive
   * it to feed the array passed in paramater.
   *
   * @param[in] &$return array Reference of the array for retreived values
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   *
   * @retval boolean true if value is in POST data, false instead
   */
  public function getPostData(&$return, $onlyIfPresent=false) {
    if($this -> isFreeze()) {
      return true;
    }

    // Extract value form POST data
    $parseValues = array();
    // API mode
    if ($this -> form -> api_mode) {
      $json_values = $this -> getData($_POST, $this -> name);
      if (!is_array($json_values) || empty($json_values)) {
        self :: log_trace($this." -> getPostData(): not in POST data");
        return true;
      }

      $json_value_count = 0;
      foreach($json_values as $json_value) {
        $json_value_count += 1;
        $input_value = json_decode($json_value, true);
        if (!is_array($input_value)) {
          $this -> form -> setElementError(
            $this -> attr_html,
            getFData(_('Fail to decode JSON value #%{idx}.'), $json_value_count)
          );
          continue;
        }

        $parseValue = array();
        $unemptyComponents = array();

        foreach (array_keys($this -> components) as $c) {
          if (!isset($input_value[$c]))
            continue;
          if ($this -> getComponentConfig($c, 'multiple', false, 'bool')) {
            $parseValue[$c] = array();
            if (is_array($input_value[$c])) {
              foreach($input_value[$c] as $val) {
                if (is_empty($val))
                  continue;
                $parseValue[$c][] = $val;
              }
            }
          }
          else {
            $parseValue[$c] = $input_value[$c];
          }

          if (is_empty($parseValue[$c])) {
            unset($parseValue[$c]);
            continue;
          }
          $unemptyComponents[] = $c;
        }

        // Ignore empty value from form
        if (empty($unemptyComponents))
          continue;

        $parseValues[] = $parseValue;
      }
    }
    elseif (is_array($_POST[$this -> name.'__values_uuid'])) {
      // HTML form mode
      foreach ($_POST[$this -> name.'__values_uuid'] as $uuid) {
        $parseValue = array();
        $unemptyComponents = array();

        foreach (array_keys($this -> components) as $c) {
          if (!isset($_POST[$this -> name.'__'.$c.'__'.$uuid]))
            continue;
          $parseValue[$c] = array();
          foreach($_POST[$this -> name.'__'.$c.'__'.$uuid] as $val) {
            if (empty($val))
              continue;
            $parseValue[$c][] = $val;
          }

          if (empty($parseValue[$c])) {
            unset($parseValue[$c]);
            continue;
          }
          if (!$this -> getComponentConfig($c, 'multiple', false, 'bool')) {
            $parseValue[$c] = $parseValue[$c][0];
          }
          $unemptyComponents[] = $c;
        }

        // Ignore empty value from form
        if (empty($unemptyComponents))
          continue;

        $parseValues[] = $parseValue;
      }
    }

    // Check extracted values
    foreach ($parseValues as $parseValue) {
      // Check component value
      foreach ($parseValue as $c => $value)
        $this -> checkComponentValues($c, $value);

      // Check required components
      foreach (array_keys($this -> components) as $c) {
        if ($this -> getComponentConfig($c, 'required', false, 'bool') && !isset($parseValue[$c])) {
          $this -> form -> setElementError(
            $this -> attr_html,
            getFData(
              _('Component %{c} must be defined'),
              __($this -> getComponentConfig($c, 'label'))
            )
          );
        }
      }
      $return[$this -> name][] = json_encode($parseValue);
    }

    return true;
  }

  /**
   * Check one component's values
   *
   * @param[] $c       The component name
   * @param[] $value   The values of the component
   *
   * @retval void
   **/
  private function checkComponentValues($c, $value) {
    if ($this -> getComponentConfig($c, 'multiple', false, 'bool')) {
      foreach ($value as $val) {
        $this -> checkComponentValue($c, $val);
      }
    }
    else
      $this -> checkComponentValue($c, $value);
  }

  /**
   * Check one component's value
   *
   * @param[] $c       The component name
   * @param[] $value   The value to check
   *
   * @retval void
   **/
  private function checkComponentValue($c, $value) {
    $label = __($this -> getComponentConfig($c, 'label'));

    // select_list components : check values
    if ($this -> getComponentConfig($c, 'type') == 'select_list') {
      if (!$this -> getSelectListComponentValueLabel($c, $value)) {
        $this -> form -> setElementError(
          $this -> attr_html,
          getFData(
            _('Invalid value "%{value}" for component %{component}.'),
            array('value' => $value, 'component' => $label)
          )
        );
      }
    }

    // Apply check data rules
    foreach($this -> getComponentConfig($c, 'check_data', array(), 'array') as $ruleType => $rconf) {
      $className = 'LSformRule_'.$ruleType;
      if (LSsession::loadLSclass($className)) {
        $r = new $className();
        if (!$r -> validate($value, $rconf, $this)) {
          if (isset($rconf['msg'])) {
            $this -> form -> setElementError(
              $this -> attr_html,
              getFData(__($rconf['msg']), $label)
            );
          }
          else {
            $this -> form -> setElementError(
              $this -> attr_html,
              getFData(
                _('Invalid value "%{value}" for component %{component}.'),
                array('value' => $value, 'component' => $label)
              )
            );
          }
        }
      }
      else {
        $this -> form -> setElementError(
          $this -> attr_html,
          getFData(_("Can't validate value of component %{c}."), $label)
        );
      }
    }
  }

  /**
   * Return a configuration parameter for a specific component (or default value)
   *
   * @param[] $component  The component name
   * @param[] $param      The configuration parameter
   * @param[] $default    The default value (default : null)
   * @param[] $cast       Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  public function getComponentConfig($component, $param, $default=null, $cast=null) {
    return LSconfig :: get(
      $param, $default, $cast,
      (array_key_exists($component, $this -> components)?$this -> components[$component]:array())
    );
  }

  /**
   * Retreive value as return in API response
   *
   * @param[in] $details boolean If true, returned values will contain details if this field type
   *                             support it (optional, default: false)
   *
   * @retval mixed API value(s) or null/empty array if no value
   */
  public function getApiValue($details=false) {
    $values = array();
    foreach(ensureIsArray($this -> values) as $value) {
      $decodedValue = json_decode($value, true);
      if (is_array($decodedValue)) {
        $parsedValue = array();
        foreach(array_keys($this -> components) as $c) {
          if (!isset($decodedValue[$c]))
            continue;
          if ($this -> getComponentConfig($c, 'multiple', false, 'bool')) {
            $parsedValue[$c] = ensureIsArray($decodedValue[$c]);
          }
          else {
            $parsedValue[$c] = $decodedValue[$c];
          }
        }
        $values[] = $parsedValue;
      }
    }
    if ($this -> isMultiple()) {
      return $values;
    }
    if (!$values)
      return null;
    return $values[0];
  }

}
