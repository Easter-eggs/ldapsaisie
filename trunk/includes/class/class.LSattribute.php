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

$GLOBALS['LSsession'] -> loadLSclass('LSattr_ldap');
$GLOBALS['LSsession'] -> loadLSclass('LSattr_html');

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
    $GLOBALS['LSsession'] -> loadLSclass($html_type);
    $GLOBALS['LSsession'] -> loadLSclass($ldap_type);
    if((class_exists($html_type))&&(class_exists($ldap_type))) {
      $this -> html = new $html_type($name,$config,$this);
      $this -> ldap = new $ldap_type($name,$config,$this);
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(41,array('attr' => $name,'html'=>$config['html_type'],'ldap'=>$config['ldap_type']));
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
    $updateData=$this -> getUpdateData();
    if (empty($updateData)) {
      return $this -> data;
    }
    else {
      return $updateData;
    }
  }
  
  /**
   * Retourne la valeur d'affichage de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string La valeur d'affichage de l'attribut
   */
  function getDisplayValue() {
    $data = $this -> ldap -> getDisplayValue($this -> data);
    if ($this -> config['onDisplay']) {
      if (is_array($this -> config['onDisplay'])) {
        $result=$data;
        foreach($this -> config['onDisplay'] as $func) {
          if (function_exists($func)) {
            $result=$func($result);
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(42,array('attr' => $this->name,'func' => $func));
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
          $GLOBALS['LSerror'] -> addErrorCode(42,array('attr' => $this->name,'func' => $this -> config['onDisplay']));
          return;
        }
      }
    }
    return $data;
  }
  
  /**
   * Ajoute l'attribut au formualaire
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
        $GLOBALS['LSerror'] -> addErrorCode(206,$this -> name);
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
                $GLOBALS['LSerror'] -> addErrorCode(43,array('attr' => $this->name,'rule' => $rule));
                return;
              }
              if(!isset($rule_infos['msg']))
                $rule_infos['msg']=getFData(_('La valeur du champs %{label} est invalide.'),$this -> config['label']);
              if(!isset($rule_infos['param']))
                $rule_infos['param']=NULL;
              $form -> addRule($this -> name,$rule,array('msg' => $rule_infos['msg'], 'param' => $rule_infos['param']));
            }
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(44,$this->name);
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
    switch ($this -> ldapObject -> whoami()) {
      case 'admin':
        if($this -> config['rights']['admin']=='w') {
          $return='w';
        }
        else {
          $return='r';
        }
        break;
      case 'self':
        if (($this -> config['rights']['self'] == 'w') || ($this -> config['rights']['self'] == 'r')) {
          $return=$this -> config['rights']['self'];
        }
        break;
      default:    //user
        if (($this -> config['rights']['user'] == 'w') || ($this -> config['rights']['user'] == 'r')) {
            $return=$this -> config['rights']['user'];
        }
        break;
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
    if((isset($this -> config['view'])) && ($this -> myRights() != 'n')) {
      if($this -> data !='') {
        $data=$this -> getFormVal();
      }
      else {
        $data='';
      }
      $element = $this -> html -> addToForm($form,'view',$data);
      if(!$element instanceof LSformElement) {
        $GLOBALS['LSerror'] -> addErrorCode(206,$this -> name);
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
    if(isset($this -> config['form'][$idForm])) {
      $form_element = $form -> getElement($this -> name);
      $values = $this -> html -> refreshForm($this -> getFormVal());
      return $form_element -> setValue($values);
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
    if(!is_array($data))
      $data=array($data);
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
    if($this -> getFormVal() != $data) {
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
    return ($this -> updateData)?true:false;
  }
  
  /**
   * Vérifie si l'attribut est obligatoire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'attribut est obligatoire, false sinon
   */
  function isRequired() {
    return $this -> config['required'];
  }
  
  /**
   * Vérifie si la valeur de l'attribut peut être générée
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la valeur de l'attribut peut être générée, false sinon
   */
  function canBeGenerated() {
    if (function_exists($this -> config['generate_function'])) {
      return true;
    }
    else if (isset($this -> config['generate_value_format'])) {
      return true;
    }
    else {
      return ;
    }
  }

  /**
   * Génere la valeur de l'attribut à partir de la fonction de génération
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la valeur à put être générée, false sinon
   */
  function generateValue() {
    if (function_exists($this -> config['generate_function'])) {
      $value=call_user_func($this -> config['generate_function'],$this -> ldapObject);
    }
    else if (isset($this -> config['generate_value_format'])) {
      $value = $this -> ldapObject -> getFData($this -> config['generate_value_format']);
    }
    if (!empty($value)) {
      //$this -> setValue($value); // pas nécéssaire ??
      $this -> updateData=array($value);
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
            $GLOBALS['LSerror'] -> addErrorCode(45,array('attr' => $this->name,'func' => $func));
            return;
          }
        }
      }
      else {
        if (function_exists($this -> config['onSave'])) {
          $result = $this -> config['onSave']($data);
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(45,array('attr' => $this->name,'func' => $this -> config['onSave']));
          return;
        }
      }
    }
    else {
      $result = $this -> ldap -> getUpdateData($data);
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

}

?>
