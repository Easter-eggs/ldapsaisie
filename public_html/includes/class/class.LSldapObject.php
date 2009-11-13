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

LSsession :: loadLSclass('LSattribute');

/**
 * Base d'un objet ldap
 *
 * Cette classe dÃ©finis la base de tout objet ldap gÃ©rÃ© par LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSldapObject { 
  
  var $config = array();
  var $type_name;
  var $attrs = array();
  var $forms;
  var $view;
  var $dn=false;
  var $oldDn=false;
  var $other_values=array();
  var $submitError=true;
  var $_whoami=NULL;
  var $_LSrelationsCache=array();

  var $_events=array();
  var $_objectEvents=array();
  
  var $cache=array();
  
  /**
   * Constructeur
   *
   * Cette methode construit l'objet et dÃ©finis la configuration.
   * Elle lance la construction du tableau d'attributs reprÃ©sentÃ©s par un objet LSattribute.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si l'objet a Ã©tÃ© construit, false sinon.
   */ 
  function LSldapObject() {
    $this -> type_name = get_class($this);
    $config = LSconfig :: get('LSobjects.'.$this -> type_name);
    if(is_array($config)) {
      $this -> config = $config;
    }
    else {
      LSerror :: addErrorCode('LSldapObject_01');
      return;
    }
    
    foreach($this -> config['attrs'] as $attr_name => $attr_config) {
      if(!$this -> attrs[$attr_name]=new LSattribute($attr_name,$attr_config,$this)) {
        return;
      }
    }
    
    return true;
  }
  
  /**
   * Charge les donnÃ©es de l'objet
   *
   * Cette methode dÃ©finis le DN de l'objet et charge les valeurs de attributs de l'objet
   * Ã  partir de l'annuaire.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string Le DN de l'objet.
   *
   * @retval boolean true si la chargement a rÃ©ussi, false sinon.
   */ 
  function loadData($dn) {
    $this -> dn = $dn;
    $data = LSldap :: getAttrs($dn);
    if(!empty($data)) {
      foreach($this -> attrs as $attr_name => $attr) {
        if(!$this -> attrs[$attr_name] -> loadData($data[$attr_name]))
          return;
      }
      return true;
    }
    return;
  }
  
  /**
   * Recharge les donnÃ©es de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la rechargement a rÃ©ussi, false sinon.
   */ 
  function reloadData() {
    $data = LSldap :: getAttrs($this -> dn);
    foreach($this -> attrs as $attr_name => $attr) {
      if(!$this -> attrs[$attr_name] -> reloadData($data[$attr_name]))
        return;
    }
    return true;
  }
  
  /**
   * Retourne le format d'affichage de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Format d'affichage de l'objet.
   */ 
  function getDisplayNameFormat() {
    return $this -> config['display_name_format'];
  }
  
  /**
   * Retourne la valeur descriptive d'affichage de l'objet
   * 
   * Cette fonction retourne la valeur descriptive d'affichage de l'objet en fonction
   * du format dÃ©fini dans la configuration de l'objet ou spÃ©cifiÃ© en paramÃ¨tre.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $spe [<i>optionnel</i>] string Format d'affichage de l'objet
   * @param[in] $full [<i>optionnel</i>] boolean True pour afficher en plus le
   *                                             subDnName
   *
   * @retval string Valeur descriptive d'affichage de l'objet
   */ 
  function getDisplayName($spe='',$full=false) {
    if ($spe=='') {
      $spe = $this -> getDisplayNameFormat();
    }
    $val = $this -> getFData($spe,&$this -> attrs,'getDisplayValue');
    if (LSsession :: haveSubDn() && $full) {
      $val.=' ('.$this -> subDnName.')';
    }
    return $val;
  }
  
  /**
   * Chaine formatÃ©e
   * 
   * Cette fonction retourne la valeur d'une chaine formatÃ©e en prennant les valeurs
   * de l'objet.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $format string Format de la chaine
   *
   * @retval string Valeur d'une chaine formatÃ©e
   */ 
  function getFData($format) {
    $format=getFData($format,$this,'getValue');
    return $format;
  }
  
  /**
   * Construit un formulaire de l'objet
   * 
   * Cette mÃ©thode construit un formulaire LSform Ã  partir de la configuration de l'objet
   * et de chaque attribut.
   *
   * @param[in] $idForm [<b>required</b>] Identifiant du formulaire a crÃ©er
   * @param[in] $load DN d'un objet similaire dont la valeur des attribut doit Ãªtre chargÃ© dans le formulaire.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval LSform Le formulaire crÃ©e
   */ 
  function getForm($idForm,$load=NULL) {
    LSsession :: loadLSclass('LSform');
    $LSform = new LSform($this,$idForm);
    $this -> forms[$idForm] = array($LSform,$load);
    
    if ($load) {
      $type = $this -> getType();
      $loadObject = new $type();
      if (!$loadObject -> loadData($load)) {
        $load=false;
      }
    }
    
    if ($load) {
      foreach($this -> attrs as $attr_name => $attr) {
        if(!$this -> attrs[$attr_name] -> addToForm($LSform,$idForm,$this,$loadObject -> attrs[$attr_name] -> getFormVal())) {
          $LSform -> can_validate = false;
        }
      }
    }
    else {
      foreach($this -> attrs as $attr_name => $attr) {
        if(!$this -> attrs[$attr_name] -> addToForm($LSform,$idForm,$this)) {
          $LSform -> can_validate = false;
        }
      }      
    }
    LSsession :: addJSconfigParam('LSform_'.$idForm,array(
      'ajaxSubmit' => ((isset($this -> config['LSform']['ajaxSubmit']))?$this -> config['LSform']['ajaxSubmit']:1)
    ));
    return $LSform;
  }
  
  /**
   * Construit un formulaire de l'objet
   * 
   * Cette mÃ©thode construit un formulaire LSform Ã  partir de la configuration de l'objet
   * et de chaque attribut.
   *
   * @param[in] $idForm [<b>required</b>] Identifiant du formulaire a crÃ©er
   * @param[in] $config Configuration spÃ©cifique pour le formulaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval LSform Le formulaire crÃ©e
   */ 
  function getView() {
    LSsession :: loadLSclass('LSform');
    $this -> view = new LSform($this,'view');
    foreach($this -> attrs as $attr_name => $attr) {
      $this -> attrs[$attr_name] -> addToView($this -> view);
    }
    $this -> view -> can_validate = false;
    return $this -> view;
  }  
  
  /**
   * Rafraichis le formulaire de l'objet
   * 
   * Cette mÃ©thode recharge les donnÃ©es d'un formulaire LSform.
   *
   * @param[in] $idForm [<b>required</b>] Identifiant du formulaire a crÃ©er
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true sile formulaire a Ã©tÃ© rafraichis, false sinon
   */ 
  function refreshForm($idForm) {
    $LSform = $this -> forms[$idForm][0];
    foreach($this -> attrs as $attr_name => $attr) {
      if(!$this -> attrs[$attr_name] -> refreshForm($LSform,$idForm)) {
        return;
      }
    }
    return true;
  }
  
  /**
   * Met Ã  jour les donnÃ©es de l'objet et de l'entrÃ© de l'annuaire
   * 
   * Met Ã  jour les donnÃ©es de l'objet Ã  partir d'un retour d'un formulaire.
   *
   * @param[in] $idForm Identifiant du formulaire d'origine
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la mise Ã  jour a rÃ©ussi, false sinon
   *
   * @see validateAttrsData()
   * @see submitChange()
   */ 
  function updateData($idForm=NULL) {
    if($idForm!=NULL) {
      if(isset($this -> forms[$idForm]))
        $LSform = $this -> forms[$idForm][0];
      else {
        LSerror :: addErrorCode('LSldapObject_02',$this -> getType());
        return;
      }
    }
    else {
      if(count($this -> forms) > 0) {
        reset($this -> forms);
        $idForm = key($this -> forms);
        $LSform = current($this -> forms);
        $config = $LSform[1];
        $LSform = $LSform[0];
      }
      else {
        LSerror :: addErrorCode('LSldapObject_03',$this -> getType());
        return;
      }
    }
    $new_data = $LSform -> exportValues();
    if(!is_array($new_data)) {
      return;
    }
    foreach($new_data as $attr_name => $attr_val) {
      if(isset($this -> attrs[$attr_name])) {
        $this -> attrs[$attr_name] -> setUpdateData($attr_val);
      }
    }
    if($this -> validateAttrsData($idForm)) {
      LSdebug("les données sont validées");
      
      if (!$this -> fireEvent('before_modify')) {
        return;
      }
      
      // $this -> attrs[ {inNewData} ] -> fireEvent('before_modify')
      foreach($new_data as $attr_name => $attr_val) {
        if (!$this -> attrs[$attr_name] -> fireEvent('before_modify')) {
          return;
        }
      }
      
      if ($this -> submitChange($idForm)) {
        LSdebug('Les modifications sont submitÃ©es');
        $this -> submitError = false;
        $this -> reloadData();
        $this -> refreshForm($idForm);
      }
      else {
        return;
      }
      
      // Event After Modify
      if(!$this -> submitError) {
        $this -> fireEvent('after_modify');
      }
      
      // $this -> attrs[*] => After Modify
      foreach($new_data as $attr_name => $attr_val) {
        $this -> attrs[$attr_name] -> fireEvent('after_modify');
      }
      return true;
    }
    else {
      return;
    }
  }
  
  /**
   * Valide les donnÃ©es retournÃ©es par un formulaire
   *
   * @param[in] $idForm Identifiant du formulaire d'origine
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si les donnÃ©es sont valides, false sinon
   */ 
  function validateAttrsData($idForm) {
    $retval = true;
    $LSform=$this -> forms[$idForm][0];
    foreach($this -> attrs as $attr) {
      $attr_values = $attr -> getValue();
      if (!$attr -> isValidate()) {
        if($attr -> isUpdate()) {
          if (!$this -> validateAttrData($LSform, $attr)) {
            $retval = false;
          }
        }
        else if( (empty($attr_values)) && ($attr -> isRequired()) ) { 
          if ( $attr -> canBeGenerated()) {
            if ($attr -> generateValue()) {
              if (!$this -> validateAttrData($LSform, $attr)) {
                LSerror :: addErrorCode('LSattribute_08',$attr -> getLabel());
                $retval = false;
              }
            }
            else {
              LSerror :: addErrorCode('LSattribute_07',$attr -> getLabel());
              $retval = false;
            }
          }
          else {
            LSerror :: addErrorCode('LSattribute_06',$attr -> getLabel());
            $retval = false;
          }
        }
      }
    }
    return $retval;
  }

   /**
   * Valide les donnÃ©es d'un attribut
   *
   * @param[in] $LSForm Formulaire d'origine
   * @param[in] &$attr Attribut Ã  valider
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si les donnÃ©es sont valides, false sinon
   */
  function validateAttrData(&$LSform,&$attr) {
    $retval = true;
    
    $vconfig=$attr -> getValidateConfig();

    $data=$attr -> getUpdateData();
    if(!is_array($data)) {
      $data=array($data);
    }

    // Validation des valeurs de l'attribut
    if(is_array($vconfig)) {
      foreach($vconfig as $test) {
        // DÃ©finition du basedn par dÃ©faut
        if (!isset($test['basedn'])) {
          $test['basedn']=LSsession :: getTopDn();
        }

        // DÃ©finition du message d'erreur
        if (!empty($test['msg'])) {
          $msg_error=getFData($test['msg'],$this,'getValue');
        }
        else {
          $msg_error=getFData(_("The attribute %{attr} is not valid."),$attr -> getLabel());
        }
        foreach($data as $val) {
          // validation par check LDAP
          if((isset($test['filter'])||isset($test['basedn']))&&(isset($test['result']))) {
            $sparams=(isset($test['scope']))?array('scope' => $test['scope']):array();
            $this -> other_values['val']=$val;
            $sfilter_user=(isset($test['basedn']))?getFData($test['filter'],$this,'getValue'):NULL;
            if(isset($test['object_type'])) {
              $test_obj = new $test['object_type']();
              $sfilter=$test_obj->getObjectFilter();
              $sfilter='(&'.$sfilter;
              if($sfilter_user[0]=='(') {
                $sfilter=$sfilter.$sfilter_user.')';
              }
              else {
                $sfilter=$sfilter.'('.$sfilter_user.'))';
              }
            }
            else {
              $sfilter=$sfilter_user;
            }
            $sbasedn=(isset($test['basedn']))?getFData($test['basedn'],$this,'getValue'):NULL;
            $ret=LSldap :: getNumberResult ($sfilter,$sbasedn,$sparams);
            if($test['result']==0) {
              if($ret!=0) {
                $LSform -> setElementError($attr,$msg_error);
                $retval = false;
              }
            }
            else {
              if($ret<0) {
                $LSform -> setElementError($attr,$msg_error);
                $retval = false;
              }
            }
          }
          // Validation par fonction externe
          else if(isset($test['function'])) {
            if (function_exists($test['function'])) {
              if(!$test['function']($this)) {
                $LSform -> setElementError($attr,$msg_error);
                $retval = false;
              }
            }
            else {
              LSerror :: addErrorCode('LSldapObject_04',array('attr' => $attr->name,'obj' => $this->getType(),'func' => $test['function']));
              $retval = false;
            }
          }
          else {
            LSerror :: addErrorCode('LSldapObject_05',array('attr' => $attr->name,'obj' => $this->getType()));
            $retval = false;
          }
        }
      }
    }
    // GÃ©nÃ©ration des valeurs des attributs dÃ©pendants
    $dependsAttrs=$attr->getDependsAttrs();
    if (!empty($dependsAttrs)) {
      foreach($dependsAttrs as $dependAttr) {
        if(!isset($this -> attrs[$dependAttr])){
          LSerror :: addErrorCode('LSldapObject_14',array('attr_depend' => $dependAttr, 'attr' => $attr -> getLabel()));
          continue;
        }
        if($this -> attrs[$dependAttr] -> canBeGenerated()) {
          if (!$this -> attrs[$dependAttr] -> generateValue()) {
            LSerror :: addErrorCode('LSattribute_07',$this -> attrs[$dependAttr] -> getLabel());
            $retval = false;
          }
        }
        else {
          LSerror :: addErrorCode('LSattribute_06',$this -> attrs[$dependAttr] -> getLabel());
          $retval = false;
        }
      }
    }

    $attr -> validate();
    unset($this -> other_values['val']);
    return $retval;
  }

  /**
   * Met Ã  jour les donnÃ©es modifiÃ©s dans l'annuaire
   *
   * @param[in] $idForm Identifiant du formulaire d'origine
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la mise Ã  jour a rÃ©ussi, false sinon
   */ 
  function submitChange($idForm) {
    $submitData=array();
    $new = $this -> isNew();
    foreach($this -> attrs as $attr) {
      if(($attr -> isUpdate())&&($attr -> isValidate())) {
        if(($attr -> name == $this -> config['rdn'])&&(!$new)) {
          $new = true;
          LSdebug('Rename');
          if (!$this -> fireEvent('before_rename')) {
            LSerror :: addErrorCode('LSldapObject_16');
            return;
          }
          $oldDn = $this -> getDn();
          $this -> dn = false;
          $newDn = $this -> getDn();
          if ($newDn) {
            if (!LSldap :: move($oldDn,$newDn)) {
              return;
            }
            $this -> dn = $newDn;
            $this -> oldDn = $oldDn;
            if (!$this -> fireEvent('after_rename')) {
              LSerror :: addErrorCode('LSldapObject_17');
              return;
            }
          }
          else {
            return;
          }
        }
        else {
          $submitData[$attr -> name] = $attr -> getUpdateData();
        }
      }
    }
    if(!empty($submitData)) {
      $dn=$this -> getDn();
      if($dn) {
        $this -> dn=$dn;
        LSdebug($submitData);
        if ($new) {
          if (!$this -> fireEvent('before_create')) {
            LSerror :: addErrorCode('LSldapObject_20');
            return;
          }
          foreach ($submitData as $attr_name => $attr) {
            if (!$this -> attrs[$attr_name] -> fireEvent('before_create')) {
              LSerror :: addErrorCode('LSldapObject_20');
              return;
            }
          }
        }
        if (!LSldap :: update($this -> getType(),$dn, $submitData)) {
          return;
        }
        if ($new) {
          if (!$this -> fireEvent('after_create')) {
            LSerror :: addErrorCode('LSldapObject_21');
            return;
          }
          foreach ($submitData as $attr_name => $attr) {
            if (!$this -> attrs[$attr_name] -> fireEvent('after_create')) {
              LSerror :: addErrorCode('LSldapObject_21');
              return;
            }
          }
        }
        return true;
      }
      else {
        LSerror :: addErrorCode('LSldapObject_13');
        return;
      }
    }
    else {
      return true;
    }
  }
  
  /**
   * Retourne les informations issus d'un DN
   *
   * @param[in] $dn Un DN.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau : 
   *                  - [0] : le premier paramÃ¨tre
   *                  - [1] : les paramÃ¨tres suivants
   */ 
  function getDnInfos($dn) {
    $infos=ldap_explode_dn($dn,0);
    if(!$infos)
      return;
    $first=true;
    for($i=1;$i<$infos['count'];$i++)
      if($first) {
        $basedn.=$infos[$i];
        $first=false;
      }
      else
        $basedn.=','.$infos[$i];
    return array($infos[0],$basedn);
  }
  
  /**
   * Retourne le filtre correpondants aux objetcClass de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string le filtre ldap correspondant au type de l'objet
   */ 
  function getObjectFilter($type=null) {
    if (is_null($type)) {
      $type = $this -> type_name;
    }
    $oc=LSconfig::get("LSobjects.$type.objectclass");
    if(!is_array($oc)) return;
    $filters=array();
    foreach ($oc as $class) {
      $filters[]=Net_LDAP2_Filter::create('objectClass','equals',$class);
    }
    
    $filter=LSconfig::get("LSobjects.$type.filter");
    if ($filter) {
      $filters[]=$filter;
    }

    $filter = LSldap::combineFilters('and',$filters,true);
    if ($filter)
      return $filter;
    LSerror :: addErrorCode('LSldapObject_30',$type);
    return;
  }
  
  /**
   * Retourne le filtre correpondants au pattern passé
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @param[in] $pattern string Le mot clé recherché
   * @param[in] $approx booléen Booléen activant ou non la recherche approximative
   *
   * @retval string le filtre ldap correspondant
   */ 
  function getPatternFilter($pattern=null,$approx=null) {
    if ($pattern!=NULL) {
      if (is_array($this -> config['LSsearch']['attrs'])) {
        $attrs=$this -> config['LSsearch']['attrs'];
      }
      else {
        $attrs=array($this -> config['rdn']);
      }
      $pfilter='(|';
      if ($approx) {
        foreach ($attrs as $attr_name) {
          $pfilter.='('.$attr_name.'~='.$pattern.')';
        }
      }
      else {
        foreach ($attrs as $attr_name) {
          $pfilter.='('.$attr_name.'=*'.$pattern.'*)';
        }
      }
      $pfilter.=')';
      return $pfilter;
    }
    else {
      return NULL;
    }
  }
  
  /**
   * Retourne une liste d'objet du mÃªme type.
   *
   * Effectue une recherche en fonction des paramÃ¨tres passÃ© et retourne un
   * tableau d'objet correspond au resultat de la recherche.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $filter array (ou string) Filtre de recherche Ldap / Tableau de filtres de recherche
   * @param[in] $basedn string DN de base pour la recherche
   * @param[in] $params array ParamÃ¨tres de recherche au format Net_LDAP2::search()
   *
   * @retval array Tableau d'objets correspondant au resultat de la recherche
   */ 
  function listObjects($filter=NULL,$basedn=NULL,$params=array()) {
    if (!LSsession :: loadLSclass('LSsearch')) {
      LSerror::addErrorCode('LSsession_05','LSsearch');
      return;
    }
    
    $sparams = array(
      'basedn' => $basedn,
      'filter' => $filter,
      'attributes' => array('dn')
    );

    if (is_array($params)) {    
      $sparams=array_merge($sparams,$params);
    }
    $LSsearch = new LSsearch($this -> type_name,'LSldapObjet::listObjects',$sparams,true);
    
    $LSsearch -> run();
    
    return $LSsearch -> listObjects();
    
/*
    for($i=0;$i<count($ret);$i++) {
      $retInfos[$i] = new $this -> type_name($this -> config);
      $retInfos[$i] -> loadData($ret[$i]['dn']);
    }
    
    return $retInfos;
*/
  }
  
  /**
   * Recherche les objets du mÃªme type dans l'annuaire
   *
   * Effectue une recherche en fonction des paramÃ¨tres passÃ© et retourne un
   * tableau array(dn => '', attrs => array()) d'objet correspondant au resultat*
   * de la recherche.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $filter array (ou string) Filtre de recherche Ldap / Tableau de filtres de recherche
   * @param[in] $basedn string DN de base pour la recherche
   * @param[in] $params array ParamÃ¨tres de recherche au format Net_LDAP2::search()
   *
   * @retval array Tableau d'objets correspondant au resultat de la recherche
   */ 
