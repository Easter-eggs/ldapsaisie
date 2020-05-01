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
  public function __construct() {
    $this -> type_name = get_class($this);
    $config = LSconfig :: get('LSobjects.'.$this -> type_name);
    if(is_array($config)) {
      $this -> config = $config;
    }
    else {
      LSerror :: addErrorCode('LSldapObject_01');
      return;
    }

    foreach($this -> getConfig('attrs', array()) as $attr_name => $attr_config) {
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
  public function loadData($dn) {
    $this -> dn = $dn;
    $data = LSldap :: getAttrs($dn);
    if(!empty($data)) {
      foreach($this -> attrs as $attr_name => $attr) {
        if( !$this -> attrs[$attr_name] -> loadData( (isset($data[$attr_name])?$data[$attr_name]:NULL) ) )
          return;
      }
      $this->cache=array();
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
  public function reloadData() {
    $data = LSldap :: getAttrs($this -> dn);
    foreach($this -> attrs as $attr_name => $attr) {
      if(!$this -> attrs[$attr_name] -> reloadData( (isset($data[$attr_name])?$data[$attr_name]:NULL) ))
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
  public function getDisplayNameFormat() {
    return $this -> getConfig('display_name_format');
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
  public function getDisplayName($spe=null, $full=false) {
    if (is_null($spe))
      $spe = $this -> getDisplayNameFormat();
    $val = $this -> getFData($spe, $this -> attrs, 'getDisplayValue');
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
  public function getFData($format) {
    $format=getFData($format,$this,'getValue');
    return $format;
  }

  /**
   * Chaine formatee
   *
   * Cette fonction retourne la valeur d'une chaine formatee en prennant les valeurs
   * d'affichage de l'objet.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $format string Format de la chaine
   *
   * @retval string Valeur d'une chaine formatee
   */
  public function getDisplayFData($format) {
    return getFData($format,$this,'getDisplayValue');
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
  public function getForm($idForm,$load=NULL) {
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
  public function getView() {
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
  public function refreshForm($idForm) {
    $LSform = $this -> forms[$idForm][0];
    foreach($this -> attrs as $attr_name => $attr) {
      if(!$this -> attrs[$attr_name] -> refreshForm($LSform,$idForm)) {
        return;
      }
    }
    return true;
  }

  /**
   * Update LDAP object data from one form specify by it's ID.
   *
   * This method just valid form ID, extract form data and call
   * _updateData() private method.
   *
   * @param[in] $idForm Form ID
   * @param[in] $justValidate Boolean to enable just validation mode
   *
   * @see _updateData()
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true if object data was updated, false otherwise
   */
  public function updateData($idForm=NULL,$justValidate=False) {
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
    return $this -> _updateData($new_data,$idForm,$justValidate);
  }

  /**
   * Update LDAP object data from one form specify by it's ID.
   *
   * This method implement the continuation and the end of the object data
   * udpate.
   *
   * @param[in] $new_data Array of object data
   * @param[in] $idForm Form ID
   * @param[in] $justValidate Boolean to enable just validation mode
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true if object data was updated, false otherwise
   *
   * @see updateData()
   * @see validateAttrsData()
   * @see submitChange()
   */
  private function _updateData($new_data,$idForm=null,$justValidate=False) {
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
      if ($justValidate) {
        LSdebug('Just validate mode');
        return True;
      }

      if (!$this -> fireEvent('before_modify')) {
        return;
      }

      // $this -> attrs[ {inNewData} ] -> fireEvent('before_modify')
      foreach($new_data as $attr_name => $attr_val) {
        if ($this -> attrs[$attr_name] -> isUpdate() && !$this -> attrs[$attr_name] -> fireEvent('before_modify')) {
          return;
        }
      }

      if ($this -> submitChange($idForm)) {
        LSdebug('Les modifications sont submitÃ©es');
        // Event After Modify
        $this -> fireEvent('after_modify');

        // $this -> attrs[*] => After Modify
        foreach($new_data as $attr_name => $attr_val) {
          if ($this -> attrs[$attr_name] -> isUpdate()) {
            $this -> attrs[$attr_name] -> fireEvent('after_modify');
          }
        }
        $this -> reloadData();
        $this -> refreshForm($idForm);
      }
      else {
        return;
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
  public function validateAttrsData($idForm=null) {
    $retval = true;
    if ($idForm) {
      $LSform=$this -> forms[$idForm][0];
    }
    else {
      $LSform=false;
    }
    foreach($this -> attrs as $attr_name => $attr) {
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
            // Don't blame on non-create form for attributes not-present in form (or freezed)
            if ($LSform && $idFrom != 'create' && (!$LSform -> hasElement($attr_name) || $LSform -> isFreeze($attr_name)))
              continue;

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
  public function validateAttrData(&$LSform,&$attr) {
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
          $msg_error=getFData(__($test['msg']),$this,'getValue');
        }
        else {
          $msg_error=getFData(_("The attribute %{attr} is not valid."),$attr -> getLabel());
        }
        foreach($data as $val) {
          // validation par check LDAP
          if((isset($test['filter'])||isset($test['basedn']))&&(isset($test['result']))) {
            $sparams=array('onlyAccessible' => False);
            if (isset($test['scope']))
              $sparams['scope'] = $test['scope'];
            $this -> other_values['val']=$val;
            // Filter from test configuration
            if (isset($test['filter']) && !empty($test['filter'])) {
              $sfilter_user=getFData($test['filter'],$this,'getValue');
              if ($sfilter_user[0]!='(') $sfilter_user="(".$sfilter_user.")";
              $sfilter_user=Net_LDAP2_Filter::parse($sfilter_user);
            }
            else {
              $sfilter_user=NULL;
            }
            if(isset($test['object_type']) && LSsession :: loadLSobject($test['object_type']) ) {
              $sfilter=self :: _getObjectFilter($test['object_type']);

              if ($sfilter_user) {
                $sfilter=LSldap::combineFilters('and',array($sfilter_user,$sfilter));
              }
            }
            else {
              $sfilter=$sfilter_user;
            }
            $sbasedn=(isset($test['basedn']))?getFData($test['basedn'],$this,'getValue'):NULL;
            if (isset($test['except_current_object']) && (bool)$test['except_current_object'] && !$LSform -> idForm!='create') {
              $sret=LSldap :: search ($sfilter,$sbasedn,$sparams);
              $dn=$this->getDn();
              $ret=0;
              foreach($sret as $obj) {
                if ($obj['dn']!=$dn)
                  $ret++;
              }
            }
            else {
              $ret=LSldap :: getNumberResult ($sfilter,$sbasedn,$sparams);
            }
            if($test['result']==0) {
              if($ret!=0) {
                if ($LSform) $LSform -> setElementError($attr,$msg_error);
                $retval = false;
              }
            }
            else {
              if($ret<0) {
                if ($LSform) $LSform -> setElementError($attr,$msg_error);
                $retval = false;
              }
            }
          }
          // Validation par fonction externe
          else if(isset($test['function'])) {
            if (function_exists($test['function'])) {
              if(!call_user_func_array($test['function'],array(&$this))) {
                if ($LSform) $LSform -> setElementError($attr,$msg_error);
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
          elseif (!$this -> validateAttrData($LSform,$this -> attrs[$dependAttr])) {
            LSerror :: addErrorCode('LSattribute_08',$this -> attrs[$dependAttr] -> getLabel());
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
  public function submitChange($idForm) {
    $submitData=array();
    $new = $this -> isNew();
    foreach($this -> attrs as $attr) {
      if(($attr -> isUpdate())&&($attr -> isValidate())) {
        if(($attr -> name == $this -> getConfig('rdn')) && (!$new)) {
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

            // PHP Net_LDAP2 does not remove old RDN value : replace RDN value
            $submitData[$attr -> name] = $attr -> getUpdateData();

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
  public static function getDnInfos($dn) {
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
   * Retourne le filtre correpondants aux objetcClass de l'objet courant
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval Net_LDAP2_Filter le filtre ldap correspondant au type de l'objet
   */
  public function getObjectFilter() {
    return self :: getObjectFilter($this -> type_name);
  }

  /**
   * Retourne le filtre correpondants aux objetcClass de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval Net_LDAP2_Filter le filtre ldap correspondant au type de l'objet
   */
  public static function _getObjectFilter($type) {
    $oc=LSconfig::get("LSobjects.$type.objectclass");
    if(!is_array($oc)) return;
    $filters=array();
    foreach ($oc as $class) {
      $filters[]=Net_LDAP2_Filter::create('objectClass','equals',$class);
    }

    $filter=LSconfig::get("LSobjects.$type.filter");
    if ($filter) {
      $filters[]=Net_LDAP2_Filter::parse($filter);
    }

    $filter = LSldap::combineFilters('and',$filters);
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
  public function getPatternFilter($pattern=null,$approx=null) {
    if ($pattern) {
      $search = new LSsearch($this -> type_name, 'LSldapObject', array('approx' => (bool)$approx));
      $filter = $search -> getFilterFromPattern($pattern);
      if ($filter instanceof Net_LDAP2_Filter) {
        return $filter -> asString();
      }
    }
    return NULL;
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
  public function listObjects($filter=NULL,$basedn=NULL,$params=array()) {
    if (!LSsession :: loadLSclass('LSsearch')) {
      LSerror::addErrorCode('LSsession_05','LSsearch');
      return;
    }

    $sparams = array(
      'basedn' => $basedn,
      'filter' => $filter
    );

    if (is_array($params)) {
      $sparams=array_merge($sparams,$params);
    }
    $LSsearch = new LSsearch($this -> type_name,'LSldapObjet::listObjects',$sparams,true);

    $LSsearch -> run();

    return $LSsearch -> listObjects();
  }

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
  public function listObjectsName($filter=NULL,$sbasedn=NULL,$sparams=array(),$displayFormat=false,$cache=true) {
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
  public function searchObject($name,$basedn=NULL,$filter=NULL,$params=NULL) {
    if (!$filter) {
      $filter = '('.$this -> getConfig('rdn').'='.$name.')';
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
  public function getValue($val) {
    if(($val=='dn')||($val=='%{dn}')) {
      return $this -> dn;
    }
    else if(($val=='rdn')||($val=='%{rdn}')) {
      return $this -> rdn;
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
   * Retourne une valeur d'affichage de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $val string Le nom de la valeur demandee
   *
   * @retval mixed la valeur demandee ou ' ' si celle-ci est inconnue.
   */
  public function getDisplayValue($val) {
    if(isset($this ->  attrs[$val])){
      if (method_exists($this ->  attrs[$val],'getDisplayValue'))
        return $this -> attrs[$val] -> getDisplayValue();
      else
        return ' ';
    }
    else {
      return $this -> getValue($val);
    }
  }

  /**
   * Ajoute une valeur dans le tableau $this -> other_values
   *
   * @param[in] $name string Le nom de la valeur
   * @param[in] $value mixed La valeur
   *
   * @retval void
   **/
  public function registerOtherValue($name,$value) {
    $this -> other_values[$name]=$value;
  }

  /**
   * Retourn un tableau pour un select d'un objet du mÃªme type
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array('dn' => 'display')
   */
  public function getSelectArray($pattern=NULL,$topDn=NULL,$displayFormat=NULL,$approx=false,$cache=true,$filter=NULL,$sparams=array()) {
    $sparams['pattern']=$pattern;
    return $this -> listObjectsName($filter,$topDn,$sparams,$displayFormat,$cache);
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
  public function getDn() {
    if($this -> dn) {
      return $this -> dn;
    }
    else {
      $container_dn=$this -> getContainerDn();
      if ($container_dn) {
        $rdn_attr = $this -> getConfig('rdn');
        if( $rdn_attr && isset($this -> attrs[$rdn_attr]) ) {
          $rdn_val=$this -> attrs[$rdn_attr] -> getUpdateData();
          if (!empty($rdn_val)) {
            return $rdn_attr.'='.$rdn_val[0].','.$container_dn;
          }
          else {
            LSerror :: addErrorCode('LSldapObject_12', $rdn_attr);
            return;
          }
        }
        else {
          LSerror :: addErrorCode('LSldapObject_11',$this -> getType());
          return;
        }
      }
      return;
    }
  }

  /**
   * Retourne le container DN de l'objet
   *
   * Cette methode retourne le container DN de l'objet.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Le container DN de l'objet
   */
  public function getContainerDn() {
    $topDn = LSsession :: getTopDn();
    $generate_container_dn = $this -> getConfig('generate_container_dn');
    $container_dn = $this -> getConfig('container_dn');
    if ($generate_container_dn) {
      if (is_callable($generate_container_dn)) {
        try {
          $container_dn = call_user_func_array($generate_container_dn, array(&$this));
          return $container_dn.','.$topDn;
        }
        catch (Exception $e) {
          LSerror :: addErrorCode('LSldapObject_34',$e);
        }
      }
      else {
        LSerror :: addErrorCode('LSldapObject_33', $generate_container_dn);
      }
    }
    else if ($container_dn && $topDn) {
      return $container_dn.','.$topDn;
    }
    else {
      LSerror :: addErrorCode('LSldapObject_11',$this -> getType());
    }
    LSerror :: addErrorCode('LSldapObject_32');
    return;
  }

  /**
   * Retourne le type de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Le type de l'objet ($this -> type_name)
   */
  public function getType() {
    return $this -> type_name;
  }

  /**
   * Retourne qui est l'utilisateur par rapport Ã  cet object
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string 'admin'/'self'/'user' pour Admin , l'utilisateur lui mÃªme ou un simple utilisateur
   */
  public function whoami() {
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
  public function getLabel($type=null) {
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
  public function remove() {
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
  public function isNew() {
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
  private function updateLSrelationsCache() {
    $this -> _LSrelationsCache=array();
    $LSrelations = $this -> getConfig('LSrelation');
    if (is_array($LSrelations) && LSsession :: loadLSclass('LSrelation')) {
      $type = $this -> getType();
      $me = new $type();
      $me -> loadData($this -> getDn());
      foreach($LSrelations as $relation_name => $relation_conf) {
        $relation = new LSrelation($me, $relation_name);
        $list = $relation -> listRelatedObjects();
        if (is_array($list)) {
          $this -> _LSrelationsCache[$relation_name] = array(
            'list' => $list,
            'keyvalue' => $relation -> getRelatedKeyValue()
          );
        }
        else {
          LSdebug('Problème durant la mise en cache de la relation '.$relation_name);
          return;
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
  private function beforeRename() {
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
  private function afterRename() {
    $error = 0;

    // Change LSsession -> userObject Dn
    if(LSsession :: getLSuserObjectDn() == $this -> oldDn) {
      LSsession :: changeAuthUser($this);
    }

    // LSrelations
    foreach($this -> _LSrelationsCache as $relation_name => $objInfos) {
      $relation = new LSrelation($this, $relation_name);
      if (is_array($objInfos['list'])) {
        foreach($objInfos['list'] as $obj) {
          if (!$relation -> renameRelationWithObject($obj, $objInfos['keyvalue'])) {
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
  private function beforeDelete() {
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
  private function afterDelete() {
    $error = 0;

    // LSrelations
    foreach($this -> _LSrelationsCache as $relation_name => $objInfos) {
      $relation = new LSrelation($this, $relation_name);
      if (is_array($objInfos['list'])) {
        foreach($objInfos['list'] as $obj) {
          if (!$relation -> canEditRelationWithObject($obj)) {
            LSerror :: addErrorCode('LSsession_11');
          }
          elseif (!$relation -> removeRelationWithObject($obj)) $error=1;
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
  private function afterCreate() {
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
  private function afterModify() {
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
   * @param[in] $objectType Le type d'objet en relation
   * @param[in] $attrValues La/les valeur(s) que doit/peut avoir l'attribut :
   *                        - soit le dn (par defaut)
   *                        - soit une des valeurs d'un attribut
   *
   * @retval Mixed La valeur clef d'un objet en relation
   **/
  public static function getObjectKeyValueInRelation($object,$objectType,$attrValues='dn') {
    if (!$objectType) {
      LSerror :: addErrorCode('LSrelations_05','getObjectKeyValueInRelation');
      return;
    }
    if (!is_array($attrValues)) $attrValues=array($attrValues);
    $keyValues=array();
    foreach ($attrValues as $attrValue) {
      if ($attrValue=='dn') {
        $dn=$object -> getDn();
        if (!in_array($dn,$keyValues))
          $keyValues[] = $dn;
      }
      else {
        $values=$object -> getValue($attrValue);
        if (is_array($values))
          foreach ($values as $keyValue)
            if (!in_array($keyValue,$keyValues))
              $keyValues[]=$keyValue;
      }
    }
    return $keyValues;
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
   * @param[in] $attrValues La/les valeur(s) que doit/peut avoir l'attribut :
   *                      - soit le dn (par defaut)
   *                      - soit une des valeurs d'un attribut
   *
   * @retval Array of $objectType Les objets en relations
   **/
  public function listObjectsInRelation($object,$attr,$objectType,$attrValues='dn') {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','listObjectsInRelation');
      return;
    }
    if (!is_array($attrValues)) $attrValues=array($attrValues);
    $keyValues=self :: getObjectKeyValueInRelation($object,$objectType,$attrValues);
    if (!empty($keyValues)) {
      $keyValuesFilters=array();
      foreach($keyValues as $keyValue) {
        $keyValuesFilters[] = Net_LDAP2_Filter::create($attr,'equals',$keyValue);
      }
      $filter = LSldap::combineFilters('or', $keyValuesFilters);
      return $this -> listObjects($filter,LSsession :: getRootDn(),array('scope' => 'sub','recursive' => true,'withoutCache'=>true, 'onlyAccessible' => false));
    }

    return array();
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
  public function addOneObjectInRelation($object,$attr,$objectType,$attrValue='dn',$canEditFunction=NULL) {
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
      elseif (!LSsession::canEdit($this -> getType(), $this -> getDn(), $attr)) {
        LSerror :: addErrorCode('LSsession_11');
        return;
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
          return $this -> _updateData(array($attr => $updateData));
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
   * @param[in] $attrValues L'ensembe des valeurs que peut avoir l'attribut avant mise à jour :
   *                        - soit le dn (par defaut)
   *                        - soit une des valeurs d'un attribut
   *
   * @retval boolean true si l'objet à été supprimé, False sinon
   **/
  public function deleteOneObjectInRelation($object,$attr,$objectType,$attrValue='dn',$canEditFunction=NULL,$attrValues=null) {
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
      elseif (!LSsession::canEdit($this -> getType(), $this -> getDn(), $attr)) {
        LSerror :: addErrorCode('LSsession_11');
        return;
      }
      if ($this -> attrs[$attr] instanceof LSattribute) {
        if (!is_array($attrValues)) $attrValues=array($attrValue);
        $keyValues=self :: getObjectKeyValueInRelation($object,$objectType,$attrValues);
        $values = $this -> attrs[$attr] -> getValue();
        if ((!is_array($values)) && (!empty($values))) {
          $values = array($values);
        }
        if (is_array($values)) {
          $updateData=array();
          foreach($values as $value) {
            if (!in_array($value,$keyValues)) {
              $updateData[]=$value;
            }
          }
          return $this -> _updateData(array($attr => $updateData));
        }
      }
    }
    return;
  }

 /**
  * Renome un objet en relation dans l'attribut $attr de $this
  *
  * @param[in] $object Un objet de type $objectType à renomer
  * @param[in] $oldValues array|string Le(s) ancienne(s) valeur(s possible(s)
  *                                    faisant référence à l'objet
  * @param[in] $attr L'attribut dans lequel l'objet doit être supprimé
  * @param[in] $objectType Le type d'objet en relation
  * @param[in] $attrValue La valeur que doit avoir l'attribut :
  *                      - soit le dn (par defaut)
  *                      - soit la valeur [0] d'un attribut
  *
  * @retval boolean True en cas de succès, False sinon
  */
  public function renameOneObjectInRelation($object,$oldValues,$attr,$objectType,$attrValue='dn') {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','renameOneObjectInRelation');
      return;
    }
    if (!is_array($oldValues)) $oldValues=array($oldValues);
    if ($object instanceof $objectType) {
      if ($this -> attrs[$attr] instanceof LSattribute) {
        $values = $this -> attrs[$attr] -> getValue();
        if ((!is_array($values)) && (!empty($values))) {
          $values = array($values);
        }
        if (is_array($values)) {
          $updateData=array();
          foreach($values as $value) {
            if (!in_array($value,$oldValues)) {
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
          return $this -> _updateData(array($attr => $updateData));
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
   * @param[in] $attrValues L'ensembe des valeurs que peut avoir l'attribut avant mise à jour :
   *                        - soit le dn (par defaut)
   *                        - soit une des valeurs d'un attribut
   *
   * @retval boolean true si tout c'est bien passé, False sinon
   **/
  public function updateObjectsInRelation($object,$listDns,$attr,$objectType,$attrValue='dn',$canEditFunction=NULL,$attrValues=null) {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelations_05','updateObjectsInRelation');
      return;
    }
    if (!is_array($attrValues)) $attrValues=array($attrValue);
    $currentDns=array();
    $currentObjects = $this -> listObjectsInRelation($object,$attr,$objectType,$attrValues);
    if(is_array($currentObjects)) {
      for ($i=0;$i<count($currentObjects);$i++) {
        $currentDns[]=$currentObjects[$i] -> getDn();
      }
    }
    $dontTouch=array_intersect($listDns,$currentDns);

    for($i=0;$i<count($currentObjects);$i++) {
      if (in_array($currentObjects[$i] -> getDn(),$dontTouch)) continue;
      if (!$currentObjects[$i] -> deleteOneObjectInRelation($object,$attr,$objectType,$attrValue,$canEditFunction,$attrValues)) {
        return;
      }
    }

    $type=$this -> getType();
    foreach($listDns as $dn) {
      if (in_array($dn,$dontTouch)) continue;
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
    return true;
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
  public function addEvent($event,$fct,$param=NULL,$class=NULL) {
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
  public function addObjectEvent($event,&$obj,$meth,$param=NULL) {
    $this -> _objectEvents[$event][] = array(
      'obj'  => &$obj,
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
  public function fireEvent($event) {

    // Object event
    $return = $this -> fireObjectEvent($event);

    // Config
    $funcs = $this -> getConfig($event);
    if($funcs) {
      if (!is_array($funcs))
        $funcs = array($this -> config[$event]);
      foreach($funcs as $func) {
        if(function_exists($func)) {
          if(!call_user_func_array($func, array(&$this))) {
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
    if (isset($this -> _events[$event]) && is_array($this -> _events[$event])) {
      foreach ($this -> _events[$event] as $e) {
        if ($e['class']) {
          if (class_exists($e['class'])) {
            $obj = new $e['class']();
            if (method_exists($obj,$e['fct'])) {
              try {
                call_user_func_array(array($obj,$e['fct']),array(&$e['param']));
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
              call_user_func_array($e['fct'],array(&$e['param']));
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
    if (isset($this -> _objectEvents[$event]) && is_array($this -> _objectEvents[$event])) {
      foreach ($this -> _objectEvents[$event] as $e) {
        if (method_exists($e['obj'],$e['meth'])) {
          try {
            call_user_func_array(array($e['obj'], $e['meth']),array(&$e['param']));
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
  public function fireObjectEvent($event) {
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
  public function __get($key) {
    if ($key=='subDnValue') {
      if (isset($this -> cache['subDnValue'])) {
        return $this -> cache['subDnValue'];
      }
      $this -> cache['subDnValue'] = self :: getSubDnValue($this -> dn);
      return $this -> cache['subDnValue'];
    }
    elseif ($key=='subDnName') {
      if ($this -> cache['subDnName']) {
        return $this -> cache['subDnName'];
      }
      $this -> cache['subDnName'] = self :: getSubDnName($this -> dn);
      return $this -> cache['subDnName'];
    }
    elseif ($key=='rdn') {
      $rdn_attr = $this -> getConfig('rdn');
      if ($rdn_attr && isset($this -> attrs[ $rdn_attr ])) {
        return $this -> attrs[ $rdn_attr ] -> getValue();
      }
      return false;
    }
  }

  /**
   * List IOformats of this object type
   *
   * @retval mixed Array of valid IOformats of this object type
   **/
  public function listValidIOformats() {
    $ret=array();
    $ioFormats = $this -> getConfig('ioFormat');
    if (is_array($ioFormats)) {
      foreach($ioFormats as $name => $conf) {
        $ret[$name] = _((isset($conf['label'])?$conf['label']:$name));
      }
    }
    return $ret;
  }

  /**
   * Check if an IOformat is valid for this object type
   *
   * @param[in] $f string The IOformat name to check
   *
   * @retval boolean True if it's a valid IOformat, false otherwise
   **/
  public function isValidIOformat($f) {
    return is_array($this -> getConfig("ioFormat.$f"));
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

 /**
  * Allow conversion of LdapObject to string
  *
  * @retval string The string representation of the LdapObject
  */
  public function __toString() {
    if ($this -> dn)
      return "<LdapObject ".$this -> dn.">";
    $rdn_attr = $this -> getConfig('rdn');
    if( $rdn_attr && isset($this -> attrs[$rdn_attr]) ) {
      $rdn_val = $this -> attrs[$rdn_attr] -> getUpdateData();
      if (!empty($rdn_val))
        return "<LdapObject (new) $rdn_attr=".$rdn_val[0].">";
    }
    return "<LdapObject (new)>";
  }

  /**
   * CLI show command
   *
   * @param[in] $command_args array Command arguments :
   *   - Positional arguments :
   *     - LSobject type
   *     - object DN
   *   - Optional arguments :
   *     - -r|--raw-values : show raw values (instead of display ones)
   *
   * @retval boolean True on succes, false otherwise
   **/
  public static function cli_show($command_args) {
    $objType = null;
    $dn = null;
    $raw_values = false;
    foreach ($command_args as $arg) {
      if ($arg == '-r' || $arg == '--raw-value')
        $raw_values = true;
      elseif (is_null($objType)) {
        $objType = $arg;
      }
      elseif (is_null($dn)) {
        $dn = $arg;
      }
      else
        LScli :: usage("Invalid $arg parameter.");
    }

    if (is_null($objType) || is_null($dn))
      LScli :: usage('You must provide LSobject type and DN.');

    if (!LSsession :: loadLSobject($objType))
      return false;

    $obj = new $objType();
    if (!$obj->loadData($dn)) {
      LSlog :: fatal("Fail to load object $dn data from LDAP");
      return false;
    }

    echo $obj -> _cli_show($raw_values);

    return true;
  }

  /**
   * CLI helper to show the object info
   *
   * @param[in] $raw_values bool Show attributes raw values (instead of display ones)
   *
   * @retval void
   **/
  public function _cli_show($raw_values=false) {
    echo $this -> type_name." (".($this -> dn?$this -> dn:'new').") :\n";

    // Show attributes
    if (is_array($this -> getConfig('LSform.layout'))) {
      foreach($this -> getConfig('LSform.layout') as $tab_name => $tab) {
        echo "  - ".(isset($tab['label'])?$tab['label']:$tab_name)." :\n";
        foreach ($tab['args'] as $attr_name) {
          $this -> _cli_show_attr($attr_name, $raw_values, "  ");
        }
        echo "\n";
      }
    }
    else {
      foreach ($this -> attrs as $attr_name => $attr) {
        $this -> _cli_show_attr($attr_name, $raw_values);
      }
      echo "\n";
    }

    // Show LSrelations
    if (LSsession :: loadLSclass('LSrelation') && is_array($this -> getConfig('LSrelation'))) {
      foreach ($this -> getConfig('LSrelation') as $rel_name => $rel_conf) {
        echo "  - ".(isset($rel_conf['label'])?$rel_conf['label']:$rel_name)." :\n";
        $relation = new LSrelation($this, $rel_name);
        $list = $relation -> listRelatedObjects();
        if (is_array($list)) {
          foreach($list as $o) {
            echo "    - ".$o -> getDisplayName(NULL,true)." (".$o -> getDn().")\n";
          }
          if (empty($list)) {
            echo "    => ".(isset($rel_conf['emptyText'])?$rel_conf['emptyText']:"No objects.")."\n";
          }
        }
        else {
          LSlog :: error("Fail to load related objects.");
        }
        echo "\n";
      }
    }
  }

  /**
   * CLI helper to show the attribute
   *
   * @param[in] $attr_name string The attribute name
   * @param[in] $raw_values bool Show attributes raw values (instead of display ones)
   * @param[in] $prefix string Prefix for each line displayed (optional, default: no prefix)
   *
   * @retval void
   **/
  public function _cli_show_attr($attr_name, $raw_values=false, $prefix="") {
    if (!isset($this -> attrs[$attr_name]))
      return;
    echo "$prefix  - ".$this -> attrs[$attr_name]->getLabel()." ($attr_name) :";
    $values = ($raw_values?$this -> attrs[$attr_name]->getValue():$this -> attrs[$attr_name]->getDisplayValue());
    if (empty($values)) {
      echo " empty\n";
      return true;
    }
    if (!is_array($values)) $values = array($values);

    // Truncate values if too long
    for ($i=0; $i < count($values); $i++)
      if (strlen($values[$i]) > 70)
        $values[$i] = substr($values[$i], 0, 65)."[...]";
    echo (count($values) > 1?"\n$prefix    - ":" ");
    echo  implode("\n$prefix    - ", $values);
    echo "\n";
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

LSerror :: defineError('LSldapObject_31',
_("LSldapObject : Error during execution of the custom action %{customAction} on %{objectname}.")
);

LSerror :: defineError('LSldapObject_32',
_("LSldapObject : Fail to retrieve container DN.")
);
LSerror :: defineError('LSldapObject_33',
_("LSldapObject : The function %{func} to generate container DN is not callable.")
);
LSerror :: defineError('LSldapObject_34',
_("LSldapObject : Error during generating container DN : %{error}")
);

// LSrelation
LSerror :: defineError('LSrelations_05',
_("LSrelation : Some parameters are missing in the call of methods to handle standard relations (Method : %{meth}).")
);

// LScli
LScli :: add_command(
    'show',
    array('LSldapObject', 'cli_show'),
    'Show an LSobject',
    '[object type] [dn] [-r|--raw-values]'
);
