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
LSsession :: loadLSclass('LSattribute');

/**
 * Base d'un objet ldap
 *
 * Cette classe dÃ©finis la base de tout objet ldap gÃ©rÃ© par LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSldapObject extends LSlog_staticLoggerClass {

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
   * Load object data from LDAP
   *
   * This method set object DN and load its data from LDAP
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string The object DN
   * @param[in] $additional_filter string|Net_LDAP2_Filter|null A custom LDAP filter that LDAP object
   *                                                            must respect to permit its data loading
   *
   * @retval boolean True if object data loaded, false otherwise
   */
  public function loadData($dn, $additional_filter=null) {
    $this -> dn = $dn;
    $filter = $this -> getObjectFilter();
    if ($additional_filter) {
      if (!is_a($additional_filter, Net_LDAP2_Filter))
        $additional_filter = Net_LDAP2_Filter :: parse($additional_filter);
      $filter = Net_LDAP2_Filter :: combine(
        'and',
        array ($filter, $additional_filter)
      );
    }
    $data = LSldap :: getAttrs($dn, $filter, array_keys($this -> attrs));
    if(is_array($data) && !empty($data)) {
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
   * Constuct a form for this object
   *
   * This method create a LSform from the object and its attributes configurations.
   *
   * @param[in] $idForm string Form identifier (required)
   * @param[in] $load string DN of a similar object. If defined, attributes values of this object will be loaded in the form.
   * @param[in] $api_mode boolean Enable API mode (defaut: false)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval LSform The created LSform object
   */
  public function getForm($idForm, $load=NULL, $api_mode=false) {
    LSsession :: loadLSclass('LSform');
    $LSform = new LSform($this, $idForm, null, $api_mode);
    $this -> forms[$idForm] = array($LSform, $load);

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
   * Constuct a view for this object
   *
   * This method create a LSform in view mode from the object and its attributes configurations.
   *
   * @param[in] $api_mode boolean Enable API mode (defaut: false)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval LSform The created LSform object
   */
  public function getView($api_mode=false) {
    LSsession :: loadLSclass('LSform');
    $this -> view = new LSform($this, 'view', null, $api_mode);
    foreach($this -> attrs as $attr_name => $attr) {
      $this -> attrs[$attr_name] -> addToView($this -> view, $api_mode);
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
   * _updateData() protected method.
   *
   * @param[in] $idForm Form ID
   * @param[in] $justCheck Boolean to enable just check mode
   *
   * @see _updateData()
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true if object data was updated, false otherwise
   */
  public function updateData($idForm=NULL, $justCheck=False) {
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
    self :: log_debug($this. "->updateData($idForm, $justCheck): new data=".varDump($new_data));
    return $this -> _updateData($new_data, $idForm, $justCheck);
  }

  /**
   * Update LDAP object data from one form specify by it's ID.
   *
   * This method implement the continuation and the end of the object data
   * udpate.
   *
   * @param[in] $new_data Array of object data
   * @param[in] $idForm Form ID
   * @param[in] $justCheck Boolean to enable just check mode
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true if object data was updated, false otherwise
   *
   * @see updateData()
   * @see validateAttrsData()
   * @see submitChange()
   */
  protected function _updateData($new_data, $idForm=null, $justCheck=False) {
    if(!is_array($new_data)) {
      return;
    }
    foreach($new_data as $attr_name => $attr_val) {
      if(isset($this -> attrs[$attr_name])) {
        $this -> attrs[$attr_name] -> setUpdateData($attr_val);
      }
    }
    if($this -> validateAttrsData($idForm, $justCheck)) {
      self :: log_debug($this."->_updateData(): Update data are validated");
      if ($justCheck) {
        self :: log_debug($this."->_updateData(): Just validate mode");
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

      $new = $this -> isNew();
      if ($this -> submitChange($idForm)) {
        self :: log_debug($this."->_updateData(): changes are submited");
        // Event After Modify
        if (!$new && $idForm != 'create') {
          $this -> fireEvent('after_modify');

          // $this -> attrs[*] => After Modify
          foreach($new_data as $attr_name => $attr_val) {
            if ($this -> attrs[$attr_name] -> isUpdate()) {
              $this -> attrs[$attr_name] -> fireEvent('after_modify');
            }
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
   * Validate data returned by form
   *
   * @param[in] $idForm string The source LSform ID
   * @param[in] $justCheck Boolean to enable just check mode (do not validate data using LDAP search)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if form data are valid, False otherwise
   */
  public function validateAttrsData($idForm=null, $justCheck=False) {
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
          if (!$this -> validateAttrData($LSform, $attr, $justCheck)) {
            $retval = false;
          }
        }
        else if( (empty($attr_values)) && ($attr -> isRequired()) ) {
          if ( $attr -> canBeGenerated()) {
            if ($attr -> generateValue()) {
              if (!$this -> validateAttrData($LSform, $attr, $justCheck)) {
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
            if ($LSform && $idForm != 'create' && (!$LSform -> hasElement($attr_name) || $LSform -> isFreeze($attr_name)))
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
   * Validate one attribute's data returned by form
   *
   * @param[in] $idForm string The source LSform ID
   * @param[in] &$attr LSattribute The attribute to validate
   * @param[in] $justCheck Boolean to enable just check mode (do not validate data using LDAP search)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if form data for this attribute are valid, False otherwise
   */
  public function validateAttrData(&$LSform, &$attr, $justCheck=false) {
    $retval = true;

    $vconfig = $attr -> getValidateConfig();

    // Validate attribute values
    if ($justCheck) {
      self :: log_trace(
        "validateAttrData(".$LSform->idForm.", ".$attr->name."): ".
        "just check mode: do not validate attribute data using LDAP search"
      );
    }
    else {
      foreach($attr -> getConfig('validation', array(), 'array') as $test) {
        self :: log_trace(
          "validateAttrData(".$LSform->idForm.", ".$attr->name."): ".
          "run validation with following config:\n".varDump($test)
        );

        // Generate error message
        $msg_error = LSconfig :: get('msg', null, 'string', $test);
        if ($msg_error) {
          $msg_error = $this -> getFData(__($msg_error));
        }
        else {
          $msg_error = getFData(_("The attribute %{attr} is not valid."), $attr -> getLabel());
        }

        // Iter on attribute values to check all of it
        foreach(ensureIsArray($attr -> getUpdateData()) as $val) {
          // Validation using LDAP search
          if((isset($test['filter']) || isset($test['basedn'])) && isset($test['result'])) {
            $this -> other_values['val'] = $val;
            $sparams = array(
              'scope' => LSconfig :: get('scope', 'sub', 'string', $test),
              'onlyAccessible' => False,
            );

            // Handle filter parameter
            $sfilter_user = LSconfig :: get('filter', null, 'string', $test);
            if ($sfilter_user) {
              $sfilter_user = $this -> getFData($test['filter']);
              // Check if filter format is surronded by '()'
              if ($sfilter_user[0] != '(')
                $sfilter_user = "($sfilter_user)";
              $sfilter_user = Net_LDAP2_Filter::parse($sfilter_user);
            }

            // Handle object_type parameter & compose final LDAP filter string
            $object_type = LSconfig :: get('object_type', null, 'string', $test);
            if($object_type && LSsession :: loadLSobject($object_type) ) {
              $sfilter = self :: _getObjectFilter($object_type);
              if ($sfilter_user)
                $sfilter = LSldap::combineFilters('and', array($sfilter_user, $sfilter));
            }
            else {
              $sfilter = $sfilter_user;
            }

            // Handle base_dn parameter
            $sbasedn = LSconfig :: get('basedn', null, 'string', $test);
            if ($sbasedn)
              $sbasedn = $this -> getFData($sbasedn);

            // If except_current_object (and not create form), list matching objets to exclude current one
            if (LSconfig :: get('except_current_object', false, 'bool', $test) &&
                $LSform -> idForm != 'create') {
              $sret = LSldap :: search($sfilter, $sbasedn, $sparams);
              $dn = $this->getDn();
              $ret = 0;
              foreach($sret as $obj)
                if ($obj['dn'] != $dn)
                  $ret++;
            }
            else {
              // Otherwise, just retrieve number of matching objets
              $ret = LSldap :: getNumberResult($sfilter, $sbasedn, $sparams);
              if (!is_int($ret)) {
                // An error occured
                $retval = false;
                continue;
              }
            }

            // Check result
            $configured_result = LSconfig :: get('result', null, 'int', $test);
            if(
                ($configured_result == 0 && $ret != 0) ||
                ($configured_result > 0 && $ret <= 0)
              ) {
              $retval = false;
              self :: log_warning(
                "validateAttrData(".$LSform->idForm.", ".$attr->name."): ".
                "validation with LDAP search on base DN='$sbasedn' and ".
                "filter='".($sfilter?$sfilter->as_string():null)."' error ($ret object(s) found)"
              );
              if ($LSform)
                $LSform -> setElementError($attr, $msg_error);
            }
            else {
              self :: log_trace(
                "validateAttrData(".$LSform->idForm.", ".$attr->name."): ".
                "validation with LDAP search on base DN='$sbasedn' and ".
                "filter='".($sfilter?$sfilter->as_string():null)."' success ($ret object(s) found)"
              );
            }
          }
          // Validation using external function
          else if(isset($test['function'])) {
            if (function_exists($test['function'])) {
              if(!call_user_func_array($test['function'], array(&$this))) {
                if ($LSform)
                  $LSform -> setElementError($attr,$msg_error);
                $retval = false;
              }
            }
            else {
              LSerror :: addErrorCode(
                'LSldapObject_04',
                array(
                  'attr' => $attr->name,
                  'obj' => $this->getType(),
                  'func' => $test['function']
                )
              );
              $retval = false;
            }
          }
          else {
            LSerror :: addErrorCode(
              'LSldapObject_05',
              array(
                'attr' => $attr->name,
                'obj' => $this->getType()
              )
            );
            $retval = false;
          }
        }
      }
    }

    // Generate values of dependent attributes
    $dependsAttrs = $attr->getDependsAttrs();
    if (empty($dependsAttrs)) {
      self :: log_trace(
        "validateAttrData(".$LSform->idForm.", ".$attr->name."): ".
        "no dependent attribute"
      );
    }
    else {
      foreach($dependsAttrs as $dependAttr) {
        if(!isset($this -> attrs[$dependAttr])){
          LSerror :: addErrorCode(
            'LSldapObject_14',
            array('attr_depend' => $dependAttr, 'attr' => $attr -> getLabel())
          );
          continue;
        }
        self :: log_debug(
          "validateAttrData(".$LSform->idForm.", ".$attr->name."): ".
          'Attribute '.$attr->name.' updated: generate new value for attribute '.$dependAttr
        );
        if($this -> attrs[$dependAttr] -> canBeGenerated()) {
          if (!$this -> attrs[$dependAttr] -> generateValue()) {
            LSerror :: addErrorCode('LSattribute_07', $this -> attrs[$dependAttr] -> getLabel());
            $retval = false;
          }
          elseif (!$this -> validateAttrData($LSform, $this -> attrs[$dependAttr], $justCheck)) {
            LSerror :: addErrorCode('LSattribute_08', $this -> attrs[$dependAttr] -> getLabel());
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
   * Submit changes made to LDAP directory
   *
   * @param[in] $idForm string The source LSform ID
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if changes are successfully push to LDAP directory, False otherwise
   */
  public function submitChange($idForm) {
    $submitData=array();
    $new = $this -> isNew();
    if (!$new && $idForm == 'create')
      self :: log_fatal("submitChange($idForm): object isn't considered as new !?");
    foreach($this -> attrs as $attr) {
      if(($attr -> isUpdate())&&($attr -> isValidate())) {
        if(($attr -> name == $this -> getConfig('rdn')) && (!$new)) {
          self :: log_debug("$this -> submitChange($idForm): renaming detected");
          if (!$this -> fireEvent('before_rename')) {
            LSerror :: addErrorCode('LSldapObject_16');
            return;
          }
          $oldDn = $this -> getDn();
          $this -> dn = false;
          $newDn = $this -> getDn();
          if ($newDn) {
            self :: log_debug("$this -> submitChange($idForm): Rename me from '$oldDn' to '$newDn'");
            if (!LSldap :: move($oldDn, $newDn)) {
              self :: log_error("$this -> submitChange($idForm): Fail to rename me from '$oldDn' to '$newDn'. Stop.");
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
            self :: log_error("$this -> submitChange($idForm): fail to retrieve new DN");
            return;
          }
        }
        else {
          $submitData[$attr -> name] = $attr -> getUpdateData();
        }
      }
    }
    if(!empty($submitData)) {
      $dn = $this -> getDn();
      if (!$dn) {
        LSerror :: addErrorCode('LSldapObject_13');
        return;
      }

      $this -> dn = $dn;
      self :: log_debug($this." -> submitChange($idForm): submitData=".varDump($submitData));
      if ($new) {
        // Check DN is not already exist
        if (LSldap :: exists($dn)) {
          return;
        }
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
        self :: log_debug($this." -> submitChange($idForm): LSldap :: update() failed");
        return;
      }
      self :: log_debug($this." -> submitChange($idForm): changes applied in LDAP");
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
      self :: log_debug($this." -> submitChange($idForm): no change");
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
    return self :: _getObjectFilter($this -> type_name);
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
   * Return an objet value
   *
   * The value returned depends of the $val parameter. If the requested value is unknown, the returned value
   * will be $default (default: a space caracter = ' ').
   *
   * Supported values:
   * - 'dn' ou '%{dn} : The object DN
   * - [attribute name] : the value of the corresponding attribute
   * - [key of $this -> other_values] : the value of corresponding key in $this -> other_values
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $val string The requested value
   * @param[in] $first boolean If true and the result is an array, return only the first value (optional, default: false)
   * @param[in] $default mixed Default value if unknown (optional, default: a space caracter = ' ')
   *
   * @retval mixed The requested value or $default if unknown
   */
  public function getValue($val, $first=false, $default=' ') {
    $return = $default;
    if(($val=='dn')||($val=='%{dn}')) {
      $return = $this -> dn;
    }
    else if(($val=='rdn')||($val=='%{rdn}')) {
      $return = $this -> rdn;
    }
    else if(($val=='subDn')||($val=='%{subDn}')) {
      $return = $this -> subDnValue;
    }
    else if(($val=='subDnName')||($val=='%{subDnName}')) {
      $return = $this -> subDnName;
    }
    else if(isset($this ->  attrs[$val])){
      if (method_exists($this ->  attrs[$val],'getValue'))
        $return = ensureIsArray($this -> attrs[$val] -> getValue());
    }
    else if(isset($this -> other_values[$val])){
      $return = $this -> other_values[$val];
    }
    if (is_array($return) && $first)
      $return = (count($return) >= 1?$return[0]:null);
    return $return;
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
   * Return connected user's LSprofiles on this object
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Array of LSprofiles of connected user's LSprofiles on this object
   */
  public function whoami() {
    if (!$this -> _whoami)
      $this -> _whoami = LSsession :: whoami($this -> dn);
    return $this -> _whoami;
  }

  /**
   * Retrieve object type translated label
   *
   * @param[in] $type string|null The object type (optional, default: called class name)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string The translated object type label
   */
  public static function getLabel($type=null) {
    if (is_null($type))
      $type = get_called_class();
    self :: log_debug("getLabel($type): LSobjects.$type.label");
    $label = LSconfig::get("LSobjects.$type.label");
    if (!$label) return;
    return __($label);
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
          self :: log_debug('Problème durant la mise en cache de la relation '.$relation_name);
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
    self :: log_debug('after');
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
                self :: log_debug("Impossible de créer l'entrée fille : ".print_r(
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
  public static function getObjectKeyValueInRelation($object, $objectType, $attrValues='dn') {
    if (!$objectType) {
      LSerror :: addErrorCode('LSrelation_05','getObjectKeyValueInRelation');
      return;
    }
    $keyValues = array();
    foreach (ensureIsArray($attrValues) as $attrValue) {
      if ($attrValue == 'dn') {
        $dn=$object -> getDn();
        if (!in_array($dn, $keyValues))
          $keyValues[] = $dn;
      }
      else {
        $values = $object -> getValue($attrValue);
        if (is_array($values))
          foreach ($values as $keyValue)
            if (!in_array($keyValue, $keyValues))
              $keyValues[] = $keyValue;
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
  public function listObjectsInRelation($object, $attr, $objectType, $attrValues='dn') {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelation_05','listObjectsInRelation');
      return;
    }
    $keyValues = self :: getObjectKeyValueInRelation($object, $objectType, $attrValues);
    if (!empty($keyValues)) {
      $keyValuesFilters = array();
      foreach($keyValues as $keyValue) {
        $keyValuesFilters[] = Net_LDAP2_Filter::create($attr,'equals',$keyValue);
      }
      $filter = LSldap::combineFilters('or', $keyValuesFilters);
      return $this -> listObjects(
        $filter,
        LSsession :: getRootDn(),
        array(
          'scope' => 'sub',
          'recursive' => true,
          'withoutCache'=>true,
          'onlyAccessible' => false
        )
      );
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
      LSerror :: addErrorCode('LSrelation_05','addOneObjectInRelation');
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
      LSerror :: addErrorCode('LSrelation_05','deleteOneObjectInRelation');
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
        if (!$attrValues)
          $attrValues = array($attrValue);
        $keyValues = self :: getObjectKeyValueInRelation($object, $objectType, $attrValues);
        $values = ensureIsArray($this -> attrs[$attr] -> getValue());
        if ($values) {
          $updateData = array();
          foreach($values as $value) {
            if (!in_array($value, $keyValues)) {
              $updateData[] = $value;
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
  public function renameOneObjectInRelation($object, $oldValues, $attr, $objectType, $attrValue='dn') {
    if ((!$attr)||(!$objectType)) {
      LSerror :: addErrorCode('LSrelation_05','renameOneObjectInRelation');
      return;
    }
    if (!($object instanceof $objectType))
      return;
    if (!($this -> attrs[$attr] instanceof LSattribute))
      return;
    $values = ensureIsArray($this -> attrs[$attr] -> getValue());
    if (!$values)
      return;
    $updateData = array();
    $oldValues = ensureIsArray($oldValues);
    foreach($values as $value) {
      if (!in_array($value, $oldValues)) {
        $updateData[] = $value;
      }
      else {
        if ($attrValue == 'dn') {
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
      LSerror :: addErrorCode('LSrelation_05','updateObjectsInRelation');
      return;
    }
    if (!$attrValues)
      $attrValues = array($attrValue);
    $currentDns = array();
    $currentObjects = $this -> listObjectsInRelation($object, $attr, $objectType, $attrValues);
    if(!is_array($currentObjects))
      return;
    for ($i=0; $i<count($currentObjects); $i++) {
      $currentDns[] = $currentObjects[$i] -> getDn();
    }
    self :: log_trace("updateObjectsInRelation($object, $attr, $objectType, $attrValue): current DNs=".varDump($currentDns));
    $dontTouch = array_intersect($listDns, $currentDns);
    self :: log_trace("updateObjectsInRelation($object, $attr, $objectType, $attrValue): dontTouch=".varDump($dontTouch));

    for($i=0; $i<count($currentObjects); $i++) {
      if (in_array($currentObjects[$i] -> getDn(), $dontTouch)) continue;
      if (!$currentObjects[$i] -> deleteOneObjectInRelation($object, $attr, $objectType, $attrValue, $canEditFunction, $attrValues)) {
        return;
      }
    }

    $type = $this -> getType();
    foreach($listDns as $dn) {
      if (in_array($dn, $dontTouch)) continue;
      $obj = new $type();
      if ($obj -> loadData($dn)) {
        if (!$obj -> addOneObjectInRelation($object, $attr, $objectType, $attrValue, $canEditFunction)) {
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
    self :: log_debug(strval($this)." -> fireEvent($event)");

    // Object event
    $return = $this -> fireObjectEvent($event);

    // Config
    $funcs = $this -> getConfig($event);
    if($funcs) {
      if (!is_array($funcs))
        $funcs = array($this -> config[$event]);
      foreach($funcs as $func) {
        if(function_exists($func)) {
          self :: log_debug(strval($this)." -> fireEvent($event): run ".format_callable($func));
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
                self :: log_debug(strval($this)." -> fireEvent($event): run ".format_callable(array($obj, $e['fct'])));
                call_user_func_array(array($obj,$e['fct']),array(&$e['param']));
              }
              catch(Exception $er) {
                self :: log_exception($er, strval($this)." -> fireEvent($event): exception occured running ".format_callable(array($obj, $e['fct'])));
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
              self :: log_debug(strval($this)." -> fireEvent($event): run ".format_callable($e['fct']));
              call_user_func_array($e['fct'],array(&$e['param']));
            }
            catch(Exception $er) {
              self :: log_exception($er, strval($this)." -> fireEvent($event): exception occured running ".format_callable($e['fct']));
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
            self :: log_debug(strval($this)." -> fireEvent($event): run ".format_callable(array($e['obj'], $e['meth'])));
            call_user_func_array(array($e['obj'], $e['meth']),array(&$e['param']));
          }
          catch(Exception $er) {
            self :: log_exception($er, strval($this)." -> fireEvent($event): exception occured running ".format_callable(array($e['obj'], $e['meth'])));
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
      if (isset($this -> cache['subDnName']) && $this -> cache['subDnName']) {
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
    elseif ($key=='type') {
      return $this -> getType();
    }
    // Unknown key, log warning
    self :: log_warning("__get($key): invalid property requested\n".LSlog :: get_debug_backtrace_context());
  }


  /**
   * Check if object type have a specified attribute
   *
   * @param[in] $attr_name  string The attribute name
   *
   * @teval boolean True if object type have a attribute of this name, False otherwise
   */
  public static function hasAttr($attr_name) {
    return array_key_exists($attr_name, LSconfig :: get('LSobjects.'.get_called_class().'.attrs', array()));
  }

  /**
   * List IOformats of this object type
   *
   * @retval mixed Array of valid IOformats of this object type
   **/
  public function listValidIOformats() {
    $ret = array();
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
   * @param[] $param      The configuration parameter
   * @param[] $default    The default value (default : null)
   * @param[] $cast       Cast resulting value in specific type (default : disabled)
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
    return $this -> toString(true);
  }

 /**
  * Return string representation of the object
  *
  * @param[in] $enclosed boolean If true, result will be enclosed using "< >" (optional, default: true)
  *
  * @retval string The string representation of the LdapObject
  */
  public function toString($enclosed=true) {
    $str = "LdapObject ".$this -> getType();
    if ($this -> dn)
       $str .= " ".$this -> dn;
    else {
      $str .= " (new)";
      $rdn_attr = $this -> getConfig('rdn');
      if( $rdn_attr && isset($this -> attrs[$rdn_attr]) ) {
        $rdn_val = $this -> attrs[$rdn_attr] -> getUpdateData();
        if (!empty($rdn_val))
          $str .= " $rdn_attr=".$rdn_val[0];
      }
    }
    if ($enclosed)
      return "<$str>";
    return $str;
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
   * @retval boolean True on success, false otherwise
   **/
  public static function cli_show($command_args) {
    $objType = null;
    $dn = null;
    $raw_values = false;
    $json = false;
    $pretty = false;
    foreach ($command_args as $arg) {
      if ($arg == '-r' || $arg == '--raw-values')
        $raw_values = true;
      elseif ($arg == '-j' || $arg == '--json')
        $json = true;
      elseif ($arg == '-p' || $arg == '--pretty')
        $pretty = true;
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
      self :: log_fatal("Fail to load object $dn data from LDAP");
      return false;
    }

    if ($json)
      $obj -> _cli_json_export($raw_values, $pretty);
    else
      $obj -> _cli_show($raw_values);
    return true;
  }

  /**
   * Args autocompleter for CLI show search
   *
   * @param[in] $command_args array List of already typed words of the command
   * @param[in] $comp_word_num int The command word number to autocomplete
   * @param[in] $comp_word string The command word to autocomplete
   * @param[in] $opts array List of global available options
   *
   * @retval array List of available options for the word to autocomplete
   **/
  public static function cli_show_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
    $opts = array_merge($opts, array ('-r', '--raw-values', '-j', '--json', '-p', '--pretty'));

    // Handle positional args
    $objType = null;
    $objType_arg_num = null;
    $dn = null;
    $dn_arg_num = null;
    for ($i=0; $i < count($command_args); $i++) {
      if (!in_array($command_args[$i], $opts)) {
        // If object type not defined
        if (is_null($objType)) {
          // Defined it
          $objType = $command_args[$i];
          LScli :: unquote_word($objType);
          $objType_arg_num = $i;

          // Check object type exists
          $objTypes = LScli :: autocomplete_LSobject_types($objType);

          // Load it if exist and not trying to complete it
          if (in_array($objType, $objTypes) && $i != $comp_word_num) {
            LSsession :: loadLSobject($objType, false);
          }
        }
        elseif (is_null($dn)) {
          $dn = $command_args[$i];
          LScli :: unquote_word($dn);
          $dn_arg_num = $i;
        }
      }
    }

    // If objType not already choiced (or currently autocomplete), add LSobject types to available options
    if (!$objType || $objType_arg_num == $comp_word_num)
      $opts = array_merge($opts, LScli :: autocomplete_LSobject_types($comp_word));

    // If dn not alreay choiced (or currently autocomplete), try autocomplete it
    elseif (!$dn || $dn_arg_num == $comp_word_num)
      $opts = array_merge($opts, LScli :: autocomplete_LSobject_dn($objType, $comp_word));

    return LScli :: autocomplete_opts($opts, $comp_word);
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
          echo $this -> _cli_show_attr($attr_name, $raw_values, "  ");
        }
        echo "\n";
      }
    }
    else {
      foreach ($this -> attrs as $attr_name => $attr) {
        echo $this -> _cli_show_attr($attr_name, $raw_values);
      }
      echo "\n";
    }

    // Show LSrelations
    if (LSsession :: loadLSclass('LSrelation') && is_array($this -> getConfig('LSrelation'))) {
      foreach ($this -> getConfig('LSrelation') as $rel_name => $rel_conf) {
        echo "  - ".(isset($rel_conf['label'])?$rel_conf['label']." (relation $rel_name)":$rel_name)." :\n";
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
          self :: log_error("Fail to load related objects.");
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
   * @retval string The formated attribute message
   **/
  private function _cli_show_attr($attr_name, $raw_values=false, $prefix="") {
    if (!isset($this -> attrs[$attr_name]))
      return;
    $values = ($raw_values?$this -> attrs[$attr_name]->getValue():$this -> attrs[$attr_name]->getDisplayValue());
    return self :: _cli_show_attr_values($attr_name, $values, $prefix);
  }

  /**
   * CLI helper to show the attribute values
   *
   * @param[in] $attr_name string The attribute name
   * @param[in] $values array Attribute values
   * @param[in] $prefix string Prefix for each line displayed (optional, default: no prefix)
   *
   * @retval string The formated attribute values message
   **/
  private function _cli_show_attr_values($attr_name, $values, $prefix="") {
    $return = "$prefix  - ".$this -> attrs[$attr_name]->getLabel()." ($attr_name) :";
    if (empty($values)) {
      return $return." empty\n";
    }
    $values = ensureIsArray($values);

    // Truncate values if too long
    for ($i=0; $i < count($values); $i++)
      if (strlen($values[$i]) > 70)
        $values[$i] = substr($values[$i], 0, 65)."[...]";
    $return .= (count($values) > 1?"\n$prefix    - ":" ");
    $return .= implode("\n$prefix    - ", $values);
    return $return."\n";
  }

  /**
   * CLI helper to show the attributes values
   *
   * @param[in] $attrs_values attrs The attributes values
   * @param[in] $label string|null The label text to show before attributes values
   * @param[in] $prefix string Prefix for each line displayed (optional, default: no prefix)
   *
   * @retval string The formated attributes values message
   **/
  private function _cli_show_attrs_values($attrs_values, $label=null, $prefix="") {
    $return = "";
    if ($label)
      $return .= "$prefix$label\n";
    foreach ($attrs_values as $attr => $values)
      $return .= self :: _cli_show_attr_values($attr, $values, $prefix);
    return $return;
  }


  /**
   * CLI helper to export the object info as JSON
   *
   * @param[in] $raw_values bool Export attributes raw values (instead of display ones)
   * @param[in] $pretty bool Prettify JSON export
   *
   * @retval void
   **/
  private function _cli_json_export($raw_values=false, $pretty=false) {
    $export = array(
      'dn' => $this -> dn,
      'attrs' => array(),
      'relations' => array(),
    );

    // Export attributes values
    foreach (array_keys($this -> attrs) as $attr_name) {
      $export['attrs'][$attr_name] = array(
        'label' => $this -> attrs[$attr_name]->getLabel(),
        'values' => (
          $raw_values?
          $this -> attrs[$attr_name]->getValue():
          $this -> attrs[$attr_name]->getDisplayValue()
        ),
      );
      // If attribute value can't be encoded as JSON, encode it as base64
      if ($export['attrs'][$attr_name]['values'] && json_encode($export['attrs'][$attr_name]['values']) === false ) {
        $base64_values = array();
        foreach($export['attrs'][$attr_name]['values'] as $value)
          $base64_values[] = base64_encode($value);
        $export['attrs'][$attr_name]['values'] = $base64_values;
      }
    }

    // Export LSrelations
    if (LSsession :: loadLSclass('LSrelation') && is_array($this -> getConfig('LSrelation'))) {
      foreach ($this -> getConfig('LSrelation') as $rel_name => $rel_conf) {
        $export['relations'][$rel_name] = array(
          'label' => (isset($rel_conf['label'])?$rel_conf['label']:null),
          'relatedObjects' => array(),
        );
        $relation = new LSrelation($this, $rel_name);
        $list = $relation -> listRelatedObjects();
        if (is_array($list)) {
          foreach($list as $o) {
            $export['relations'][$rel_name]['relatedObjects'][$o -> getDn()] = array(
              'name' => $o -> getDisplayName(NULL,true),
              'type' => get_class($o),
            );
          }
        }
        else {
          self :: log_error("Fail to load related objects.");
        }
      }
    }
    $output = json_encode($export, ($pretty?JSON_PRETTY_PRINT:0));
    if ($output === false)
      self :: log_error("Fail to encode data as JSON");
    else
      echo $output;
  }

    /**
     * CLI remove command
     *
     * @param[in] $command_args array Command arguments :
     *   - Positional arguments :
     *     - LSobject type
     *     - object DN
     *   - Optional arguments :
     *     - -N|--no-confirm : Do not ask for confirmation
     *
     * @retval boolean True on success, false otherwise
     **/
    public static function cli_remove($command_args) {
      $objType = null;
      $dn = null;
      $confirm = true;
      foreach ($command_args as $arg) {
        if ($arg == '-N' || $arg == '--no-confirm')
          $confirm = false;
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
        self :: log_fatal("Fail to load object $dn data from LDAP");
        return false;
      }

      if ($confirm) {
        $obj -> _cli_show($raw_values);
        // Sure ?
        if (!LScli :: confirm("\nAre you sure you want to delete this object?"))
          return True;
      }

      if ($obj -> remove()) {
        self :: log_info("Object ".$obj->getDn()." removed.");
        return true;
      }
      self :: log_error("Fail to remove object ".$obj->getDn().".");
      return false;
    }

    /**
     * Args autocompleter for CLI remove command
     *
     * @param[in] $command_args array List of already typed words of the command
     * @param[in] $comp_word_num int The command word number to autocomplete
     * @param[in] $comp_word string The command word to autocomplete
     * @param[in] $opts array List of global available options
     *
     * @retval array List of available options for the word to autocomplete
     **/
    public static function cli_remove_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
      $opts = array_merge($opts, array ('-N', '--no-confirm'));

      // Handle positional args
      $objType = null;
      $objType_arg_num = null;
      $dn = null;
      $dn_arg_num = null;
      for ($i=0; $i < count($command_args); $i++) {
        if (!in_array($command_args[$i], $opts)) {
          // If object type not defined
          if (is_null($objType)) {
            // Defined it
            $objType = $command_args[$i];
            LScli :: unquote_word($objType);
            $objType_arg_num = $i;

            // Check object type exists
            $objTypes = LScli :: autocomplete_LSobject_types($objType);

            // Load it if exist and not trying to complete it
            if (in_array($objType, $objTypes) && $i != $comp_word_num) {
              LSsession :: loadLSobject($objType, false);
            }
          }
          elseif (is_null($dn)) {
            $dn = $command_args[$i];
            LScli :: unquote_word($dn);
            $dn_arg_num = $i;
          }
        }
      }

      // If objType not already choiced (or currently autocomplete), add LSobject types to available options
      if (!$objType || $objType_arg_num == $comp_word_num)
        $opts = array_merge($opts, LScli :: autocomplete_LSobject_types($comp_word));

      // If dn not alreay choiced (or currently autocomplete), try autocomplete it
      elseif (!$dn || $dn_arg_num == $comp_word_num)
        $opts = array_merge($opts, LScli :: autocomplete_LSobject_dn($objType, $comp_word));

      return LScli :: autocomplete_opts($opts, $comp_word);
    }

    /**
     * CLI helper to parse attribute values given via command argument in format :
     *
     *    attr=value1|value2
     *
     * @param[in] $obj LSldapObject The LDAP object
     * @param[in] &$attrs_values array The reference of the array that store attributes values
     * @param[in] $command_arg string The command argument to parse
     * @param[in] $delimiter string The delimiter of multiple-value attribute (optional, default: '|')
     *
     * @retval boolean True if attribute values successfully parsed, False otherwise
     **/
    function _cli_parse_attr_values($obj, &$attrs_values, $command_arg, $delimiter='|') {
      $arg_parts = explode('=', $command_arg);
      if (count($arg_parts) < 2)
        LScli :: usage("Invalid parameter '$command_arg'.");
      $attr = $arg_parts[0];
      if (!isset($obj -> attrs[$attr]))
        LScli :: usage("Invalid parameter '$command_arg': ".$obj->type_name." object as no attribute '$attr'.");

      // Split an check values
      $values = array();
      foreach (explode($delimiter, implode('=', array_slice($arg_parts, 1))) as $value)
        if (!empty($value))
          $values[] = $value;
      if (!array_key_exists($attr, $attrs_values))
        $attrs_values[$attr] = $values;
      else
        $attrs_values[$attr] = array_merge($attrs_values[$attr], $values);
      return true;
    }

    /**
     * CLI create command
     *
     * @param[in] $command_args array Command arguments :
     *   - Positional arguments :
     *     - LSobject type
     *     - object DN
     *     - attributes values in format attr=value1|value2
     *   - Optional arguments :
     *     - -D|--delimiter : delimiter for multiple values attributes (default: "|")
     *     - -N|--no-confirm : Do not ask for confirmation
     *     - -j|--just-try : Just-try mode = validate provided data but do not really
     *                       create LDAP object data
     *
     *   Note: for multiple-values attributes, you also could specify attribute and value
     *   multiple time, for instance : attr1=value1 attr1=value2
     *
     * @retval boolean True on success, false otherwise
     **/
    public static function cli_create($command_args) {
      $objType = null;
      $delimiter = "|";
      $confirm = true;
      $just_try = false;
      $attrs_values = array();
      for ($i=0; $i < count($command_args); $i++) {
        switch ($command_args[$i]) {
          case '-d':
          case '--delimiter':
            $delimiter = $command_args[++$i];
            if ($delimiter == '=')
              LScli :: usage("Invalid delimiter: you can't use '=' as delimiter.");
            break;
          case '-N':
          case '--no-confirm':
            $confirm = false;
            break;
          case '-j':
          case '--just-try':
            $just_try = true;
            break;
          default:
            if (is_null($objType)) {
              $objType = $command_args[$i];
              if (!LSsession :: loadLSobject($objType))
                return false;
              $obj = new $objType();
            }
            else {
              // Change on an attribute
              $obj -> _cli_parse_attr_values($obj, $attrs_values, $command_args[$i], $delimiter);
            }
        }
      }

      if (is_null($objType) || empty($attrs_values))
        LScli :: usage('You must provide LSobject type, DN and at least one change.');

      // Instanciate a create LSform (in API mode)
      $form = $obj -> getForm('create', null, true);

      // Check all changed attributes are in modify form and are'nn freezed
      foreach ($attrs_values as $attr => $value) {
        if (!$form -> hasElement($attr))
          LScli :: usage("Change on attribute '$attr' is not possible (attribute not in create form).");
        if ($form -> isFreeze($attr))
          LScli :: usage("Change on attribute '$attr' is not possible (attribute freezed in create form).");
      }

      // Set form data from inputed data, validate form (only for present data) and validate data (just-validation)
      if ($form -> setPostData($attrs_values, true) && $form -> validate(true) && $obj -> updateData('create', true)) {
        // Data validated, get user confirmation
        if ($confirm) {
          echo $obj -> _cli_show(false);
          // Sure ?
          if (!LScli :: confirm("Are you sure you want to create an $objType with this attributes's values?"))
            return True;
        }
        // Just-try mode: stop
        if ($just_try) {
          self :: log_info($obj -> getDn().": Just-try mode : provided information validated but object not updated.");
          return True;
        }
        // Create object in LDAP
        if ($obj -> updateData('create')) {
          self :: log_info($obj -> getDn().": Object created.");
          return True;
        }
      }
      // An error occured: show/log it
      $error_msg = "Validation errors occured on provided information:";
      $errors = $form->getErrors();
      if (is_array($errors) && !empty($errors))
        $error_msg .= "\n".$obj -> _cli_show_attrs_values($errors, "");
      else
        $error_msg .= " unknown error";
      LSlog :: error($error_msg);
      return false;
    }

    /**
     * Args autocompleter for CLI create command
     *
     * @param[in] $command_args array List of already typed words of the command
     * @param[in] $comp_word_num int The command word number to autocomplete
     * @param[in] $comp_word string The command word to autocomplete
     * @param[in] $opts array List of global available options
     *
     * @retval array List of available options for the word to autocomplete
     **/
    public static function cli_create_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
      $opts = array_merge($opts, array ('-j', '--just-try', '-D', '--delimiter', '-N', '--no-confirm'));

      // Handle positional args
      $objType = null;
      $objType_arg_num = null;
      for ($i=0; $i < count($command_args); $i++) {
        if (!in_array($command_args[$i], $opts)) {
          // If object type not defined
          if (is_null($objType)) {
            // Defined it
            $objType = $command_args[$i];
            LScli :: unquote_word($objType);
            $objType_arg_num = $i;

            // Check object type exists
            $objTypes = LScli :: autocomplete_LSobject_types($objType);

            // Load it if exist and not trying to complete it
            if (in_array($objType, $objTypes) && $i != $comp_word_num) {
              LSsession :: loadLSobject($objType, false);
            }
          }
        }
        else {
          // All args accept option, increase $i
          $i++;
        }
      }
      self :: log_debug("obj type :'$objType' (#$objType_arg_num)");

      // Handle completion of args value
      self :: log_debug("Last complete word = '".$command_args[$comp_word_num-1]."'");
      switch ($command_args[$comp_word_num-1]) {
        case '-D':
        case '--delimiter':
          return array('|', ';');
      }

      // If objType not already choiced (or currently autocomplete), add LSobject types to available options
      if (!$objType || $objType_arg_num == $comp_word_num)
        $opts = array_merge($opts, LScli :: autocomplete_LSobject_types($comp_word));

      // Otherwise, autocomplete on attribute=value
      elseif ($objType && class_exists($objType)) {
        LScli :: need_ldap_con();
        $obj = new $objType();
        $form = $obj -> getForm('create', null, true);  // Instanciate create form in API mode
        $form -> autocomplete_attrs_values($opts, $comp_word);
      }

      return LScli :: autocomplete_opts($opts, $comp_word);
    }

    /**
     * CLI modify command
     *
     * @param[in] $command_args array Command arguments :
     *   - Positional arguments :
     *     - LSobject type
     *     - object DN
     *     - changes to made on object in format attr=value1|value2
     *   - Optional arguments :
     *     - -D|--delimiter : delimiter for multiple values attributes (default: "|")
     *     - -N|--no-confirm : Do not ask for confirmation
     *     - -j|--just-try : Just-try mode = validate changes but do not really update LDAP object data
     *
     *   Note: for multiple-values attributes, you also could specify attribute and value
     *   multiple time, for instance : attr1=value1 attr1=value2
     *
     * @retval boolean True on success, false otherwise
     **/
    public static function cli_modify($command_args) {
      $objType = null;
      $dn = null;
      $delimiter = "|";
      $confirm = true;
      $just_try = false;
      $changes = array();
      for ($i=0; $i < count($command_args); $i++) {
        switch ($command_args[$i]) {
          case '-D':
          case '--delimiter':
            $delimiter = $command_args[++$i];
            if ($delimiter == '=')
              LScli :: usage("Invalid delimiter: you can't use '=' as delimiter.");
            break;
          case '-N':
          case '--no-confirm':
            $confirm = false;
            break;
          case '-j':
          case '--just-try':
            $just_try = true;
            break;
          default:
            if (is_null($objType)) {
              $objType = $command_args[$i];
              if (!LSsession :: loadLSobject($objType))
                return false;
              $obj = new $objType();
            }
            elseif (is_null($dn)) {
              $dn = $command_args[$i];
            }
            else {
              // Change on an attribute
              $obj -> _cli_parse_attr_values($obj, $changes, $command_args[$i], $delimiter);
            }
        }
      }

      if (is_null($objType) || is_null($dn) || empty($changes))
        LScli :: usage('You must provide LSobject type, DN and at least one change.');

      if (!$obj->loadData($dn)) {
        self :: log_fatal("Fail to load object $dn data from LDAP");
        return false;
      }

      if ($confirm) {
        echo $obj -> _cli_show_attrs_values($changes, "Attributes's values changes:");
        // Sure ?
        if (!LScli :: confirm("Are you sure you want to change attributes's values of this object as specified?"))
          return True;
      }

      // Instanciate a modify LSform (in API mode)
      $form = $obj -> getForm('modify', null, true);

      // Check all changed attributes are in modify form and are'nn freezed
      foreach ($changes as $attr => $value) {
        if (!$form -> hasElement($attr))
          LScli :: usage("Change on attribute '$attr' is not possible (attribute not in modify form).");
        if ($form -> isFreeze($attr))
          LScli :: usage("Change on attribute '$attr' is not possible (attribute freezed in modify form).");
      }

      // Set form data from inputed data, validate form (only for present data) and validate data
      if (!$form -> setPostData($changes, true) || !$form -> validate(true) || !$obj -> updateData('modify', $just_try)) {
        $error_msg = "Validation errors occured on your changes:";
        $errors = $form->getErrors();
        if (is_array($errors) && !empty($errors))
          $error_msg .= "\n".$obj -> _cli_show_attrs_values($errors, "");
        else
          $error_msg .= " unknown error";
        LSlog :: error($error_msg);
        return false;
      }
      elseif ($just_try) {
        self :: log_info("$dn: Just-try mode : changes validated but object not updated.");
      }
      else {
        self :: log_info("$dn: Object updated.");
      }
      return True;
    }

    /**
     * Args autocompleter for CLI modify command
     *
     * @param[in] $command_args array List of already typed words of the command
     * @param[in] $comp_word_num int The command word number to autocomplete
     * @param[in] $comp_word string The command word to autocomplete
     * @param[in] $opts array List of global available options
     *
     * @retval array List of available options for the word to autocomplete
     **/
    public static function cli_modify_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
      $opts = array_merge($opts, array ('-j', '--just-try', '-D', '--delimiter', '-N', '--no-confirm'));

      // Handle positional args
      $objType = null;
      $objType_arg_num = null;
      $dn = null;
      $dn_arg_num = null;
      for ($i=0; $i < count($command_args); $i++) {
        if (!in_array($command_args[$i], $opts)) {
          // If object type not defined
          if (is_null($objType)) {
            // Defined it
            $objType = $command_args[$i];
            LScli :: unquote_word($objType);
            $objType_arg_num = $i;

            // Check object type exists
            $objTypes = LScli :: autocomplete_LSobject_types($objType);

            // Load it if exist and not trying to complete it
            if (in_array($objType, $objTypes) && $i != $comp_word_num) {
              LSsession :: loadLSobject($objType, false);
            }
          }
          elseif (is_null($dn)) {
            $dn = $command_args[$i];
            LScli :: unquote_word($dn);
            $dn_arg_num = $i;
          }
        }
        else {
          // All args accept option, increase $i
          $i++;
        }
      }
      self :: log_debug("obj type :'$objType' (#$objType_arg_num) / dn :'$dn' (#$dn_arg_num)");

      // Handle completion of args value
      self :: log_debug("Last complete word = '".$command_args[$comp_word_num-1]."'");
      switch ($command_args[$comp_word_num-1]) {
        case '-D':
        case '--delimiter':
          return array('|', ';');
      }

      // If objType not already choiced (or currently autocomplete), add LSobject types to available options
      if (!$objType || $objType_arg_num == $comp_word_num)
        $opts = array_merge($opts, LScli :: autocomplete_LSobject_types($comp_word));

      // If dn not already choiced (or currently autocomplete), try autocomplete it
      elseif (!$dn || $dn_arg_num == $comp_word_num)
        $opts = array_merge($opts, LScli :: autocomplete_LSobject_dn($objType, $comp_word));

      // Otherwise, autocomplete on attribute=value
      elseif ($objType && class_exists($objType) && $dn) {
        LScli :: need_ldap_con();
        $obj = new $objType();
        if (!$obj->loadData($dn)) {
          self :: log_error("Fail to load object $dn data from LDAP");
        }
        else {
          $form = $obj -> getForm('modify', null, True);  // Instanciate modify form in API mode
          $form -> autocomplete_attrs_values($opts, $comp_word);
        }
      }

      return LScli :: autocomplete_opts($opts, $comp_word);
    }

    /**
     * CLI relation command
     *
     * @param[in] $command_args array Command arguments :
     *   - Positional arguments :
     *     - LSobject type
     *     - object DN
     *     - relation ID
     *   - Optional arguments :
     *     - -a|--add : Add related object (specified by DN)
     *     - -r|--remove : Remove related object (specified by DN)
     *
     * @retval boolean True on success, false otherwise
     **/
    public static function cli_relation($command_args) {
      $objType = null;
      $dn = null;
      $relName = null;
      $add = array();
      $remove = array();
      for ($i=0; $i < count($command_args); $i++) {
        if ($command_args[$i] == '-a' || $command_args[$i] == '--add')
          $add[] = $command_args[++$i];
        elseif ($command_args[$i] == '-r' || $command_args[$i] == '--remove')
          $remove[] = $command_args[++$i];
        elseif (is_null($objType)) {
          $objType = $command_args[$i];
        }
        elseif (is_null($dn)) {
          $dn = $command_args[$i];
        }
        elseif (is_null($relName)) {
          $relName = $command_args[$i];
        }
        else
          LScli :: usage("Invalid ".$command_args[$i]." parameter.");
      }

      if (is_null($objType) || is_null($dn) || is_null($relName))
        LScli :: usage('You must provide LSobject type, DN and relation ID.');

      if (empty($add) && empty($remove))
        LScli :: usage('You must provide at least one DN of related object to add/remove.');

      if (!LSsession :: loadLSobject($objType))
        return false;

      $obj = new $objType();
      if (!$obj->loadData($dn)) {
        self :: log_fatal("Fail to load object $dn data from LDAP");
        return false;
      }

      if (!LSsession :: loadLSclass('LSrelation')) {
        self :: log_fatal("Fail to load LSrelation class.");
        return false;
      }

      if (!is_array($obj -> getConfig("LSrelation.$relName"))) {
        self :: log_fatal("LSobject $objType have no relation '$relName'.");
        return false;
      }

      $relation = new LSrelation($obj, $relName);

      // List current related objects
      $list = $relation -> listRelatedObjects();
      $listDns = array();
      if (is_array($list)) {
        foreach($list as $o) {
          $listDns[] = $o -> getDn();
        }
      }
      self :: log_debug("Current related object(s) :".varDump($listDns));

      // Keep a copy of initial related objects list
      $initialListDns = $listDns;

      // Handle add
      $relatedLSobject = $obj -> getConfig("LSrelation.$relName.LSobject");
      $search = new LSsearch(
        $relatedLSobject,
        "LSldapObject.cli_relation.$objType.$relName",
        array(
          'scope' => 'base',
        )
      );
      foreach ($add as $dn) {
        // Check if DN is already in relation
        if (in_array($dn, $listDns)) {
          self :: log_debug("LSobject $relatedLSobject $dn is already in relation with ".$obj -> getDn().".");
          continue;
        }

        // Check DN refer to a related object
        $search -> setParam('basedn', $dn);
        $search -> run(false);
        $result = $search -> listObjectsDn();
        if (!is_array($result) || count($result) != 1) {
          self :: log_error("No $relatedLSobject found for DN $dn");
          return false;
        }
        $listDns[] = $dn;
      }

      // Handle remove
      foreach ($remove as $dn) {
        $found = false;
        while(true) {
          $key = array_search($dn, $listDns);
          if ($key === false) break;
          $found = true;
          unset($listDns[$key]);
        }
        if (!$found)
          self :: log_debug("LSobject $relatedLSobject $dn is not in relation with ".$obj -> getDn().".");
      }

      if ($initialListDns == $listDns) {
        self :: log_info('No changes done.');
        return True;
      }

      self :: log_debug("New related object(s) list: ".varDump($listDns));
      if ($relation -> updateRelations($listDns)) {
        self :: log_info('Objects in relation updated.');
        return True;
      }
      self :: log_error("Fail to update objects in relation");
      return False;
    }

    /**
     * Args autocompleter for CLI relation command
     *
     * @param[in] $command_args array List of already typed words of the command
     * @param[in] $comp_word_num int The command word number to autocomplete
     * @param[in] $comp_word string The command word to autocomplete
     * @param[in] $opts array List of global available options
     *
     * @retval array List of available options for the word to autocomplete
     **/
    public static function cli_relation_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
      $opts = array_merge($opts, array ('-a', '--add', '-r', '--remove'));

      // Handle positional args
      $objType = null;
      $objType_arg_num = null;
      $dn = null;
      $dn_arg_num = null;
      $relation_id = null;
      $relation_id_arg_num = null;
      for ($i=0; $i < count($command_args); $i++) {
        if (!in_array($command_args[$i], $opts)) {
          // If object type not defined
          if (is_null($objType)) {
            // Defined it
            $objType = $command_args[$i];
            LScli :: unquote_word($objType);
            $objType_arg_num = $i;

            // Check object type exists
            $objTypes = LScli :: autocomplete_LSobject_types($objType);

            // Load it if exist and not trying to complete it
            if (in_array($objType, $objTypes) && $i != $comp_word_num) {
              LSsession :: loadLSobject($objType, false);
            }
          }
          elseif (is_null($dn)) {
            $dn = $command_args[$i];
            LScli :: unquote_word($dn);
            $dn_arg_num = $i;
          }
          elseif (is_null($relation_id)) {
            $relation_id = $command_args[$i];
            LScli :: unquote_word($relation_id);
            $relation_id_arg_num = $i;
          }
        }
        else {
          // All args accept option, increase $i
          $i++;
        }
      }
      self :: log_debug("obj type :'$objType' (#$objType_arg_num) / dn :'$dn' (#$dn_arg_num) / relation_id:'$relation_id' (#$relation_id_arg_num)");

      // Handle completion of args value
      self :: log_debug("Last complete word = '".$command_args[$comp_word_num-1]."'");
      switch ($command_args[$comp_word_num-1]) {
        case '-a':
        case '--add':
          if (!$objType || !$dn || !$relation_id)
            return array();
          $related_obj_type = LSconfig :: get("LSobjects.$objType.LSrelation.$relation_id.LSobject");
          self :: log_debug("Related obj type='$related_obj_type'");
          if ($related_obj_type)
            return LScli :: autocomplete_LSobject_dn($related_obj_type, $comp_word);
          return array();
        case '-r':
        case '-remove':
          if (!$objType || !$dn || !$relation_id)
            return array();
          if (!LSsession :: loadLSobject($objType)) {
            self :: log_error("Invalid object type $objType");
            return array();
          }

          LScli :: need_ldap_con();

          $obj = new $objType();
          if (!$obj->loadData($dn)) {
            self :: log_error("Fail to load object $dn data from LDAP");
            return array();
          }

          if (!LSsession :: loadLSclass('LSrelation')) {
            self :: log_error("Fail to load LSrelation class.");
            return array();
          }

          if (!is_array($obj -> getConfig("LSrelation.$relation_id"))) {
            self :: log_error("LSobject $objType have no relation '$relation_id'.");
            return array();
          }

          $relation = new LSrelation($obj, $relation_id);

          // List current related objects
          $list = $relation -> listRelatedObjects();
          $listDns = array();
          if (is_array($list)) {
            foreach($list as $o) {
              $listDns[] = $o -> getDn();
            }
          }
          return LScli :: autocomplete_opts($listDns, $comp_word, false);
      }

      // If objType not already choiced (or currently autocomplete), add LSobject types to available options
      if (!$objType || $objType_arg_num == $comp_word_num)
        $opts = array_merge($opts, LScli :: autocomplete_LSobject_types($comp_word));

      // If dn not already choiced (or currently autocomplete), try autocomplete it
      elseif (!$dn || $dn_arg_num == $comp_word_num)
        $opts = array_merge($opts, LScli :: autocomplete_LSobject_dn($objType, $comp_word));

      // If relation_id not already choiced (or currently autocomplete), try autocomplete it
      elseif (!$relation_id || $relation_id_arg_num == $comp_word_num) {
        $relations = LSconfig :: get("LSobjects.$objType.LSrelation");
        if (is_array($relations))
          $opts = array_merge($opts, array_keys($relations));
      }

      return LScli :: autocomplete_opts($opts, $comp_word);
    }

    /*
     * Check validity of a LSobject type name
     *
     * A LSobjet type name must only contain letter, digits or dash or
     * underscore.
     *
     * @param[in] $name string The LSobject type name to check
     *
     * @retval boolean True is type name is valid, False otherwise
     */
    public static function isValidTypeName($name) {
      if (preg_match('/^[a-zA-Z0-9\_\-]+$/', $name))
        return True;
      self :: log_warning("isValidTypeName($name): Invalid LSobject type name !");
      return False;
    }
}

/**
 * Error Codes
 **/
LSerror :: defineError('LSldapObject_01',
___("LSldapObject : Object type unknown.")
);
LSerror :: defineError('LSldapObject_02',
___("LSldapObject : Update form is not defined for the object %{obj}.")
);
LSerror :: defineError('LSldapObject_03',
___("LSldapObject : No form exists for the object %{obj}.")
);
LSerror :: defineError('LSldapObject_04',
___("LSldapObject : The function %{func} to validate the attribute %{attr} the object %{obj} is unknow.")
);
LSerror :: defineError('LSldapObject_05',
___("LSldapObject : Configuration data are missing to validate the attribute %{attr} of the object %{obj}.")
);

LSerror :: defineError('LSldapObject_06',
___("LSldapObject : The function %{func} to be executed on the object event %{event} doesn't exist.")
);
LSerror :: defineError('LSldapObject_07',
___("LSldapObject : The %{func} execution on the object event %{event} failed.")
);

LSerror :: defineError('LSldapObject_08',
___("LSldapObject : Class %{class}, which method %{meth} to be executed on the object event %{event}, doesn't exist.")
);
LSerror :: defineError('LSldapObject_09',
___("LSldapObject : Method %{meth} within %{class} class to be executed on object event %{event}, doesn't exist.")
);
LSerror :: defineError('LSldapObject_10',
___("LSldapObject : Error during execute %{meth} method within %{class} class, to be executed on object event %{event}.")
);

LSerror :: defineError('LSldapObject_11',
___("LSldapObject : Some configuration data of the object type %{obj} are missing to generate the DN of the new object.")
);
LSerror :: defineError('LSldapObject_12',
___("LSldapObject : The attibute %{attr} of the object is not yet defined. Can't generate DN.")
);
LSerror :: defineError('LSldapObject_13',
___("LSldapObject : Without DN, the object could not be changed.")
);
LSerror :: defineError('LSldapObject_14',
___("LSldapObject : The attribute %{attr_depend} depending on the attribute %{attr} doesn't exist.")
);
LSerror :: defineError('LSldapObject_15',
___("LSldapObject : Error during deleting the object %{objectname}.")
);

LSerror :: defineError('LSldapObject_16',
___("LSldapObject : Error during actions to be executed before renaming the objet.")
);
LSerror :: defineError('LSldapObject_17',
___("LSldapObject : Error during actions to be executed after renaming the objet.")
);

LSerror :: defineError('LSldapObject_18',
___("LSldapObject : Error during actions to be executed before deleting the objet.")
);
LSerror :: defineError('LSldapObject_19',
___("LSldapObject : Error during actions to be executed after deleting the objet.")
);

LSerror :: defineError('LSldapObject_20',
___("LSldapObject : Error during the actions to be executed before creating the object.")
);
LSerror :: defineError('LSldapObject_21',
___("LSldapObject : Error during the actions to be executed after creating the object. It was created anyway.")
);

LSerror :: defineError('LSldapObject_22',
___("LSldapObject : The function %{func} to be executed before creating the object doesn't exist.")
);
LSerror :: defineError('LSldapObject_23',
___("LSldapObject : Error executing the function %{func} to be execute after deleting the object.")
);
LSerror :: defineError('LSldapObject_24',
___("LSldapObject : The function %{func} to be executed after deleting the object doesn't exist.")
);
LSerror :: defineError('LSldapObject_25',
___("LSldapObject : Error executing the function %{func} to be execute after creating the object.")
);

LSerror :: defineError('LSldapObject_26',
___("LSldapObject : %{func} function, to be executed on object event %{event}, doesn't exist.")
);
LSerror :: defineError('LSldapObject_27',
___("LSldapObject : Error during the execution of %{func} function on object event %{event}.")
);

LSerror :: defineError('LSldapObject_28',
___("LSldapObject : %{meth} method, to be executed on object event %{event}, doesn't exist.")
);
LSerror :: defineError('LSldapObject_29',
___("LSldapObject : Error during execution of %{meth} method on object event %{event}.")
);
LSerror :: defineError('LSldapObject_30',
___("LSldapObject : Error during generate LDAP filter for %{LSobject}.")
);

LSerror :: defineError('LSldapObject_31',
___("LSldapObject : Error during execution of the custom action %{customAction} on %{objectname}.")
);

LSerror :: defineError('LSldapObject_32',
___("LSldapObject : Fail to retrieve container DN.")
);
LSerror :: defineError('LSldapObject_33',
___("LSldapObject : The function %{func} to generate container DN is not callable.")
);
LSerror :: defineError('LSldapObject_34',
___("LSldapObject : Error during generating container DN : %{error}")
);
LSerror :: defineError('LSldapObject_35',
___("LSldapObject : An LDAP object with the same DN as generated for this new one already exists. Please verify your configuration.")
);

// LSrelation
LSerror :: defineError('LSrelation_05',
___("LSrelation : Some parameters are missing in the call of methods to handle standard relations (Method : %{meth}).")
);

// LScli
LScli :: add_command(
    'show',
    array('LSldapObject', 'cli_show'),
    'Show an LSobject',
    '[object type] [dn] [-r|--raw-values] [-j|--json [-p|--pretty]]',
    null,
    true,
    array('LSldapObject', 'cli_show_args_autocompleter')
);

LScli :: add_command(
    'create',
    array('LSldapObject', 'cli_create'),
    'Create an LSobject',
    '[object type] [-N|--no-confirm] [-D|--delimiter] attr1=value1|value2 attr2=value3',
    array(
      '   - Positional arguments :',
      '     - LSobject type',
      '     - attributes values in format:',
      '',
      '        attr_name=value1|value2',
      '',
      '       Note: for multiple-values attributes, you also could specify',
      '             attribute and value multiple time, for instance :',
      '',
      '                  attr1=value1 attr1=value2',
      '',
      '   - Optional arguments :',
      '     -j|--just-try         Just-try mode: validate provided informations',
      '                           but do not really create LDAP object. ',
      '     -D|--delimiter        Delimiter for multiple values attributes',
      '                           (default: "|")',
      '     -N|--no-confirm       Do not ask for confirmation',
    ),
    true,
    array('LSldapObject', 'cli_create_args_autocompleter')
);

LScli :: add_command(
    'modify',
    array('LSldapObject', 'cli_modify'),
    'Modify an LSobject',
    '[object type] [dn] [-N|--no-confirm] [-D|--delimiter] attr1=value1|value2 attr2=value3',
    array(
      '   - Positional arguments :',
      '     - LSobject type',
      '     - LSobject DN',
      '     - attributes values to modify in format:',
      '',
      '        attr_name=value1|value2',
      '',
      '       Note: for multiple-values attributes, you also could specify',
      '             attribute and value multiple time, for instance :',
      '',
      '                  attr1=value1 attr1=value2',
      '',
      '   - Optional arguments :',
      '     -j|--just-try         Just-try mode: validate changes but do not',
      '                           really update LDAP object data',
      '     -D|--delimiter        Delimiter for multiple values attributes',
      '                           (default: "|")',
      '     -N|--no-confirm       Do not ask for confirmation',
    ),
    true,
    array('LSldapObject', 'cli_modify_args_autocompleter')
);

LScli :: add_command(
    'remove',
    array('LSldapObject', 'cli_remove'),
    'Remove an LSobject',
    '[object type] [dn] [-N|--no-confirm]',
    null,
    true,
    array('LSldapObject', 'cli_remove_args_autocompleter')
);

LScli :: add_command(
    'relation',
    array('LSldapObject', 'cli_relation'),
    'Manage LSobject related objects',
    '[object type] [dn] [relation ID] [-a|--add DN] [-r|--remove DN]',
    null,
    true,
    array('LSldapObject', 'cli_relation_args_autocompleter')
);