/*
  function search($filter='',$basedn=NULL,$params=array()) {
    $retInfos=array();
    $attrs=false;
    $check_final_dn=false;

    if(!is_array($filter))
      $filter=array(array('filter' => $filter));
    
    $nbFilter=count($filter);

    for($i=0;$i<$nbFilter;$i++) {
      $new_attrs=array();
      // DÃ©fintion des paramÃ¨tres de base pour la recherche
      $sbasedn=$basedn;
      $sparams=$params;
      $ret=array();
      if (isset($filter[$i]['scope']))
        $sparams["scope"]=$filter[$i]['scope'];
      
      // Definition des critÃ¨res de recherche correspondant au type d'objet Ã  lister
      if(($nbFilter==1)||(!isset($filter[$i]['attr']))) {
        // Filtre sur l'objet souhaitÃ©
        $sfilter='(&';
        $sfilter.=$this -> getObjectFilter();
        $sfilter_end=')';
        $check_final_dn=true;
      }
      // Initialisation des critÃ¨res d'une recherche intermÃ©diaire
      else {
        if(isset($filter[$i]['object_type'])) {
          $obj_tmp=new $filter[$i]['object_type']();
          $obj_filter=$obj_tmp->getObjectFilter();
          $sfilter='(&'.$obj_filter;
          $sfilter_end=')';
        }
        else {
          $sfilter='';
          $sfilter_end='';
        }
        if(isset($filter[$i]['scope'])) {
          $sparams['scope']=$filter[$i]['scope'];
        }
        if(isset($filter[$i]['basedn'])) {
          $sbasedn=$filter[$i]['basedn'];
        }
      }
      // Dans le cas d'une recherche intermÃ©diaire ou finale
      if($attrs!=false) {
        // Initialisation des variables
        $ret_gen=array();
        $new_attrs=array();
        
        // Pour tout les attributs retournÃ©s
        for($ii=0;$ii<count($attrs);$ii++) {
          $sfilter_for='';
          // DÃ©finition du filtre de recherche Ã  partir des paramÃ¨tres utilisateurs et
          // des paramÃ¨tres de recherche de l'objet Ã  listÃ© (dans le cas d'une recherche finale
          if((isset($filter[$i]['filter']))&&(!empty($filter[$i]['filter']))) {
            $sfilter_user=getFData($filter[$i]['filter'],$attrs[$ii]);
            if($sfilter_user[0]=='(')
              $sfilter_for=$sfilter.$sfilter_user;
            else
              $sfilter_for=$sfilter.'('.$sfilter_user.')';
          }
          else {
            $sfilter_for=$sfilter;
          }
          
          if(isset($filter[$i]['basedn'])) {
            $sbasedn=getFData($filter[$i]['basedn'],$attrs[$ii]);
            if ((!$this -> isCompatibleDNs($sbasedn,$basedn))&&($check_final_dn)) continue;
          }
        
          // VÃ©rification de la compatibilitÃ© du basedn de la recherche et du basedn gÃ©nÃ©rale
          // Finalisation du filtre
          $sfilter_for.=$sfilter_end;
        
        
          // Attributes
          if ($filter[$i]['attr']) {
            $sparams['attributes'] = array($filter[$i]['attr']);
          }
          else if (!isset($sparams['attributes'])) {
            $sparams['attributes'] = array($this -> config['rdn']);
          }
        
          // Execution de la recherche
          $ret=LSldap :: search ($sfilter_for,$sbasedn,$sparams);
          
          // Si il y un retour
          if(isset($ret[0])) {
            // si il ya une suite (recherche intermÃ©diaire)
            if($filter[$i]['attr']){
              for($iii=0;$iii<count($ret);$iii++) {
                if(isset($ret[$iii]['attrs'][$filter[$i]['attr']])) {
                  // cas de valeur multiple
                  if(is_array($ret[$iii]['attrs'][$filter[$i]['attr']])) {
                    foreach($ret[$iii]['attrs'][$filter[$i]['attr']] as $val_attr) {
                      $new_attrs[]=$val_attr;
                    }
                  }
                  // cas de valeur unique
                  else {
                    $new_attrs[]=$ret[$iii]['attrs'][$filter[$i]['attr']];
                  }
                }
              }
            }
          }
        }
        // cas du dernier filtre
        if(!empty($ret_gen)) {
          break;
        }
        // dans le cas d'une suite prÃ©vu mais d'un retour nul de la prÃ©cÃ©dente recherche
        else if(empty($new_attrs)) {
            // retour vide et arrÃªt de la recherche
            $ret=array();
            break;
        }
        else {
          $attrs=$new_attrs;
        }
      }
      // Dans le cas de la recherche initiale
      else {
        // DÃ©claration du filtre de recherche
        if((isset($filter[$i]['filter']))&&(!empty($filter[$i]['filter']))) {
          if($filter[$i]['filter'][0]=='(') {
            $sfilter.=$filter[$i]['filter'];
          }
          else {
            $sfilter.='('.$filter[$i]['filter'].')';
          }
        }
        // fermeture du filtre
        $sfilter.=$sfilter_end;
        
        // Attributes
        if (!isset($sparams['attributes'])) {
          $sparams['attributes'] = array($this -> config['rdn']);
        }
        
        // Lancement de la recherche
        $ret=LSldap :: search ($sfilter,$sbasedn,$sparams);
        
        //Si filtre multiple => on recupÃ¨re une liste d'attributs
        if(isset($filter[$i]['attr'])) {
          for($ii=0;$ii<count($ret);$ii++) {
            if(isset($ret[$ii]['attrs'][$filter[$i]['attr']])) {
              // cas de valeur multiple
              if(is_array($ret[$ii]['attrs'][$filter[$i]['attr']])) {
                foreach($ret[$ii]['attrs'][$filter[$i]['attr']] as $val_attr) {
                  $attrs[]=$val_attr;
                }
              }
              // cas de valeur unique
              else {
                $attrs[]=$ret[$ii]['attrs'][$filter[$i]['attr']];
              }
            }
          }
          
          // Si aucunne valeur n'est retournÃ©es
          if(empty($attrs)){
            // arrÃªt et retour Ã  zÃ©ro
            $ret=array();
            break;
          }
        }
        // Si recherche unique
        else {
          // prÃ©paration du retour finale
          if (!is_array($ret)) {
            $ret=array();
          }
          break;
        }
      }
    }
    return $ret;
  }
*/
  
  /**
   * Retourne une liste d'objet du mÃªme type et retourne leur noms
   *
   * Effectue une recherche en fonction des paramÃ¨tres passÃ© et retourne un
   * tableau (dn => nom) correspondant au resultat de la recherche.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $filter string Filtre de recherche Ldap
   * @param[in] $basedn string DN de base pour la recherche
   * @param[in] $params array ParamÃ¨tres de recherche au format Net_LDAP2::search()
   * @param[in] $displayFormat string Format d'affichage du nom des objets
   *
   * @retval array Tableau dn => name correspondant au resultat de la recherche
   */ 
  function listObjectsName($filter=NULL,$sbasedn=NULL,$sparams=array(),$displayFormat=false,$cache=true) {
    if (!LSsession :: loadLSclass('LSsearch')) {
      LSerror::addErrorCode('LSsession_05','LSsearch');
      return;
    }
    
    if (!$displayFormat) {
      $displayFormat = $this -> getDisplayNameFormat();
    }
    
    $params = array(
      'displayFormat' => $displayFormat,
      'basedn' => $sbasedn,
      'filter' => $filter
    );

    if (is_array($sparams)) {    
      $params=array_merge($sparams,$params);
    }
    
    $LSsearch = new LSsearch($this -> type_name,'LSldapObject::listObjectsName',$params,true);
    
    $LSsearch -> run($cache);
    
    return $LSsearch -> listObjectsName();
  }
 
 
  /**
   * Recherche un objet à partir de la valeur exact de son RDN ou d'un filtre de
   * recherche LDAP sous la forme d'un LSformat qui sera construit avec la valeur
   * de $name.
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @param[in] $name string Valeur de son RDN ou de la valeur pour composer le filtre
   * @param[in] $basedn string Le DN de base de la recherche
   * @param[in] $filter string Le filtre de recherche de l'objet
   * @param[in] $params array Tableau de paramètres
   * 
   * @retval array Tableau d'objets correspondant au resultat de la recherche
   */
  function searchObject($name,$basedn=NULL,$filter=NULL,$params=NULL) {
    if (!$filter) {
      $filter = '('.$this -> config['rdn'].'='.$name.')';
    }
    else {
      $filter = getFData($filter,$name);
    }
    return $this -> listObjects($filter,$basedn,$params); 
  }

  /**
   * Retourne une valeur de l'objet
   *
   * Retourne une valeur en fonction du paramÃ¨tre. Si la valeur est inconnue, la valeur retournÃ© est ' '.
   * tableau d'objet correspond au resultat de la recherche.
   *
   * Valeurs possibles :
   * - 'dn' ou '%{dn} : DN de l'objet
   * - [nom d'un attribut] : valeur de l'attribut
   * - [clef de $this -> other_values] : valeur de $this -> other_values
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $val string Le nom de la valeur demandÃ©e
   *
   * @retval mixed la valeur demandÃ© ou ' ' si celle-ci est inconnue.
   */ 
  function getValue($val) {
    if(($val=='dn')||($val=='%{dn}')) {
      return $this -> dn;
    }
    else if(($val=='rdn')||($val=='%{rdn}')) {
      return $this -> attrs[ $this -> config['rdn'] ] -> getValue();
    }
    else if(($val=='subDn')||($val=='%{subDn}')) {
      return $this -> subDnValue;
    }
    else if(($val=='subDnName')||($val=='%{subDnName}')) {
      return $this -> subDnName;
    }
    else if(isset($this ->  attrs[$val])){
      if (method_exists($this ->  attrs[$val],'getValue'))
        return $this -> attrs[$val] -> getValue();
      else
        return ' ';
    }
    else if(isset($this -> other_values[$val])){
      return $this -> other_values[$val];
    }
    else {
      return ' ';
    }
  }

  /**
   * Retourn un tableau pour un select d'un objet du mÃªme type
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array('dn' => 'display')
   */
  function getSelectArray($pattern=NULL,$topDn=NULL,$displayFormat=NULL,$approx=false,$cache=true) {
    return $this -> listObjectsName($filter,$topDn,array('pattern' => $pattern),$displayFormat,$cache);
  }

  /**
   * Retourne le DN de l'objet
   *
   * Cette methode retourne le DN de l'objet. Si celui-ci n'existe pas, il le construit Ã  partir de la 
   * configuration de l'objet et la valeur de son attribut rdn.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Le DN de l'objet
   */   
  function getDn() {
    if($this -> dn) {
      return $this -> dn;
    }
    else {
      $rdn_attr=$this -> config['rdn'];
      $topDn = LSsession :: getTopDn();
      if( (isset($this -> config['rdn'])) && (isset($this -> attrs[$rdn_attr])) && (isset($this -> config['container_dn'])) && ($topDn) ) {
        $rdn_val=$this -> attrs[$rdn_attr] -> getUpdateData();
        if (!empty($rdn_val)) {
          return $rdn_attr.'='.$rdn_val[0].','.$this -> config['container_dn'].','.$topDn;
        }
        else {
          LSerror :: addErrorCode('LSldapObject_12',$this -> config['rdn']);
          return;
        }
      }
      else {
        LSerror :: addErrorCode('LSldapObject_11',$this -> getType());
        return;
      }
    }
  }

  /**
   * Retourne le type de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval string Le type de l'objet ($this -> type_name)
   */
  function getType() {
    return $this -> type_name;
  }
  
  /**
   * Retourne qui est l'utilisateur par rapport Ã  cet object
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval string 'admin'/'self'/'user' pour Admin , l'utilisateur lui mÃªme ou un simple utilisateur
   */
  function whoami() {
    if (!$this -> _whoami)
      $this -> _whoami = LSsession :: whoami($this -> dn);
    return $this -> _whoami;
  }
  
  /**
   * Retourne le label de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval string Le label de l'objet ($this -> config['label'])
   */
  function getLabel($type=null) {
    if (is_null($type)) {
      $type = $this -> type_name;
    }
    return __(LSconfig::get("LSobjects.$type.label"));
  }
  
  
  /**
   * Supprime l'objet dans l'annuaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si l'objet Ã  Ã©tÃ© supprimÃ©, false sinon
   */
  function remove() {
    if ($this -> fireEvent('before_delete')) {
      if (LSldap :: remove($this -> getDn())) {
        if ($this -> fireEvent('after_delete')) {
          return true;
        }
        LSerror :: addErrorCode('LSldapObject_19');
      }
    }
    else {
      LSerror :: addErrorCode('LSldapObject_18');
    }
    return;
  }
  
  /**
   * L'objet est-il nouveau
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si l'objet est nouveau, false sinon
   */
  function isNew() {
    return (!$this -> dn);
  }

  /**
   * Retourne la valeur (DN) du subDn de l'objet  
   * 
   * @parram[in] $dn string Un DN
   * 
   * @return string La valeur du subDn de l'object
   */
  public static function getSubDnValue($dn) {
    $subDn_value='';
    $subDnLdapServer = LSsession :: getSortSubDnLdapServer();
    foreach ($subDnLdapServer as $subDn => $subDn_name) {
      if (isCompatibleDNs($subDn,$dn)&&($subDn!=$dn)) {
        $subDn_value=$subDn;
        break;
      }
    }
    return $subDn_value;
  }

  /**
   * Retourne la nom du subDn de l'objet  
   * 
   * @parram[in] $dn string Un DN
   * 
   * @return string Le nom du subDn de l'object
   */
  public static function getSubDnName($dn) {
    $subDnLdapServer = LSsession :: getSortSubDnLdapServer();
    return $subDnLdapServer[self :: getSubDnValue($dn)];
  }
  
  /**
   * Methode créant la liste des objets en relations avec l'objet courant et qui
   * la met en cache ($this -> _LSrelationsCache)
   * 
   * @retval True en cas de cas ce succès, False sinon.
   */
  function updateLSrelationsCache() {
    $this -> _LSrelationsCache=array();
    if (is_array($this->config['LSrelation'])) {
      $type = $this -> getType();
      $me = new $type();
      $me -> loadData($this -> getDn());
      foreach($this->config['LSrelation'] as $relation_name => $relation_conf) {
        if ( isset($relation_conf['list_function']) ) {
          if (LSsession :: loadLSobject($relation_conf['LSobject'])) {
            $obj = new $relation_conf['LSobject']();
            if ((method_exists($obj,$relation_conf['list_function']))&&(method_exists($obj,$relation_conf['getkeyvalue_function']))) {
              $list = $obj -> $relation_conf['list_function']($me);
              if (is_array($list)) {
                // Key Value
                $key = $obj -> $relation_conf['getkeyvalue_function']($me);
                
                $this -> _LSrelationsCache[$relation_name] = array(
                  'list' => $list,
                  'keyvalue' => $key
                );
              }
              else {
                LSdebug('Problème durant la mise en cache de la relation '.$relation_name);
                return;
              }
            }
            else {
              LSdebug('Les méthodes de mise en cache de la relation '.$relation_name. ' ne sont pas toutes disponibles.');
              return;
            }
          }
          else {
            return;
          }
        }
      }
    }
    return true;
  }
  
  /**
   * Methode executant les actions nécéssaires avant le changement du DN de
   * l'objet.
   * 
   * Cette méthode n'est qu'un exemple et elle doit être certainement réécrite
   * pour les objets plus complexe.
   * 
   * @retval True en cas de cas ce succès, False sinon.
   */
  function beforeRename() {
    // LSrelations
    return $this -> updateLSrelationsCache();
  }
  
  /**
   * Methode executant les actions nécéssaires après le changement du DN de
   * l'objet.
   * 
   * Cette méthode n'est qu'un exemple et elle doit être certainement réécrite
   * pour les objets plus complexe.
   * 
   * @retval True en cas de cas ce succès, False sinon.
   */
  function afterRename() {
    $error = 0;
    
    // Change LSsession -> userObject Dn
    if(LSsession :: getLSuserObjectDn() == $this -> oldDn) {
      LSsession :: changeAuthUser($this);
    }
    
    // LSrelations
    foreach($this -> _LSrelationsCache as $relation_name => $objInfos) {
      if ((isset($this->config['LSrelation'][$relation_name]['rename_function']))&&(is_array($objInfos['list']))) {
        foreach($objInfos['list'] as $obj) {
          $meth = $this->config['LSrelation'][$relation_name]['rename_function'];
          if (method_exists($obj,$meth)) {
            if (!($obj -> $meth($this,$objInfos['keyvalue']))) {
              $error=1;
            }
          }
          else {
            $error=1;
          }
        }
      }
    }
    return !$error;
  }
  
  /**
   * Methode executant les actions nécéssaires avant la suppression de
   * l'objet.
   * 
   * Cette méthode n'est qu'un exemple et elle doit être certainement réécrite
   * pour les objets plus complexe.
   * 
   * @retval True en cas de cas ce succès, False sinon.
   */
  function beforeDelete() {
    $return = $this -> updateLSrelationsCache();
    
    foreach(array_keys($this -> attrs) as $attr_name) {
      if (!$this -> attrs[$attr_name] -> fireEvent('before_delete')) {
        $return = false;
      }
    }
    
    return $return;
  }
  
  /**
   * Methode executant les actions nécéssaires après la suppression de
   * l'objet.
   * 
   * Cette méthode n'est qu'un exemple et elle doit être certainement réécrite
   * pour les objets plus complexe.
   * 
   * @retval True en cas de cas ce succès, False sinon.
   */
  function afterDelete() {
    $error = 0;
    
    // LSrelations
    foreach($this -> _LSrelationsCache as $relation_name => $objInfos) {
      if ((isset($this->config['LSrelation'][$relation_name]['remove_function']))&&(is_array($objInfos['list']))) {
        foreach($objInfos['list'] as $obj) {
          $meth = $this->config['LSrelation'][$relation_name]['remove_function'];
          if (method_exists($obj,$meth)) {
            if (!($obj -> $meth($this))) {
              $error=1;
            }
          }
          else {
            $error=1;
          }
        }
      }
    }
    
    // Binding LSattributes
    foreach(array_keys($this -> attrs) as $attr_name) {
      if (!$this -> attrs[$attr_name] -> fireEvent('after_delete')) {
        $error = true;
      }
    }
    
    // LSsearch : Purge LSobject cache
    if (LSsession :: loadLSclass('LSsearch')) {
      LSsearch :: purgeCache($this -> type_name);
    }
    
    return !$error;
  }
  
  /**
   * Methode executant les actions nécéssaires après la création de
   * l'objet.
   * 
   * Cette méthode n'est qu'un exemple et elle doit être certainement réécrite
   * pour les objets plus complexe.
   * 
   * @retval True en cas de cas ce succès, False sinon.
   */
  function afterCreate() {
    LSdebug('after');
    $error = 0;
    
    // container_auto_create
    if (LSsession :: isSubDnLSobject($this -> getType())) {
      if (is_array(LSsession :: $ldapServer['subDn']['LSobject'][$this -> getType()]['LSobjects'])) {
        foreach(LSsession :: $ldapServer['subDn']['LSobject'][$this -> getType()]['LSobjects'] as $type) {
          if (LSsession :: loadLSobject($type)) {
            $conf_type=LSconfig :: get("LSobjects.$type");
            if (isset($conf_type['container_auto_create'])&&isset($conf_type['container_dn'])) {
              $dn = $conf_type['container_dn'].','.$this -> getDn();
              if(!LSldap :: getNewEntry($dn,$conf_type['container_auto_create']['objectclass'],$conf_type['container_auto_create']['attrs'],true)) {
                LSdebug("Impossible de créer l'entrée fille : ".print_r(
                  array(
                    'dn' => $dn,
                    'objectClass' => $conf_type['container_auto_create']['objectclass'],
                    'attrs' => $conf_type['container_auto_create']['attrs']
                  )
                ,true));
                $error=1;
              }
            }
          }
          else {
            $error=1;
          }
        }
      }
    }
    
    // LSsearch : Purge LSobject cache
    if (LSsession :: loadLSclass('LSsearch')) {
      LSsearch :: purgeCache($this -> type_name);
    }
    
    return !$error;
  }
  
  /**
   * Methode executant les actions nécéssaires après la modification de
   * l'objet.
   * 
   * Cette méthode n'est qu'un exemple et elle doit être certainement réécrite
   * pour les objets plus complexe.
   * 
   * @retval True en cas de cas ce succès, False sinon.
   */
  function afterModify() {
    $error = 0;
    
    // LSsearch : Purge LSobject cache
    if (LSsession :: loadLSclass('LSsearch')) {
      LSsearch :: purgeCache($this -> type_name);
    }
    
    return !$error;
  }
  
  /**
   * Retourne la valeur clef d'un objet en relation
   * 
   * @param[in] $object Un object de type $objectType
   * @param[in] $attr L'attribut dans lequel l'objet doit apparaitre
   * @param[in] $objectType Le type d'objet en relation
   * @param[in] $value La valeur que doit avoir l'attribut :
   *                      - soit le dn (par defaut)
   *                      - soit la valeur [0] d'un attribut
   * 
   * @retval Mixed La valeur clef d'un objet en relation
   **/
  function getObjectKeyValueInRelation($object,$attr,$objectType,$attrValue='dn') {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','getObjectKeyValueInRelation');
      return;
    }
    if ($attrValue=='dn') {
      $val = $object -> getDn();
    }
    else {
      $val = $object -> getValue($attrValue);
      $val = $val[0];
    }
    return $val;
  }
  
  /**
   * Retourne la liste des relations pour l'objet en fonction de sa présence 
   * dans un des attributs
   * 
   * Retourne un tableau de d'objet (type : $objectType) correspondant à la 
   * relation entre l'objet $object et les objets de type $objectType. Cette relation
   * est établis par la présence de la valeur de référence à l'objet dans 
   * l'attribut des objets de type $objectType.
   * 
   * @param[in] $object Un object de type $objectType
   * @param[in] $attr L'attribut dans lequel l'objet doit apparaitre
   * @param[in] $objectType Le type d'objet en relation
   * @param[in] $value La valeur que doit avoir l'attribut :
   *                      - soit le dn (par defaut)
   *                      - soit la valeur [0] d'un attribut
   * 
   * @retval Array of $objectType Les objets en relations
   **/
  function listObjectsInRelation($object,$attr,$objectType,$attrValue='dn') {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','listObjectsInRelation');
      return;
    }
    if ($attrValue=='dn') {
      $val = $object -> getDn();
    }
    else {
      $val = $object -> getValue($attrValue);
      $val = $val[0];
    }
    if ($val) {
      $filter = Net_LDAP2_Filter::create($attr,'equals',$val);
      return $this -> listObjects($filter,LSsession :: getRootDn(),array('scope' => 'sub','recursive' => true,'withoutCache'=>true));
    }
    return;
  }

  /**
   * Ajoute un objet en relation dans l'attribut $attr de $this
   * 
   * @param[in] $object Un objet de type $objectType à ajouter
   * @param[in] $attr L'attribut dans lequel l'objet doit être ajouté
   * @param[in] $objectType Le type d'objet en relation
   * @param[in] $attrValue La valeur que doit avoir l'attribut :
   *                      - soit le dn (par defaut)
   *                      - soit la valeur [0] d'un attribut
   * @param[in] $canEditFunction  Le nom de la fonction pour vérifier que la
   *                              relation avec l'objet est éditable par le user
   * 
   * @retval boolean true si l'objet à été ajouté, False sinon
   **/  
  function addOneObjectInRelation($object,$attr,$objectType,$attrValue='dn',$canEditFunction=NULL) {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','addOneObjectInRelation');
      return;
    }
    if ($object instanceof $objectType) {
      if ($canEditFunction) {
        if (!$this -> $canEditFunction()) {
          LSerror :: addErrorCode('LSsession_11');
          return;
        }
      }
      if ($this -> attrs[$attr] instanceof LSattribute) {
        if ($attrValue=='dn') {
          $val = $object -> getDn();
        }
        else {
          $val = $object -> getValue($attrValue);
          $val = $val[0];
        }
        $values = $this -> attrs[$attr] -> getValue();
        if ($this -> attrs[$attr] -> config['multiple']) {
          if (!is_array($values)) {
            $updateData = array($val);
          }
          else if (!in_array($val,$values)) {
            $values[]=$val;
            $updateData = $values;
          }
        }
        else {
          if (($values[0]!=$val)&&($values!=$val)) {
            $updateData = array($val);
          }
        }
        if (isset($updateData)) {
          return LSldap :: update($this -> getType(),$this -> getDn(), array($attr => $updateData));
        }
        return true;
      }
    }
    return;
  }
  
  /**
   * Supprime un objet en relation dans l'attribut $attr de $this
   * 
   * @param[in] $object Un objet de type $objectType à supprimer
   * @param[in] $attr L'attribut dans lequel l'objet doit être supprimé
   * @param[in] $objectType Le type d'objet en relation
   * @param[in] $attrValue La valeur que doit avoir l'attribut :
   *                      - soit le dn (par defaut)
   *                      - soit la valeur [0] d'un attribut
   * @param[in] $canEditFunction  Le nom de la fonction pour vérifier que la
   *                              relation avec l'objet est éditable par le user
   * 
   * @retval boolean true si l'objet à été supprimé, False sinon
   **/  
  function deleteOneObjectInRelation($object,$attr,$objectType,$attrValue='dn',$canEditFunction=NULL) {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','deleteOneObjectInRelation');
      return;
    }
    if ($object instanceof $objectType) {
      if ($canEditFunction) {
        if (!$this -> $canEditFunction()) {
          LSerror :: addErrorCode('LSsession_11');
          return;
        }
      }
      if ($this -> attrs[$attr] instanceof LSattribute) {
        if ($attrValue=='dn') {
          $val = $object -> getDn();
        }
        else {
          $val = $object -> getValue($attrValue);
          $val = $val[0];
        }
        $values = $this -> attrs[$attr] -> getValue();
        if ((!is_array($values)) && (!empty($values))) {
          $values = array($values);
        }
        if (is_array($values)) {
          $updateData=array();
          foreach($values as $value) {
            if ($value!=$val) {
              $updateData[]=$value;
            }
          }
          return LSldap :: update($this -> getType(),$this -> getDn(), array($attr => $updateData));
        }
      }
    }
    return;
  }
  
 /**
  * Renome un objet en relation dans l'attribut $attr de $this
  * 
  * @param[in] $object Un objet de type $objectType à renomer
  * @param[in] $oldValue string L'ancienne valeur faisant référence à l'objet
  * @param[in] $attr L'attribut dans lequel l'objet doit être supprimé
  * @param[in] $objectType Le type d'objet en relation
  * @param[in] $attrValue La valeur que doit avoir l'attribut :
  *                      - soit le dn (par defaut)
  *                      - soit la valeur [0] d'un attribut
  *  
  * @retval boolean True en cas de succès, False sinon
  */
  function renameOneObjectInRelation($object,$oldValue,$attr,$objectType,$attrValue='dn') {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','renameOneObjectInRelation');
      return;
    }
    if ($object instanceof $objectType) {
      if ($this -> attrs[$attr] instanceof LSattribute) {
        $values = $this -> attrs[$attr] -> getValue();
        if ((!is_array($values)) && (!empty($values))) {
          $values = array($values);
        }
        if (is_array($values)) {
          $updateData=array();
          foreach($values as $value) {
            if ($value!=$oldValue) {
              $updateData[] = $value;
            }
            else {
              if ($attrValue=='dn') {
                $val = $object -> getDn();
              }
              else {
                $val = $object -> getValue($attrValue);
                $val = $val[0];
              }
              $updateData[] = $val;
            }
          }
          return LSldap :: update($this -> getType(),$this -> getDn(), array($attr => $updateData));
        }
      }
    }
    return;
  }
  
  /**
   * Met à jour les objets du meme type que $this en relation avec l'objet $object
   * de type $objectType en modifiant la valeur de leur attribut $attr des objets
   * en relation
   * 
   * @param[in] $object Mixed Un object (type : $this -> userObjectType) : l'utilisateur
   * @param[in] $listDns Array(string) Un tableau des DNs des objets en relation
   * @param[in] $attr L'attribut dans lequel l'utilisateur doit apparaitre
   * @param[in] $objectType Le type d'objet en relation
   * @param[in] $attrValue La valeur que doit avoir l'attribut :
   *                      - soit le dn (par defaut)
   *                      - soit la valeur [0] d'un attribut
   * @param[in] $canEditFunction  Le nom de la fonction pour vérifier que la
   *                              relation avec l'objet est éditable par le user
   * 
   * @retval boolean true si tout c'est bien passé, False sinon
   **/  
  function updateObjectsInRelation($object,$listDns,$attr,$objectType,$attrValue='dn',$canEditFunction=NULL) {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','updateObjectsInRelation');
      return;
    }
    $currentObjects = $this -> listObjectsInRelation($object,$attr,$objectType,$attrValue);
    $type=$this -> getType();
    if(is_array($currentObjects)) {
      if (is_array($listDns)) {
        $values=array();
        if ($attrValue!='dn') {
          $obj=new $objectType();
          foreach ($listDns as $dn) {
            $obj -> loadData($dn);
            $val = $obj -> getValue($attrValue);
            $values[$dn] = $val[0];
          }
        }
        else {
          foreach($listDns as $dn) {
            $values[$dn] = $dn;
          }
        }
        $dontDelete=array();
        $dontAdd=array();
        for ($i=0;$i<count($currentObjects);$i++) {
          if ($attrValue=='dn') {
            $val = $currentObjects[$i] -> getDn();
          }
          else {
            $val = $currentObjects[$i] -> getValue($attrValue);
            $val = $val[0];
          }
          if (in_array($val, $listDns)) {
            $dontDelete[$i]=true;
            $dontAdd[]=$val;
          }
        }
        
        for($i=0;$i<count($currentObjects);$i++) {
          if ($dontDelete[$i]) {
            continue;
          }
          else {
            if (!$currentObjects[$i] -> deleteOneObjectInRelation($object,$attr,$objectType,$attrValue,$canEditFunction)) {
              return;
            }
          }
        }
        
        foreach($values as $dn => $val) {
          if (in_array($val,$dontAdd)) {
            continue;
          }
          else {
            $obj = new $type();
            if ($obj -> loadData($dn)) {
              if (!$obj -> addOneObjectInRelation($object,$attr,$objectType,$attrValue,$canEditFunction)) {
                return;
              }
            }
            else {
              return;
            }
          }
        }
        return true;
      }
    }
    else {
      if(!is_array($listDns)) {
        return true;
      }
      foreach($listDns as $dn) {
        $obj = new $type();
        if ($obj -> loadData($dn)) {
          if (!$obj -> addOneObjectInRelation($object,$attr,$objectType,$attrValue,$canEditFunction)) {
            return;
          }
        }
        else {
          return;
        }
      }
    }
  }
  
  /**
   * Ajouter une action lors d'un événement
   * 
   * @param[in] $event string Le nom de l'événement
   * @param[in] $fct string Le nom de la fonction à exectuer
   * @param[in] $param mixed Paramètre pour le lancement de la fonction
   * @param[in] $class Nom de la classe possèdant la méthode $fct à executer
   * 
   * @retval void
   */
  function addEvent($event,$fct,$param=NULL,$class=NULL) {
    $this -> _events[$event][] = array(
      'function'  => $fct,
      'param'    => $param,
      'class'     => $class
    );
  }
  
  /**
   * Ajouter une action sur un objet lors d'un événement
   * 
   * @param[in] $event string Le nom de l'événement
   * @param[in] $obj object L'objet dont la méthode doit être executé
   * @param[in] $meth string Le nom de la méthode
   * @param[in] $param mixed Paramètre d'execution de la méthode
   * 
   * @retval void
   */
  function addObjectEvent($event,&$obj,$meth,$param=NULL) {
    $this -> _objectEvents[$event][] = array(
      'obj'  => $obj,
      'meth'  => $meth,
      'param'    => $param
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
    
    // Object event
    $return = $this -> fireObjectEvent($event);
    
    // Config
    if(isset($this -> config[$event])) {
      if (!is_array($this -> config[$event])) {
        $funcs = array($this -> config[$event]);
      }
      else {
        $funcs = $this -> config[$event];
      }
      foreach($funcs as $func) {
        if(function_exists($func)) {
          if(!$func($this)) {
            $return = false;
            LSerror :: addErrorCode('LSldapObject_07',array('func' => $func,'event' => $event));
          }
        }
        else {
          $return = false;
          LSerror :: addErrorCode('LSldapObject_06',array('func' => $func,'event' => $event));
        }
      }
    }
    
    // Binding via addEvent
    if (is_array($this -> _events[$event])) {
      foreach ($this -> _events[$event] as $e) {
        if ($e['class']) {
          if (class_exists($e['class'])) {
            $obj = new $e['class']();
            if (method_exists($obj,$e['fct'])) {
              try {
                $obj -> $e['fct']($e['param']);
              }
              catch(Exception $er) {
                LSerror :: addErrorCode('LSldapObject_10',array('class' => $e['class'],'meth' => $e['fct'],'event' => $event));
                $return = false;
              }
            }
            else {
              LSerror :: addErrorCode('LSldapObject_09',array('class' => $e['class'],'meth' => $e['fct'],'event' => $event));
              $return = false;
            }
          }
          else {
            LSerror :: addErrorCode('LSldapObject_08',array('class' => $e['class'],'meth' => $e['fct'],'event' => $event));
            $return = false;
          }
        }
        else {
          if (function_exists($e['fct'])) {
            try {
              $e['fct']($e['param']);
            }
            catch(Exception $er) {
              LSerror :: addErrorCode('LSldapObject_27',array('func' => $e['fct'],'event' => $event));
              $return = false;
            }
          }
          else {
            LSerror :: addErrorCode('LSldapObject_26',array('func' => $e['fct'],'event' => $event));
            $return = false;
          }
        }
      }
    }
    
    // Binding via addObjectEvent
    if (is_array($this -> _objectEvents[$event])) {
      foreach ($this -> _objectEvents[$event] as $e) {
        if (method_exists($e['obj'],$e['meth'])) {
          try {
            $e['obj'] -> $e['meth']($e['param']);
          }
          catch(Exception $er) {
            LSerror :: addErrorCode('LSldapObject_29',array('meth' => $e['meth'],'event' => $event));
            $return = false;
          }
        }
        else {
          LSerror :: addErrorCode('LSldapObject_28',array('meth' => $e['meth'],'event' => $event));
          $return = false;
        }
      }
    }
    
    return $return;
  }
  
  /**
   * Lance les actions à executer lors d'un événement sur l'objet lui-même
   * 
   * @param[in] $event string Le nom de l'événement
   * 
   * @retval boolean True si tout c'est bien passé, false sinon
   **/
  function fireObjectEvent($event) {
    switch($event) {
      case 'after_create':
        return $this -> afterCreate();
      case 'after_delete':
        return $this -> afterDelete();
      case 'after_rename':
        return $this -> afterRename();
      case 'after_modify':
        return $this -> afterModify();
/*
      case 'before_create':
        return $this -> beforeCreate();
*/
      case 'before_delete':
        return $this -> beforeDelete();
      case 'before_rename':
        return $this -> beforeRename();
/*
      case 'before_modify':
        return $this -> beforeModify();
*/
    }
    return true;
  }
  
  /**
   * Access to infos of the object
   * 
   * @param[in] $key string The name of the value
   * 
   * @retval mixed The value
   **/
  function __get($key) {
    if ($key=='subDnValue') {
      if ($this -> cache['subDnValue']) {
        return $this -> cache['subDnValue'];
      }
      $this -> cache['subDnValue'] = self :: getSubDnValue($this -> dn);
      return $this -> cache['subDnValue'];
    }
    if ($key=='subDnName') {
      if ($this -> cache['subDnName']) {
        return $this -> cache['subDnName'];
      }
      $this -> cache['subDnName'] = self :: getSubDnName($this -> dn);
      return $this -> cache['subDnName'];
    }
  }
  
}

