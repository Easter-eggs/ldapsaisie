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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Element d'un formulaire pour LdapSaisie
 *
 * Cette classe gère les éléments des formulaires.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement extends LSlog_staticLoggerClass {

  var $name;
  var $label;
  var $params;
  var $values = array();
  var $_required = false;
  var $_freeze = false;
  var $attr_html;
  var $fieldTemplate = 'LSformElement_field.tpl';
  var $template = 'LSformElement.tpl';
  var $fetchVariables = array();

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
    $this -> name = $name;
    $this -> label = $label;
    $this -> params = $params;
    $this -> form =& $form;
    $this -> attr_html =& $attr_html;
  }

  /**
   * Allow conversion of LSformElement to string
   *
   * @retval string The string representation of the LSformElement
   */
  public function __toString() {
    return strval($this -> form)." -> <".get_class($this)." ".$this -> name.">";
  }

  /**
   * Définis la valeur de l'élément
   *
   * Cette méthode définis la valeur de l'élément
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array La futur valeur de l'élément
   *
   * @retval boolean Retourne True
   */
  public function setValue($data) {
    $this -> values = ensureIsArray($data);
    return true;
  }

  /**
   * Définis la valeur de l'élément à partir des données
   * envoyées en POST du formulaire
   *
   * Cette méthode définis la valeur de l'élément à partir des données
   * envoyées en POST du formulaire.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array La futur valeur de l'élément
   *
   * @retval boolean Retourne True
   */
  public function setValueFromPostData($data) {
    $this -> values = ensureIsArray($data);
    self :: log_trace($this." -> setValueFromPostData(): input data=".varDump($data)." / values=".varDump($this -> values));
    return true;
  }

  /**
   * Exporte les valeurs de l'élément
   *
   * @retval Array Les valeurs de l'élement
   */
  public function exportValues(){
    return $this -> values;
  }

  /**
   * Ajoute une valeur à l'élément
   *
   * Cette méthode ajoute une valeur à l'élément
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array La futur valeur de l'élément
   *
   * @retval void
   */
  public function addValue($data) {
    $this -> values = array_merge($this -> values, ensureIsArray($data));
  }

  /**
   * Test si l'élément est éditable
   *
   * Cette méthode test si l'élément est éditable
   *
   * @retval boolean
   */
  public function isFreeze(){
    return $this -> _freeze;
  }

  /**
   * Freeze l'élément
   *
   * Rend l'élément non-editable
   *
   * @retval void
   */
  public function freeze() {
    $this -> _freeze = true;
  }

  /**
   * Défini la propriété required de l'élément.
   *
   * param[in] $isRequired boolean true si l'élément est requis, false sinon
   *
   * @retval void
   */
  public function setRequired($isRequired=true) {
    $this -> _required = $isRequired;
  }

  /**
   * Test si l'élément est requis
   *
   * Cette méthode test si l'élément est requis
   *
   * @retval boolean
   */
  public function isRequired(){
    return $this -> _required;
  }

  /**
   * Retourne le label de l'élement
   *
   * @retval void
   */
  public function getLabelInfos() {
    if ($this -> isRequired()) {
        $return['required']=true;
    }
    $return['label'] = $this -> getLabel();
    $help_infos = array();
    if ( $this -> getParam('displayAttrName', $this -> attr_html -> attribute -> ldapObject -> getConfig('displayAttrName', false, 'bool'), 'bool') ) {
      $help_infos[] = _("Attribute")." : ".$this -> name."\n";
    }
    if ($this -> getParam('help_info')) {
      $help_infos[] = __($this -> getParam('help_info'));
    }
    if (!empty($help_infos))
      $return['help_info'] = implode(' - ', $help_infos);
    $return['help_info_in_view'] = $this -> getParam('help_info_in_view', false, 'bool');

    return $return;
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
    $return[$this -> name] = self :: getData($_POST, $this -> name);
    if (!is_array($return[$this -> name])) {
      if ($onlyIfPresent) {
        self :: log_debug($this -> name.": not in POST data => ignore it");
        unset($return[$this -> name]);
      }
      else {
        $return[$this -> name] = array();
      }
    }
    return true;
  }

  /**
   * Retreive the value of the element specified by its name ($name)
   * from POST data (provided as $post).
   *
   * @param[in] &$post array Reference of the array for input POST data
   * @param[in] $name string POST data element name
   *
   * @retval mixed Array of POST data value if present, false otherwise
   */
  protected static function getData(&$post, $name) {
    if (isset($post[$name])) {
      $return = array();
      $post[$name] = ensureIsArray($post[$name]);
      foreach($post[$name] as $key => $val) {
        if (!is_empty($val)) {
          $return[$key] = $val;
        }
      }
      return $return;
    }
    return false;
  }

  /**
   * Retourne le label de l'élement
   *
   * Retourne $this -> label, ou $this -> params['label'], ou $this -> name
   *
   * @retval string Le label de l'élément
   */
  public function getLabel() {
    if ($this -> label != "") {
      return __($this -> label);
    }
    return __($this -> getParam('label', $this -> name));
  }

  /**
   * Le champ est-il a valeur multiple
   *
   * @retval boolean True si le champ est à valeur multiple, False sinon
   */
  public function isMultiple() {
    return $this -> getParam('multiple', false, 'bool');
  }

 /**
  * Return HTML code of the LSformElement based on its (smarty) template file
  *
  * @param[in] $template string The template filename (optional, default: $this -> template)
  * @param[in] $variables array Array of template variables to assign before template compilation (optional)
  *
  * @retval string HTML code of the LSformElement
  */
  public function fetchTemplate($template=NULL,$variables=array()) {
    if (!$template) {
      $template = $this -> template;
    }
    return LSsession :: fetchTemplate(
      $template,
      array_merge_recursive(
        $variables,
        $this -> fetchVariables,
        array(
          'freeze' => $this -> isFreeze(),
          'multiple'=> $this -> isMultiple(),
          'value' => '',
          'values' => $this -> values,
          'attr_name' => $this -> name,
          'noValueTxt' => __($this -> getParam('no_value_label', 'No set value', 'string')),
          'fieldTemplate' => $this -> fieldTemplate,
          'fieldType' => get_class($this)
        )
      )
    );
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
      )
    );
  }

 /**
  * Return a parameter (or default value)
  *
  * @param[] $param	The parameter
  * @param[] $default	The default value (default : null)
  * @param[] $cast	Cast resulting value in specific type (default : disabled)
  *
  * @retval mixed The parameter value or default value if not set
  **/
  public function getParam($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, $this -> params);
  }

  /**
   * CLI autocompleter for form element attribute values
   *
   * @param[in] &$opts      array                 Reference of array of avalaible autocomplete options
   * @param[in] $comp_word  string                The (unquoted) command word to autocomplete
   * @param[in] $attr_value string                The current attribute value in command word to autocomplete
   *                                              (optional, default: empty string)
   * @param[in] $multiple_value_delimiter string  The multiple value delimiter (optional, default: "|")
   * @param[in] $quote_char string                The quote character detected (optional, default: empty string)
   *
   * @retval void
   */
  public function autocomplete_attr_values(&$opts, $comp_word, $attr_value="", $multiple_value_delimiter="|", $quote_char='') {
    return;
  }

  /**
   * CLI autocompleter helper to split form element attribute values
   *
   * @param[in] $attr_value string                    The current attribute value in command word to autocomplete
   *                                                  (optional, default: empty string)
   * @param[in] $multiple_value_delimiter string      The multiple value delimiter (optional, default: "|")
   * @param[in] &$attr_values Reference of array      Reference of array that will contain splited attribute
   *                                                  values without last-one
   * @param[in] &$last_attr_value Reference of string Reference of array that will contain the last splited attribute
   *                                                  value
   *
   * @retval boolean True on success, False otherwise
   */
  protected function split_autocomplete_attr_values($attr_value="", $multiple_value_delimiter="|", &$attr_values, &$last_attr_value) {
    $attr_values = explode($multiple_value_delimiter, $attr_value);
    if (count($attr_values) > 1 && !$this -> getParam('multiple', false, 'bool')) {
      self :: log_error("The attribute ".$this -> name." is not multivalued.");
      return;
    }
    self :: log_debug("split_autocomplete_attr_values('$attr_value', '$multiple_value_delimiter'): values = '".implode("', '", $attr_values)."'");
    $last_attr_value = array_pop($attr_values);
    self :: log_debug("split_autocomplete_attr_values('$attr_value', '$multiple_value_delimiter'): last value = '$last_attr_value'");
    return true;
  }

  /**
   * CLI autocompleter helper to format and add form element attribute value option
   *
   * @param[in] &$opts        array                     Reference of array of avalaible autocomplete options
   * @param[in] &$attr_values Reference of array        Reference of array of splited attribute values without last-one
   * @param[in] $value        string                    The attribute value to add as option
   * @param[in] $multiple_value_delimiter string        The multiple value delimiter (optional, default: "|")
   * @param[in] $quote_char string                      The quote character (optional, default: empty string)
   *
   * @retval boolean True on success, False otherwise
   */
  protected function add_autocomplete_attr_value_opts(&$opts, &$attr_values, $value, $multiple_value_delimiter='|', $quote_char='') {
    if (in_array($value, $attr_values)) {
      self :: log_debug("LSformElement :: autocomplete_opts(): '$value' already one of selected value, ignore it");
      return;
    }
    $opt = $this -> name . "=" .implode($multiple_value_delimiter, array_merge($attr_values, array($value)));
    self :: log_debug("LSformElement :: add_autocomplete_attr_value_opts(): option=$opt");
    if ($quote_char)
      $opt = LScli :: quote_word($opt, $quote_char);
    if (!in_array($opt, $opts))
      $opts[] = $opt;
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
    if (method_exists($this, 'parseValue')) {
      $values = array();
      foreach(ensureIsArray($this -> values) as $value) {
        $parsed_value = $this -> parseValue($value, $details);
        if ($parsed_value != false)
          $values[] = $parsed_value;
      }
    }
    else {
      $values = ensureIsArray($this -> values);
    }
    if ($this -> isMultiple())
      return $values;
    if (!$values)
      return null;
    return $values[0];
  }

}
