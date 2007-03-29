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
 * Attribut Ldap
 *
 * Cette classe modélise un attribut Ldap
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattribute {
  
  var $name;
  var $config;
  var $ldap;
  var $html;
  var $data;
  var $updateData=false;
  var $is_validate=false;
  
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
   *
   * @retval boolean Retourne true si la création a réussi, false sinon.
   */	
  function LSattribute ($name,$config) {
    $this -> name = $name;
    $this -> config = $config;
    $html_type = "LSattr_html_".$config['html_type'];
    $ldap_type = "LSattr_ldap_".$config['ldap_type'];
    if((class_exists($html_type))&&(class_exists($ldap_type))) {
      $this -> html = new $html_type($name,$config);
      $this -> ldap = new $ldap_type($name,$config);
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(41,array('html'=>$config['html_type'],'ldap'=>$config['ldap_type']));
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
    $this -> data = $attr_data;
    return true;
  }
  
  /**
   * DEBIG : affiche la valeur de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  function debug_printValue() {
    print $this -> data;
  }
  
  /**
   * Retourne la valeur de l'attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed La valeur de l'attribut
   */
  function getValue() {
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
   * @param[in] object LSform Le formulaire dans lequel doit être ajouté l'attribut
   * @param[in] string L'identifiant du formulaire
   *
   * @retval boolean true si l'ajout a fonctionner ou qu'il n'est pas nécessaire, false sinon
   */
  function addToForm(&$form,$idForm) {
    if(isset($this -> config['form'][$idForm])) {
      $element = $this -> html -> addToForm($form,$idForm);
      if($this -> config['required']==1)
        $form->addRule($this -> name, "Le champ '".$this -> config['label']."' est obligatoire.",'required', null, 'client');
      /// !!!!! A CHANGER !!!!!!! \\\\ => utiliser une fonction de traitement de donnée
      if($this -> data !='')
        $element -> setValue($this -> getFormVal());
      else if (isset($this -> config['default_value']))
        $element -> setValue($this -> config['default_value']);
      if($this -> config['form'][$idForm]==0)
        $element -> freeze();
      if(isset($this -> config['check_data'])) {
        if(is_array($this -> config['check_data'])) {
          foreach ($this -> config['check_data'] as $rule => $rule_infos) {
            if((!$form -> isRuleRegistered($rule))&&($rule!='')) {
              $GLOBALS['LSerror'] -> addErrorCode(43,array('attr' => $this->name,'rule' => $rule));
              return;
            }
            if(!isset($rule_infos['msg']))
              $rule_infos['msg']='La valeur du champs '.$this -> config['label'].' est invalide.';
            if(!isset($rule_infos['param']))
              $rule_infos['param']=NULL;
            $form -> addRule($this -> name,$rule_infos['msg'],$rule,$rule_infos['param'],$GLOBALS['LSconfig']['check_data_place']);
          }
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(44,$this->name);
        }
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
    return $this -> getDisplayValue();
  }
  
  function setUpdateData($data) {
    if($this -> getFormVal() != $data)
      $this -> updateData=$data;
  }
  
  /**
   * Vérifie si l'attribut a été validé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'attribut a été validé, false sinon
   */
  function isValidate() {
    return ((!isset($this -> config['validation'])) || ($this -> is_validate));
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
   * Retourne la valeur de l'attribut pour son enregistrement dans l'annuaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed La valeur de l'attribut pour son enregistrement dans l'annuaire
   */
  function getUpdateData() {
    $data=($this ->isUpdate())?$this -> updateData:$this -> data;
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
        return $result;
      }
      else {
        if (function_exists($this -> config['onSave'])) {
          return $this -> config['onSave']($data);
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(45,array('attr' => $this->name,'func' => $this -> config['onSave']));
          return;
        }
      }
    }
    return $this -> ldap -> getUpdateData($data);
  }
  
  /**
   * Retourne la configuration de validation de l'annuaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed La configuration de validation de l'attribut
   */
  function getValidateConfig() {
    return $this -> config['validation'];
  }
  
}

?>