/**
 * Error Codes
 **/
LSerror :: defineError('LSldapObject_01',
_("LSldapObject : Object type unknown.")
);
LSerror :: defineError('LSldapObject_02',
_("LSldapObject : Update form is not defined for the object %{obj}.")
);
LSerror :: defineError('LSldapObject_03',
_("LSldapObject : No form exists for the object %{obj}.")
);
LSerror :: defineError('LSldapObject_04',
_("LSldapObject : The function %{func} to validate the attribute %{attr} the object %{obj} is unknow.")
);
LSerror :: defineError('LSldapObject_05',
_("LSldapObject : Configuration data are missing to validate the attribute %{attr} of the object %{obj}.")
);

LSerror :: defineError('LSldapObject_06',
_("LSldapObject : The function %{func} to be executed on the object event %{event} doesn't exist.")
);
LSerror :: defineError('LSldapObject_07',
_("LSldapObject : The %{func} execution on the object event %{event} failed.")
);

LSerror :: defineError('LSldapObject_08',
_("LSldapObject : Class %{class}, which method %{meth} to be executed on the object event %{event}, doesn't exist.")
);
LSerror :: defineError('LSldapObject_09',
_("LSldapObject : Method %{meth} within %{class} class to be executed on object event %{event}, doesn't exist.")
);
LSerror :: defineError('LSldapObject_10',
_("LSldapObject : Error during execute %{meth} method within %{class} class, to be executed on object event %{event}.")
);

