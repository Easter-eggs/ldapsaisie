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
 * Element supannCompositeAttribute d'un formulaire pour LdapSaisie
 *
 * Cette classe permet de gérer les attributs composite supann en la déclinant.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannCompositeAttribute extends LSformElement {

  var $template = 'LSformElement_supannCompositeAttribute.tpl';
  var $fieldTemplate = 'LSformElement_supannCompositeAttribute_field.tpl';

  /*
   * Composants des valeurs composites :
   *
   * Format :
   *   array (
   *     '[clé composant1]' => array (
   *       'label' => '[label composant]',
   *       'type' => '[type de composant]',
   *       'table' => '[table de nomenclature correspondante]',
   *       'required' => '[booléen obligatoire]'
   *     ),
   *     '[clé composant 2]' => array (
   *       [...]
   *     ),
   *     [...]
   *   )
   * Types :
   *   - 'table' => Composant alimenté à partir d'une table issu de la
   *                nomenclature SUPANN. Le paramètre 'table' permet alors
   *                de spécifier quel table SUPANN intéroger.
   *   - 'codeEntite' => Composant stockant le code d'une entite SUPANN de
   *                     l'annuaire.
   *   - 'text' => saisie manuelle
   *   - 'select' => choix avec une balise HTML select parmi une liste de choix prédéfinis
   *   - 'date' => saisie manuelle d'une date
   *   - 'parrainDN' => sélection du DN d'un objet parrain
   *
   */
  var $components = array ();

  var $_postParsedData=null;

  /**
   * Constructor
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] &$form LSform The LSform parent object
   * @param[in] $name string The name of the element
   * @param[in] $label string The label of the element
   * @param[in] $params array The parameters of the element
   * @param[in] &$attr_html LSattr_html The LSattr_html object of the corresponding attribute
   *
   * @retval void
   */
  public function __construct(&$form, $name, $label, $params, &$attr_html){
    parent :: __construct($form, $name, $label, $params, $attr_html);

    foreach($this -> components as $c => $cconf) {
      switch($cconf['type']) {
        case 'select':
          if (!isset($cconf['possible_values']))
            $this -> components[$c]['possible_values'] = array();
          if (isset($cconf['get_possible_values'])) {
            $this -> components[$c]['possible_values'] = array_merge(
              $this -> components[$c]['possible_values'],
              call_user_func($cconf['get_possible_values'])
            );
          }
          if (LSconfig :: get('sort', true, 'bool', $cconf))
            asort($this -> components[$c]['possible_values']);
          break;

        case 'date':
        case 'datetime':
          $this -> components[$c]['timezone'] = LSconfig :: get('timezone', null, null, $this -> components[$c]);
          $this -> components[$c]['naive'] = $this -> components[$c]['timezone'] == 'naive';
          $this -> components[$c]['ldap_format'] = LSconfig :: get('format', null, null, $this -> components[$c]);
          $this -> components[$c]['php_format'] = 'd/m/Y';
          $this -> components[$c]['js_format'] = '%d/%m/%Y';
          if ($cconf['type'] == 'datetime') {
            $this -> components[$c]['php_format'] .= ' H:i:s';
            $this -> components[$c]['js_format'] .= ' %H:%M:%S';
          }
          break;
      }
    }
  }

  /**
   * Parse une valeur composite gérer par ce type d'attribut
   *
   * Par défaut, cette méthode fait appel à la fonction supannParseCompositeValue()
   * fournie par le LSaddon supann, mais elle peut-être réécrite (parrallèlement à
   * la méthode formatCompositeValue()) pour supporter un autre format de valeur
   * composite.
   *
   * @param  $value string La valeur à parser
   * @return array|null La valeur parsée, ou NULL en cas de problème
   */
  public function parseCompositeValue($value) {
    return supannParseCompositeValue($value);
  }

  /**
   * Format une valeur composite gérer par ce type d'attribut
   *
   * Par défaut, cette méthode fait appel à la fonction supannFormatCompositeValue()
   * fournie par le LSaddon supann, mais elle peut-être réécrite (parrallèlement à
   * la méthode parseCompositeValue()) pour supporter un autre format de valeur
   * composite.
   *
   * @param  $value string La valeur à parser
   * @return array|null|false La valeur formatée, NULL en cas de valeur vide, ou False en cas de problème
   */
  public function formatCompositeValue($value) {
    return supannFormatCompositeValue($value);
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

    $parseValues=array();
    $invalidValues=array();
    foreach($this -> values as $val) {
      $keyValue = $this -> parseCompositeValue($val);
      if ($keyValue) {
        $parseValue=array('value' => $val);
        foreach($keyValue as $key => $value) {
          $parseValue[$key]=$this -> translateComponentValue($key,$value);
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
      'components' => $this -> components
    )
  );
  LStemplate :: addCssFile('LSformElement_supannCompositeAttribute.css');
  if (!$this -> isFreeze()) {
    LStemplate :: addJSconfigParam(
      $this -> name,
      array(
        'searchBtn' => _('Modify'),
        'noValueLabel' => _('No set value'),
        'noResultLabel' => _('No result'),
        'components' => $this->components
      )
    );
    LStemplate :: addJSscript('LSformElement_supannCompositeAttribute_field_value_component.js');
    LStemplate :: addJSscript('LSformElement_supannCompositeAttribute_field_value.js');
    LStemplate :: addJSscript('LSformElement_supannCompositeAttribute_field.js');
    LStemplate :: addJSscript('LSformElement_supannCompositeAttribute.js');

    // Handle date components JSconfigParams
    foreach($this -> components as $c => $cconf) {
      if (in_array($cconf['type'], array('date', 'datetime'))) {
        LStemplate :: addJSconfigParam(
          $this -> name.'__'.$c,
          array(
            'format' => $cconf['js_format'],
            'style' => 'vista',
            'time' => ($cconf['type']=='datetime'),
            'manual' => LSconfig :: get('manual', true, 'bool', $cconf),
            'showNowButton' => LSconfig :: get('showNowButton', ($cconf['type']=='datetime'), 'bool', $cconf),
            'showTodayButton' => LSconfig :: get('showTodayButton', ($cconf['type']=='date'), 'bool', $cconf),
          )
        );
      }
    }
  }
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
        'components' => $this -> components,
      )
    );
  }

  /**
   * Traduit la valeur d'un composant
   *
   * Retourne un array contenant :
   *  - label : l'étiquette de la valeur ou 'no' sinon
   *  - value : la valeur brute
   *  - translated : la valeur traduite ou la valeur elle même
   *
   * @param[in] $c string Le nom du composant
   * @param[in] $val string La valeur
   *
   * @retval array
   **/
  function translateComponentValue($c, $val) {
    $retval = array (
      'translated' => $val,
      'label' => 'no',
      'value' => $val,
    );
    self :: log_debug("translateComponentValue($c, $val)");
    if (isset($this -> components[$c])) {
      switch($this -> components[$c]['type']) {
        case 'table':
          $pv = supannParseLabeledValue($val);
          if ($pv) {
            $retval['label'] = $pv['label'];
            $retval['translated'] = supannGetNomenclatureLabel(
              $this -> components[$c]['table'],
              $pv['label'],
              $pv['value']
            );
          }
          break;

        case 'select':
          self :: log_trace("translateComponentValue($c, $val): possible_values=".varDump($this -> components[$c]['possible_values']));
          if (array_key_exists($val, $this -> components[$c]['possible_values'])) {
            $retval['translated'] = $this -> components[$c]['possible_values'][$val];
          }
          else {
            $retval['translated'] = getFData(__('%{val} (unrecognized)'), $val);
          }
          break;

        case 'date':
        case 'datetime':
          $retval['datetime'] = ldapDate2DateTime(
            $val,
            $this -> components[$c]['naive'],
            $this -> components[$c]['ldap_format']
          );
          self :: log_trace("translateComponentValue($c, $val): datetime = ".varDump($retval['datetime']));
          if ($retval['datetime']) {
            $retval['translated'] = $retval['datetime'] -> format($this -> components[$c]['php_format']);
            self :: log_trace("translateComponentValue($c, $val): translated = '".$retval['translated']."'");
          }
          else {
            $retval['translated'] = getFData(__('%{val} (unrecognized)'), $val);
          }
          break;

        case 'codeEntite':
          $retval['translated'] = supanGetEntiteNameById($val);
          break;

        case 'parrainDN':
          $info = supanGetParrainInfoByDN($val);
          $retval['translated'] = $info['name'];
          $retval['type'] = $info['type'];
          break;

        case 'text':
          // Aucune transformation
          break;

        default:
          self :: error('Unrecognized component type "'.$this -> components[$c]['type'].'"');
      }
    }
    self :: log_debug("translateComponentValue($c, $val): ".varDump($retval));
    return $retval;
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

    // Extract value form POST data and store it in $parseValues
    $parseValues = array();
    if ($this -> form -> api_mode) {
      // API mode
      $form_values = $this -> getData($_POST, $this -> name);
      if (!is_array($form_values) || empty($form_values)) {
        self :: log_trace($this." -> getPostData(): not in POST data");
        return true;
      }
      foreach($form_values as $idx => $form_value) {
        // Handle string value (for value provided by CLI for instance) and already decomposed value
        $input_value = (is_string($form_value)?$this -> parseCompositeValue($form_value):$form_value);
        if (!is_array($input_value)) {
          $this -> form -> setElementError(
            $this -> attr_html,
            getFData(_('Fail to decode composite value #%{idx}.'), $idx)
          );
          continue;
        }

        $parseValue = array();
        $unemptyComponents = array();

        foreach (array_keys($this -> components) as $c) {
          if (!isset($input_value[$c]) || is_empty($input_value))
            continue;
          $parseValue[$c] = $input_value[$c];
          $unemptyComponents[] = $c;
        }

        // Ignore empty value from form
        if (empty($unemptyComponents))
          continue;

        $parseValues[] = $parseValue;
      }
    }
    else {
      // HTML Form
      $end = false;
      $count = 0;
      while (!$end) {
        $parseValue = array();
        $errors = array();
        foreach ($this -> components as $c => $cconf) {
          if (!isset($_POST[$this -> name.'__'.$c][$count])) {
            // end of value break
            $end = true;
            break;
          }

          if (is_empty($_POST[$this -> name.'__'.$c][$count])) {
            continue;
          }
          $parseValue[$c] = $_POST[$this -> name.'__'.$c][$count];
        }
        $count++;

        // Ignore empty value from form
        if (empty($parseValue))
          continue;

        $parseValues[] = $parseValue;
      }
    }
    self :: log_debug($this." -> getPostData(): POST data = ".varDump($parseValues));

    // Check extracted values
    $errors = array();
    foreach ($parseValues as $parseValue) {
      // Check component value
      foreach ($parseValue as $c => $value) {
        $cconf = $this -> components[$c];
        switch ($cconf['type']) {
          case 'table':
            $pv = supannParseLabeledValue($value);
            self :: log_debug("supannParseLabeledValue($value) == ".varDump($pv));
            if ($pv) {
              if (!supannValidateNomenclatureValue($cconf['table'], $pv['label'], $pv['value'])) {
                $errors[] = getFData(__('Invalid value for component %{c}.'), __($cconf['label']));
              }
            }
            else {
              $errors[] = getFData(__('Unparsable value for component %{c}.'), __($cconf['label']));
            }
            break;

          case 'select':
            if (!array_key_exists($value, $cconf['possible_values'])) {
              $errors[] = getFData(__('Invalid value for component %{c}.'), __($cconf['label']));
            }
            break;

          case 'date':
          case 'datetime':
            if ($this -> form -> api_mode) {
              $datetime = ldapDate2DateTime(
                $value,
                $this -> components[$c]['naive'],
                $this -> components[$c]['ldap_format']
              );
            }
            else {
              $datetime = date_create_from_format($cconf['php_format'], $value);
            }
            if ($datetime) {
              $parseValue[$c] = $value = dateTime2LdapDate(
                $datetime,
                $this -> components[$c]['timezone'],
                $this -> components[$c]['ldap_format']
              );
            }
            else {
              $errors[] = getFData(__('Invalid value for component %{c}.'), __($cconf['label']));
            }
            break;

          case 'codeEntite':
            if (!supannValidateEntityId($value)) {
              $errors[] = getFData(__('Invalid value for component %{c}.'), __($cconf['label']));
            }
            break;

          case 'parrainDN':
            if (!supannValidateParrainDN($value)) {
              $errors[] = getFData(__('Invalid value for component %{c}.'), __($cconf['label']));
            }
            break;
        }

        // Check component value (if configured)
        if (isset($cconf['check_data']) && is_array($cconf['check_data'])) {
          foreach($cconf['check_data'] as $ruleType => $rconf) {
            $className = 'LSformRule_'.$ruleType;
            if (!LSsession::loadLSclass($className)) {
              $errors[] = getFData(__("Can't validate value of component %{c}."),__($cconf['label']));
              continue;
            }
            $r = new $className();
            if (!$r -> validate($value, $rconf, $this)) {
              $errors[] = getFData(
                __(LSconfig :: get('msg', 'Invalid value for component %{c}.', 'string', $rconf)),
                __($cconf['label'])
              );
            }
          }
        }
      }

      // Check required component is defined
      foreach($this -> components as $c => $cconf) {
        if (!LSconfig :: get('required', false, 'bool', $cconf))
          continue;
        if (isset($parseValue[$c]) && !is_empty($parseValue[$c]))
          continue;
        $errors[] = getFData(__('Component %{c} must be defined'), __($cconf['label']));
      }

      // Format value and add to return
      $return[$this -> name][] = $this -> formatCompositeValue($parseValue);
    }

    foreach($errors as $e)
      $this -> form -> setElementError($this -> attr_html, $e);
    $this -> _postParsedData = $parseValues;
      return true;
  }

  /**
   * This ajax method is used by the searchComponentPossibleValues function of the form element.
   *
   * @param[in] $data The address to the array of data witch will be return by the ajax request
   *
   * @retval void
   **/
  public static function ajax_searchComponentPossibleValues(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['component'])) && (isset($_REQUEST['pattern'])) && (isset($_REQUEST['idform'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $form = $object -> getForm($_REQUEST['idform']);
        $field = $form -> getElement($_REQUEST['attribute']);
        if (isset($field->components[$_REQUEST['component']])) {
          $data['possibleValues'] = $field -> searchComponentPossibleValues(
            $_REQUEST['component'], $_REQUEST['pattern']
          );
        }
      }
    }
  }

  private function searchComponentPossibleValues($c, $pattern, $max_matches=10) {
    $retval = array();
    if (isset($this -> components[$c])) {
      if ($this -> components[$c]['type'] == 'table') {
        $retval = supannSearchNomenclatureValueByPattern(
          $this -> components[$c]['table'],
          $pattern,
          $max_matches
        );
      }
      elseif ($this -> components[$c]['type'] == 'codeEntite') {
        foreach (supannSearchEntityByPattern($pattern, $max_matches) as $code => $displayName) {
          $retval[] = array(
          'label' => 'no',
          'value' => $code,
          'translated' => $displayName
          );
        }
      }
      elseif ($this -> components[$c]['type'] == 'parrainDN') {
        foreach (supannSearchParrainByPattern($pattern, $max_matches) as $dn => $displayName) {
          $retval[] = array(
          'label' => 'no',
          'value' => $dn,
          'translated' => $displayName
          );
        }
      }
    }
    self :: log_debug("searchComponentPossibleValues('$c', '$pattern'): ".varDump($retval));
    return $retval;
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
      $decodedValue = $this -> parseCompositeValue($value, true);
      if (is_array($decodedValue)) {
        $parsedValue = array();
        foreach(array_keys($this -> components) as $c) {
          if (!isset($decodedValue[$c]))
            continue;
          $parsedValue[$c] = (
            $details?
            $this -> translateComponentValue($c, $decodedValue[$c]):
            $decodedValue[$c]
          );
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
