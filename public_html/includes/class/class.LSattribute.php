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

LSsession :: loadLSclass('LSattr_ldap');
LSsession :: loadLSclass('LSattr_html');

/**
 * Attribut Ldap
 *
 * Cette classe modélise un attribut Ldap
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattribute {
  
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
  function LSattribute ($name,$config,&$ldapObject) {
    $this -> name = $name;
    $this -> config = $config;
    $this -> ldapObject = $ldapObject;
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
   * Retourne la valeur du label de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Le label de l'attribut
   *
   * @see LSattr_html::getLabel()
   */ 

  function getLabel() {
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
  function loadData($attr_data) {
    if ((!is_array($attr_data))&&(!empty($attr_data))) {
      $attr_data = array($attr_data);
    }
    $this -> data = $attr_data;
    return true;
  }
  
  /**
   * Redéfini la valeur de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true
   */
  function reloadData($attr_data) {
    if ((!is_array($attr_data))&&(!empty($attr_data))) {
      $attr_data = array($attr_data);
    }
    $this -> data = $attr_data;
    $this -> updateData=false;
    $this -> is_validate=false;
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
  function getValue() {
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
  function getOldValue() {
    return $this -> data;
  }
  
  /**
   * Retourne la valeur d'affichage de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string La valeur d'affichage de l'attribut
   */
  function getDisplayValue() {
    if (!$this -> ldap) {
      LSerror :: addErrorCode('LSattribute_09',array('type' => 'ldap','name' => $this -> name));
      return;
    }
    $data = $this -> ldap -> getDisplayValue($this -> data);
    if (isset($this -> config['onDisplay'])) {
      if (is_array($this -> config['onDisplay'])) {
        $result=$data;
        foreach($this -> config['onDisplay'] as $func) {
          if (function_exists($func)) {
            $result=$func($result);
          }
          else {
            LSerror :: addErrorCode('LSattribute_02',array('attr' => $this->name,'func' => $func));
            return;
          }
        }
        return $result;
      }
      else {
        if (function_exists($this -> config['onDisplay'])) {
          return $this -> config['onDisplay']($data);
        }
        else {
          LSerror :: addErrorCode('LSattribute_02',array('attr' => $this->name,'func' => $this -> config['onDisplay']));
          return;
        }
      }
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
  function addToForm(&$form,$idForm,&$obj=NULL,$value=NULL) {
    if(isset($this -> config['form'][$idForm])) {
      if (!$this -> html) {
        LSerror :: addErrorCode('LSattribute_09',array('type' => 'html','name' => $this -> name));
        return;
      }
      if($this -> myRights() == 'n') {
        return true;
      }
      if ($value) {
        $data = $value;
      }
      else if($this -> data !='') {
        $data=$this -> getFormVal();
      }
      else if (isset($this -> config['default_value'])) {
        $data=$obj -> getFData($this -> config['default_value']);
      }
      
      $element = $this -> html -> addToForm($form,$idForm,$data);
      if(!$element) {
        LSerror :: addErrorCode('LSform_06',$this -> name);
      }

      if($this -> config['required']==1) {
        $form -> setRequired($this -> name);
      }

      if (($this -> config['form'][$idForm]==0) || ($this -> myRights() == 'r')) {
        $element -> freeze();
      }
      else {
        if(isset($this -> config['check_data'])) {
          if(is_array($this -> config['check_data'])) {
            foreach ($this -> config['check_data'] as $rule => $rule_infos) {
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
    }
    return true;
  }

  /**
   * Récupération des droits de l'utilisateur sur l'attribut
   * 
   * @retval string 'r'/'w'/'n' pour 'read'/'write'/'none'
   **/
  function myRights() {
    // cache
    if ($this -> _myRights != NULL) {
      return $this -> _myRights;
    }
    $return='n';
    $whoami = $this -> ldapObject -> whoami();
    foreach($whoami as $who) {
      switch ($who) {
        case 'admin':
          if($this -> config['rights']['admin']=='w') {
            $return='w';
            break;
          }
          else {
            $return='r';
          }
          break;
        default:
          if (!isset($this -> config['rights'][$who])) break;
          if ($this -> config['rights'][$who] == 'w') {
            $return='w';
            break;
          }
          else if($this -> config['rights'][$who] == 'r') {
            $return='r';
          }
          break;
      }
      if ($return=='w') {
        break;
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
  function addToView(&$form) {
    if((isset($this -> config['view'])) && ($this -> config['view']) && ($this -> myRights() != 'n') ) {
      if (!$this -> html) {
        LSerror :: addErrorCode('LSattribute_09',array('type' => 'html','name' => $this -> name));
        return;
      }
      if($this -> data !='') {
        $data=$this -> getFormVal();
      }
      else {
        $data='';
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
  function refreshForm(&$form,$idForm) {
    if(isset($this -> config['form'][$idForm])&&($this -> myRights()!='n')) {
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
  function getFormVal() {
    $data=$this -> getDisplayValue();
    if ($data==NULL) {
      $data=array();
    }
    if(!is_array($data)) {
      $data=array($data);
    }
    return $data;
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
  function setUpdateData($data) {
    if($this -> ldap -> isUpdated($data)) {
      $this -> updateData=$data;
    }
  }
  
  /**
   * Vérifie si l'attribut a été validé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'attribut a été validé, false sinon
   */
  function isValidate() {
    return $this -> is_validate;
  }
  
  /**
   * Valide le champs
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  function validate() {
    $this -> is_validate=true;
  }
  
  /**
   * Vérifie si l'attribut a été mise à jour
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'attribut a été mis à jour, false sinon
   */
  function isUpdate() {
    return ($this -> updateData===false)?false:true;
  }
  
  /**
   * Vérifie si l'attribut est obligatoire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'attribut est obligatoire, false sinon
   */
  function isRequired() {
    return (isset($this -> config['required'])?(bool)$this -> config['required']:false);
  }
  
  /**
   * Vérifie si la valeur de l'attribut peut être générée
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la valeur de l'attribut peut être générée, false sinon
   */
  function canBeGenerated() {
    return (
              (function_exists($this -> config['generate_function']))
              ||
              (isset($this -> config['generate_value_format']))
              ||
              (
                (is_string($this -> config['default_value']))
                &&
                (strlen($this -> config['default_value'])>0)
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
  function generateValue() {
    $value=false;
    if (function_exists($this -> config['generate_function'])) {
      $value=call_user_func($this -> config['generate_function'],$this -> ldapObject);
    }
    else if (isset($this -> config['generate_value_format'])) {
      $value = $this -> ldapObject -> getFData($this -> config['generate_value_format']);
    }
    else if (is_string($this -> config['default_value']) && strlen($this -> config['default_value'])>0) {
      $value = $this -> ldapObject -> getFData($this -> config['default_value']);
    }
    if ($value!==false) {
      if (!empty($value)) {
        if (!is_array($value)) {
          $value=array($value);
        }
        $this -> updateData=$value;
      }
      else {
        $this -> updateData=array();
      }
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
  function getUpdateData() {
    if (!$this -> isUpdate()) {
      return;
    }
    if ( $this -> _finalUpdateData ) {
      return  $this -> _finalUpdateData;
    }
    $data=$this -> updateData;
    if ($this -> config['onSave']) {
      if (is_array($this -> config['onSave'])) {
        $result=$data;
        foreach($this -> config['onSave'] as $func) {
          if (function_exists($func)) {
            $result=$func($result);
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
  function getValidateConfig() {
    return $this -> config['validation'];
  }

  /**
   * Retourne les attributs dépendants de celui-ci
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array les noms des attributs dépendants
   */
  function getDependsAttrs() {
    return $this -> config['dependAttrs'];
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
  function addEvent($event,$fct,$params,$class=NULL) {
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
  function addObjectEvent($event,&$obj,$meth,$params=NULL) {
    $this -> _objectEvents[$event][] = array(
      'obj'  => $obj,
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
  function fireEvent($event) {
    $return = true;
    if(isset($this -> config[$event])) {
      if (!is_array($this -> config[$event])) {
        $funcs = array($this -> config[$event]);
      }
      else {
        $funcs = $this -> config[$event];
      }
      foreach($funcs as $func) {
        if(function_exists($func)) {
          if(!$func($this -> ldapObject)) {
            $return = false;
          }
        }
        else {
          $return = false;
        }
      }
    }
    
    if (is_array($this -> _events[$event])) {
      foreach ($this -> _events[$event] as $e) {
        if ($e['class']) {
          if (class_exists($e['class'])) {
            $obj = new $e['class']();
            if (method_exists($obj,$e['fct'])) {
              try {
                $obj -> $e['fct']($e['params']);
              }
              catch(Exception $er) {
                $return = false;
                LSdebug("Event ".$event." : Erreur durant l'execution de la méthode ".$e['fct']." de la classe ".$e['class']);
              }
            }
            else {
              LSdebug("Event ".$event." : La méthode ".$e['fct']." de la classe ".$e['class']." n'existe pas.");
              $return = false;
            }
          }
          else {
            $return = false;
            LSdebug("Event ".$event." : La classe ".$e['class']." n'existe pas");
          }
        }
        else {
          if (function_exists($e['fct'])) {
            try {
              $e['fct']($e['params']);
            }
            catch(Exception $er) {
              LSdebug("Event ".$event." : Erreur durant l'execution de la function ".$e['fct']);
              $return = false;
            }
          }
          else {
            LSdebug("Event ".$event." : la function ".$e['fct']." n'existe pas");
            $return = false;
          }
        }
      }
    }
    
    if (is_array($this -> _objectEvents[$event])) {
      foreach ($this -> _objectEvents[$event] as $e) {
        if (method_exists($e['obj'],$e['meth'])) {
          try {
            $e['obj'] -> $e['meth']($e['params']);
          }
          catch(Exception $er) {
            $return = false;
            LSdebug("Event ".$event." : Erreur durant l'execution de la méthode ".$e['meth']." sur l'objet.");
          }
        }
        else {
          LSdebug("Event ".$event." : La méthode ".$e['meth']." de l'objet n'existe pas.");
          $return = false;
        }
      }
    }
    
    return $return;
  }
  
}

/**
 * Error Codes
 **/
LSerror :: defineError('LSattribute_01',
  _("LSattribute : Attribute %{attr} : LDAP or HTML types unknow (LDAP = %{ldap} & HTML = %{html}).")
);
LSerror :: defineError('LSattribute_02',
  _("LSattribute : The function %{func} to display the attribute %{attr} is unknow.")
);
LSerror :: defineError('LSattribute_03',
  _("LSattribute : The rule %{rule} to validate the attribute %{attr} is unknow.")
);
LSerror :: defineError('LSattribute_04',
  _("LSattribute : Configuration data to verify the attribute %{attr} are incorrect.")
);
LSerror :: defineError('LSattribute_05',
  _("LSattribute : The function %{func} to save the attribute %{attr} is unknow.")
);
LSerror :: defineError('LSattribute_06',
  _("LSattribute : The value of the attribute %{attr} can't be generated.")
);
LSerror :: defineError('LSattribute_07',
  _("LSattribute : Generation of the attribute %{attr} failed.")
);
LSerror :: defineError('LSattribute_08',
  _("LSattribute : Generation of the attribute %{attr} did not return a correct value.")
);
LSerror :: defineError('LSattribute_09',
  _("LSattribute : The attr_%{type} of the attribute %{name} is not yet defined.")
);

?>