LSerror :: defineError('LSldapObject_11',
_("LSldapObject : Some configuration data of the object type %{obj} are missing to generate the DN of the new object.")
);
LSerror :: defineError('LSldapObject_12',
_("LSldapObject : The attibute %{attr} of the object is not yet defined. Can't generate DN.")
);
LSerror :: defineError('LSldapObject_13',
_("LSldapObject : Without DN, the object could not be changed.")
);
LSerror :: defineError('LSldapObject_14',
_("LSldapObject : The attribute %{attr_depend} depending on the attribute %{attr} doesn't exist.")
);
LSerror :: defineError('LSldapObject_15',
_("LSldapObject : Error during deleting the object %{objectname}.")
);

LSerror :: defineError('LSldapObject_16',
_("LSldapObject : Error during actions to be executed before renaming the objet.")
);
LSerror :: defineError('LSldapObject_17',
_("LSldapObject : Error during actions to be executed after renaming the objet.")
);

LSerror :: defineError('LSldapObject_18',
_("LSldapObject : Error during actions to be executed before deleting the objet.")
);
LSerror :: defineError('LSldapObject_19',
_("LSldapObject : Error during actions to be executed after deleting the objet.")
);

LSerror :: defineError('LSldapObject_20',
_("LSldapObject : Error during the actions to be executed before creating the object.")
);
LSerror :: defineError('LSldapObject_21',
_("LSldapObject : Error during the actions to be executed after creating the object. It was created anyway.")
);

