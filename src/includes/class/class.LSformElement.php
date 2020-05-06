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
 * Element d'un formulaire pour LdapSaisie
 *
 * Cette classe gère les éléments des formulaires.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement {

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
   * Constructeur
   *
   * Cette methode construit l'objet et définis sa configuration de base.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] &$form [<b>required</b>] LSform L'objet LSform parent
   * @param[in] $name [<b>required</b>] string Le nom de référence de l'élément
   * @param[in] $label [<b>required</b>] string Le label de l'élément
   * @param[in] $params mixed Paramètres supplémentaires
   *
   * @retval true
   */
  public function __construct(&$form, $name, $label, $params, &$attr_html){
    $this -> name = $name;
    $this -> label = $label;
    $this -> params = $params;
    $this -> form =& $form;
    $this -> attr_html =& $attr_html;
    return true;
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
    if (!is_array($data)) {
      $data=array($data);
    }

    $this -> values = $data;
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
    if (!is_array($data)) {
      $data=array($data);
    }
    $this -> values = $data;
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
    if (is_array($data)) {
      $this -> values = array_merge($this -> values, $data);
    }
    else {
      $this -> values[] = $data;
    }
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
   * @param[] array Pointeur sur le tableau qui recupèrera la valeur.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  public function getPostData(&$return) {
    if($this -> isFreeze()) {
      return true;
    }
    if (isset($_POST[$this -> name])) {
      $return[$this -> name]=array();
      if(!is_array($_POST[$this -> name])) {
        $_POST[$this -> name] = array($_POST[$this -> name]);
      }
      foreach($_POST[$this -> name] as $key => $val) {
        if (!empty($val)||(is_string($val)&&($val=="0"))) {
          $return[$this -> name][$key] = $val;
        }
      }
      return true;
    }
    else {
      $return[$this -> name] = array();
      return true;
    }
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
  * Retournne un template Smarty compilé dans le contexte d'un LSformElement
  *
  * @param[in] string $template Le template à retourner
  * @param[in] array $variables Variables Smarty à assigner avant l'affichage
  *
  * @retval string Le HTML compilé du template
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
  * Retourne le code HTML d'un champ vide
  *
  * @retval string Code HTML d'un champ vide.
  */
  public function getEmptyField() {
    return $this -> fetchTemplate($this -> fieldTemplate);
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

}