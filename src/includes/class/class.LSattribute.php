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

LSsession :: loadLSclass('LSlog_staticLoggerClass');
LSsession :: loadLSclass('LSattr_ldap');
LSsession :: loadLSclass('LSattr_html');

/**
 * Attribut Ldap
 *
 * Cette classe modélise un attribut Ldap
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattribute extends LSlog_staticLoggerClass {

  var $name;
  var $config;
  var $ldapObject;
  var $ldap;
  var $html;
  var $data;
  var $updateData=false;
  var $is_validate=false;
  var $_finalUpdateData=false;
  var $_myRights=NULL;
  var $_events=array();
  var $_objectEvents=array();

  /**
   * Constructeur
   *
   * Cette methode construit l'objet et définis la configuration.
   * Elle lance la construction des objets LSattr_html et LSattr_ldap correspondant
   * à ses types définis définis dans sa configuration
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $name string Nom de l'attribut ldap
   * @param[in] $config array Configuration de l'objet
   * @param[in] &$ldapObject LSldapObject L'objet ldap parent
   *
   * @retval boolean Retourne true si la création a réussi, false sinon.
   */
  public function __construct($name, $config, &$ldapObject) {
    $this -> name = $name;
    $this -> config = $config;
    $this -> ldapObject =& $ldapObject;
    $html_type = "LSattr_html_".$config['html_type'];
    $ldap_type = "LSattr_ldap_".$config['ldap_type'];
    LSsession :: loadLSclass($html_type);
    LSsession :: loadLSclass($ldap_type);
    if((class_exists($html_type))&&(class_exists($ldap_type))) {
      $this -> html = new $html_type($name,$config,$this);
      $this -> ldap = new $ldap_type($name,$config,$this);
    }
    else {
      LSerror :: addErrorCode('LSattribute_01',array('attr' => $name,'html'=>$config['html_type'],'ldap'=>$config['ldap_type']));
      return;
    }
    return true;
  }

 /**
  * Allow conversion of LSattribute to string
  *
  * @retval string The string representation of the LSattribute
  */
  public function __toString() {
    return strval($this -> ldapObject)." -> <LSattribute ".$this -> name.">";
  }

  /**
   * Retourne la valeur du label de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Le label de l'attribut
   *
   * @see LSattr_html::getLabel()
   */

  public function getLabel() {
    if (!$this -> html) {
      LSerror :: addErrorCode('LSattribute_09',array('type' => 'html','name' => $this -> name));
      return;
    }
    return $this -> html -> getLabel();
  }

  /**
   * Défini la valeur de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true
   */
  public function loadData($attr_data) {
    $this -> data = ensureIsArray($attr_data);
    return true;
  }

  /**
   * Redéfini la valeur de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true
   */
  public function reloadData($attr_data) {
    $this -> data = ensureIsArray($attr_data);
    $this -> updateData = false;
    $this -> is_validate = false;
    return true;
  }

  /**
   * Retourne la valeur de l'attribut
   *
   * Retourne la valeur nouvelle si elle existe, sinon la valeur passé.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed La valeur de l'attribut
   */
  public function getValue() {
    if ($this -> isUpdate()) {
      return $this -> getUpdateData();
    }
    else {
      return $this -> getOldValue();
    }
  }

  /**
   * Retourne la valeur originale de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed La valeur originale de l'attribut
   */
  public function getOldValue() {
    return $this -> data;
  }

  /**
   * Retourne la valeur d'affichage de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string La valeur d'affichage de l'attribut
   */
  public function getDisplayValue() {
    if (!$this -> ldap) {
      LSerror :: addErrorCode('LSattribute_09',array('type' => 'ldap','name' => $this -> name));
      return;
    }

    if ($this -> isUpdate()) {
      $data = $this -> ldap -> getDisplayValue($this -> updateData);
    }
    else {
      $data = $this -> ldap -> getDisplayValue($this -> data);
    }

    $onDisplay = $this -> getConfig('onDisplay');
    if ($onDisplay) {
      $result = $data;
      foreach(ensureIsArray($onDisplay) as $func) {
        if (function_exists($func)) {
          $result = call_user_func($func, $result);
        }
        else {
          LSerror :: addErrorCode('LSattribute_02', array('attr' => $this->name, 'func' => $func));
          return;
        }
      }
      return $result;
    }
    return $data;
  }

  /**
   * Ajoute l'attribut au formulaire
   *
   * Cette méthode ajoute l'attribut au formulaire $form si l'identifiant de celui-ci
   * ($idForm) est connu dans la configuration de l'objet.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] object $form Le formulaire dans lequel doit être ajouté l'attribut
   * @param[in] string $idForm L'identifiant du formulaire
   * @param[in] objet  &$obj Objet utilisable pour la génération de la valeur de l'attribut
   * @param[in] array  $value valeur de l'élement
   *
   * @retval boolean true si l'ajout a fonctionner ou qu'il n'est pas nécessaire, false sinon
   */
  public function addToForm(&$form,$idForm,&$obj=NULL,$value=NULL) {
    if($this -> getConfig("form.$idForm")) {
      if (!$this -> html) {
        LSerror :: addErrorCode('LSattribute_09',array('type' => 'html','name' => $this -> name));
        return;
      }
      if($this -> myRights() == 'n') {
        return true;
      }
      if (!is_null($value)) {
        $data = $value;
      }
      else if(!is_empty($this -> data)) {
        $data = $this -> getFormVal();
      }
      else if (!is_empty($this -> getConfig('default_value'))) {
        $data = $obj -> getFData($this -> getConfig('default_value'));
      }
      else {
        $data = NULL;
      }

      $element = $this -> html -> addToForm($form,$idForm,$data);
      if(!$element) {
        LSerror :: addErrorCode('LSform_06',$this -> name);
      }

      if($this -> getConfig('required')) {
        $form -> setRequired($this -> name);
      }

      if ( ($this -> getConfig("form.$idForm")==0) || ($this -> myRights() == 'r') ) {
        self :: log_debug("Attribute ".$this -> name." is freeze in form $idForm.");
        $element -> freeze();
      }
      else {
        $check_data = $this -> getConfig('check_data', array());
        if(is_array($check_data)) {
          foreach ($check_data as $rule => $rule_infos) {
            if((!$form -> isRuleRegistered($rule))&&($rule!='')) {
              LSerror :: addErrorCode('LSattribute_03',array('attr' => $this->name,'rule' => $rule));
              return;
            }
            if(!isset($rule_infos['msg'])) {
              $rule_infos['msg']=getFData(_('The value of field %{label} is invalid.'),$this -> getLabel());
            }
            else {
              $rule_infos['msg']=__($rule_infos['msg']);
            }
            if(!isset($rule_infos['params']))
              $rule_infos['params']=NULL;
            $form -> addRule($this -> name,$rule,array('msg' => $rule_infos['msg'], 'params' => $rule_infos['params']));
          }
        }
        else {
          LSerror :: addErrorCode('LSattribute_04',$this->name);
        }
      }
    }
    else {
      self :: log_debug("Attribute ".$this -> name." not in form $idForm.");
    }
    return true;
  }

  /**
   * Récupération des droits de l'utilisateur sur l'attribut
   *
   * @retval string 'r'/'w'/'n' pour 'read'/'write'/'none'
   **/
  private function myRights() {
    // cache
    if ($this -> _myRights != NULL) {
      return $this -> _myRights;
    }
    $return='n';
    if (php_sapi_name() == 'cli') {
      // In CLI mode, take maximum rights affected to LSprofiles
      foreach ($this -> getConfig("rights", array()) as $who => $right) {
        if (in_array($right, array('r', 'w'))) {
          $return = $right;
          if ($return == 'w') break;
        }
      }
    }
    else {
      $whoami = $this -> ldapObject -> whoami();
      foreach($whoami as $who) {
        $right = $this -> getConfig("rights.$who", null);
        if (in_array($right, array('r', 'w'))) {
          $return = $right;
          if ($return == 'w') break;
        }
      }
    }
    $this -> _myRights = $return;
    return $return;
  }

  /**
   * Ajoute l'attribut au formualaire de vue
   *
   * Cette méthode ajoute l'attribut au formulaire $form de vue si il doit l'être
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] object $form Le formulaire dans lequel doit être ajouté l'attribut
   *
   * @retval boolean true si l'ajout a fonctionner ou qu'il n'est pas nécessaire, false sinon
   */
  public function addToView(&$form) {
    if ($this -> getConfig('view', false, 'bool') && ($this -> myRights() != 'n') ) {
      if (!$this -> html) {
        LSerror :: addErrorCode('LSattribute_09',array('type' => 'html','name' => $this -> name));
        return;
      }
      if(!is_empty($this -> data)) {
        $data = $this -> getFormVal();
      }
      else {
        $data=null;
      }
      $element = $this -> html -> addToForm($form,'view',$data);
      if(!$element instanceof LSformElement) {
        LSerror :: addErrorCode('LSform_06',$this -> name);
        return;
      }
      $element -> freeze();
      return true;
    }
    return true;
  }

  /**
   * Rafraichis la valeur de l'attribut dans un formualaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] object &$form LSform Le formulaire dans lequel doit être ajouté l'attribut
   * @param[in] string $idForm L'identifiant du formulaire
   *
   * @retval boolean true si la valeur a été rafraichie ou que ce n'est pas nécessaire, false sinon
   */
  public function refreshForm(&$form,$idForm) {
    if ($this -> getConfig("form.$idForm") && ($this -> myRights() != 'n')) {
      if (!$this -> html) {
        LSerror :: addErrorCode('LSattribute_09',array('type' => 'html','name' => $this -> name));
        return;
      }
      $form_element = $form -> getElement($this -> name);
      if ($form_element) {
        $values = $this -> html -> refreshForm($this -> getFormVal());
        return $form_element -> setValue($values);
      }
      else {
        LSdebug('LSformElement "'.$this -> name.'" n\'existe pas');
      }
    }
    return true;
  }

  /**
   * Retourne la valeur a afficher dans le formulaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string La valeur a afficher dans le formulaire.
   */
  public function getFormVal() {
    return ensureIsArray($this -> html -> getFormVal($this -> data));
  }

  /**
   * Définis les données de mises à jour si un changement a eut lieu
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] string $data Les données de mise à jour.
   *
   * @retval void
   */
  public function setUpdateData($data) {
    if($this -> ldap -> isUpdated($data)) {
      $this -> updateData = ensureIsArray($data);
    }
  }

  /**
   * Vérifie si l'attribut a été validé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'attribut a été validé, false sinon
   */
  public function isValidate() {
    return $this -> is_validate;
  }

  /**
   * Valide le champs
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  public function validate() {
    $this -> is_validate=true;
  }

  /**
   * Vérifie si l'attribut a été mise à jour
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'attribut a été mis à jour, false sinon
   */
  public function isUpdate() {
    return ($this -> updateData===false)?false:true;
  }

  /**
   * Vérifie si l'attribut est obligatoire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'attribut est obligatoire, false sinon
   */
  public function isRequired() {
    return $this -> getConfig('required', false, 'bool');
  }

  /**
   * Vérifie si la valeur de l'attribut peut être générée
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la valeur de l'attribut peut être générée, false sinon
   */
  public function canBeGenerated() {
    $format = $this -> getConfig('generate_value_format', $this -> getConfig('default_value'));
    return (
              (function_exists($this -> getConfig('generate_function')))
              ||
              (
                (is_string($format))
                &&
                (strlen($format) > 0)
              )
           );
  }

  /**
   * Génere la valeur de l'attribut à partir de la fonction de génération
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la valeur à put être générée, false sinon
   */
  public function generateValue() {
    $value = false;
    $generate_function = $this -> getConfig('generate_function');
    $format = $this -> getConfig('generate_value_format', $this -> getConfig('default_value'));
    if ($generate_function && function_exists($generate_function)) {
      $value = call_user_func_array($generate_function, array(&$this -> ldapObject));
    }
    else if ($format) {
      $value = $this -> ldapObject -> getFData($format);
    }
    if ($value !== false) {
      $this -> updateData = ensureIsArray($value);
      return true;
    }
    return;
  }

  /**
   * Retourne la valeur de l'attribut pour son enregistrement dans l'annuaire
   * si l'attribut à été modifié.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed La valeur de l'attribut pour son enregistrement dans l'annuaire
   */
  public function getUpdateData() {
    if (!$this -> isUpdate()) {
      return;
    }
    if ( $this -> _finalUpdateData ) {
      return  $this -> _finalUpdateData;
    }
    $data=$this -> updateData;
    if (isset($this -> config['onSave'])) {
      if (is_array($this -> config['onSave'])) {
        $result=$data;
        foreach($this -> config['onSave'] as $func) {
          if (function_exists($func)) {
            $result=call_user_func($func, $result);
          }
          else {
            LSerror :: addErrorCode('LSattribute_05',array('attr' => $this->name,'func' => $func));
            return;
          }
        }
      }
      else {
        if (function_exists($this -> config['onSave'])) {
          $result = $this -> config['onSave']($data);
        }
        else {
          LSerror :: addErrorCode('LSattribute_05',array('attr' => $this->name,'func' => $this -> config['onSave']));
          return;
        }
      }
    }
    else {
      if (!$this -> ldap) {
        LSerror :: addErrorCode('LSattribute_09',array('type' => 'ldap','name' => $this -> name));
        return;
      }
      else {
        $result = $this -> ldap -> getUpdateData($data);
      }
    }
    $this -> _finalUpdateData = $result;
    return $result;
  }

  /**
   * Retourne la configuration de validation de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed La configuration de validation de l'attribut
   */
  public function getValidateConfig() {
    if (isset($this -> config['validation'])) {
      return $this -> config['validation'];
    }
    return;
  }

  /**
   * Retourne les attributs dépendants de celui-ci
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array les noms des attributs dépendants
   */
  public function getDependsAttrs() {
    return (isset($this -> config['dependAttrs'])?$this -> config['dependAttrs']:null);
  }

  /**
   * Ajouter une action lors d'un événement
   *
   * @param[in] $event string Le nom de l'événement
   * @param[in] $fct string Le nom de la fonction à exectuer
   * @param[in] $params mixed Paramètres pour le lancement de la fonction
   * @param[in] $class Nom de la classe possèdant la méthode $fct à executer
   *
   * @retval void
   */
  public function addEvent($event,$fct,$params,$class=NULL) {
    $this -> _events[$event][] = array(
      'function'  => $fct,
      'params'    => $params,
      'class'     => $class
    );
  }

  /**
   * Ajouter une action sur un objet lors d'un événement
   *
   * @param[in] $event string Le nom de l'événement
   * @param[in] $obj object L'objet dont la méthode doit être executé
   * @param[in] $meth string Le nom de la méthode
   * @param[in] $params mixed Paramètres d'execution de la méthode
   *
   * @retval void
   */
  public function addObjectEvent($event,&$obj,$meth,$params=NULL) {
    $this -> _objectEvents[$event][] = array(
      'obj'  => &$obj,
      'meth'  => $meth,
      'params'    => $params
    );
  }

  /**
   * Lance les actions à executer lors d'un événement
   *
   * @param[in] $event string Le nom de l'événement
   *
   * @retval boolean True si tout c'est bien passé, false sinon
   */
  public function fireEvent($event) {
    self :: log_debug(strval($this)." -> fireEvent($event)");
    $return = true;
    if(isset($this -> config[$event])) {
      foreach(ensureIsArray($this -> config[$event]) as $func) {
        if(function_exists($func)) {
          self :: log_debug(strval($this)." -> fireEvent($event): run ".format_callable($func));
          if(!call_user_func_array($func, array(&$this -> ldapObject))) {
            $return = false;
          }
        }
        else {
          self :: log_warning(strval($this)." -> fireEvent($event): function '".format_callable($func)."' doesn't exists.");
          $return = false;
        }
      }
    }
    else
      self :: log_trace(strval($this)." -> fireEvent($event): no configured trigger for this event.");

    if (isset($this -> _events[$event]) && is_array($this -> _events[$event])) {
      foreach ($this -> _events[$event] as $e) {
        if ($e['class']) {
          if (class_exists($e['class'])) {
            $obj = new $e['class']();
            if (method_exists($obj,$e['fct'])) {
              try {
                self :: log_debug(strval($this)." -> fireEvent($event): run ".format_callable(array($obj, $e['fct'])));
                call_user_func_array(array($obj, $e['fct']), array(&$e['params']));
              }
              catch(Exception $er) {
                self :: log_exception($er, strval($this)." -> fireEvent($event): exception occured running ".format_callable(array($obj, $e['fct'])));
                $return = false;
              }
            }
            else {
              self :: log_warning(strval($this)." -> fireEvent($event): method '".$e['fct']."' of the class '".$e['class']."' doesn't exists.");
              $return = false;
            }
          }
          else {
            self :: log_warning(strval($this)." -> fireEvent($event): the class '".$e['class']."' doesn't exists.");
            $return = false;
          }
        }
        else {
          if (function_exists($e['fct'])) {
            try {
              self :: log_debug(strval($this)." -> fireEvent($event): run ".format_callable($e['fct']));
              call_user_func_array($e['fct'], array(&$e['params']));
            }
            catch(Exception $er) {
              self :: log_exception($er, strval($this)." -> fireEvent($event): exception occured running ".format_callable(e['fct']));
              $return = false;
            }
          }
          else {
            self :: log_warning(strval($this)." -> fireEvent($event): the function '".$e['fct']."' doesn't exists.");
            $return = false;
          }
        }
      }
    }

    if (isset($this -> _objectEvents[$event]) && is_array($this -> _objectEvents[$event])) {
      foreach ($this -> _objectEvents[$event] as $e) {
        if (method_exists($e['obj'], $e['meth'])) {
          try {
            self :: log_debug(strval($this)." -> fireEvent($event): run ".format_callable(array($e['obj'], $e['meth'])));
            call_user_func_array(array($e['obj'], $e['meth']),array(&$e['params']));
          }
          catch(Exception $er) {
            self :: log_exception($er, strval($this)." -> fireEvent($event): exception occured running ".format_callable(array($e['obj'], $e['meth'])));
            $return = false;
          }
        }
        else {
          self :: log_warning(strval($this)." -> fireEvent($event): the method '".$e['meth']."' of the object doesn't exists.");
          $return = false;
        }
      }
    }

    return $return;
  }

  /**
   * Return a configuration parameter (or default value)
   *
   * @param[] $param	The configuration parameter
   * @param[] $default	The default value (default : null)
   * @param[] $cast	Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  public function getConfig($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, $this -> config);
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSattribute_01',
  ___("LSattribute : Attribute %{attr} : LDAP or HTML types unknow (LDAP = %{ldap} & HTML = %{html}).")
);
LSerror :: defineError('LSattribute_02',
  ___("LSattribute : The function %{func} to display the attribute %{attr} is unknow.")
);
LSerror :: defineError('LSattribute_03',
  ___("LSattribute : The rule %{rule} to validate the attribute %{attr} is unknow.")
);
LSerror :: defineError('LSattribute_04',
  ___("LSattribute : Configuration data to verify the attribute %{attr} are incorrect.")
);
LSerror :: defineError('LSattribute_05',
  ___("LSattribute : The function %{func} to save the attribute %{attr} is unknow.")
);
LSerror :: defineError('LSattribute_06',
  ___("LSattribute : The value of the attribute %{attr} can't be generated.")
);
LSerror :: defineError('LSattribute_07',
  ___("LSattribute : Generation of the attribute %{attr} failed.")
);
LSerror :: defineError('LSattribute_08',
  ___("LSattribute : Generation of the attribute %{attr} did not return a correct value.")
);
LSerror :: defineError('LSattribute_09',
  ___("LSattribute : The attr_%{type} of the attribute %{name} is not yet defined.")
);