LSerror :: defineError('LSldapObject_22',
_("LSldapObject : The function %{func} to be executed before creating the object doesn't exist.")
);
LSerror :: defineError('LSldapObject_23',
_("LSldapObject : Error executing the function %{func} to be execute after deleting the object.")
);
LSerror :: defineError('LSldapObject_24',
_("LSldapObject : The function %{func} to be executed after deleting the object doesn't exist.")
);
LSerror :: defineError('LSldapObject_25',
_("LSldapObject : Error executing the function %{func} to be execute after creating the object.")
);

LSerror :: defineError('LSldapObject_26',
_("LSldapObject : %{func} function, to be executed on object event %{event}, doesn't exist.")
);
LSerror :: defineError('LSldapObject_27',
_("LSldapObject : Error during the execution of %{func} function on object event %{event}.")
);

LSerror :: defineError('LSldapObject_28',
_("LSldapObject : %{meth} method, to be executed on object event %{event}, doesn't exist.")
);
LSerror :: defineError('LSldapObject_29',
_("LSldapObject : Error during execution of %{meth} method on object event %{event}.")
);
LSerror :: defineError('LSldapObject_30',
_("LSldapObject : Error during generate LDAP filter for %{LSobject}.")
);

// LSrelation
LSerror :: defineError('LSrelations_05',
_("LSrelation : Some parameters are missing in the call of methods to handle standard relations (Method : %{meth}).")
);

?>
