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

class LSrelation extends LSlog_staticLoggerClass {

  // Reference to the LSldapObject
  private $obj = null;

  // Relation name
  private $name = null;

  // Relation config
  private $config = null;

  /**
   * LSrelation constructor
   *
   * An LSrelation object focus on one type of relations of a specific
   * object. All non-static method are designed to manipulate this type
   * of relation of the object specified at constuct time.
   *
   * @param[in] &$obj LSldapObject  A reference to the LSldapObject
   * @param [in] $relationName string The relation name
   *
   * @retval void
   */
  public function __construct(&$obj, $relationName) {
    $this -> obj =& $obj;
    $this -> name = $relationName;
    $this -> config = $obj -> getConfig("LSrelation.$relationName");
    if (!is_array($this -> config) || !$this -> checkConfig()) {
      $this -> config = null;
      LSerror :: addErrorCode(
        'LSrelations_02',
        array(
          'relation' => $relationName,
          'LSobject' => $obj -> getType()
        )
      );
    }
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
   * Get relation info
   *
   * @param[in] $key string The info name
   *
   * @retval mixed The info value
   */
  public function __get($key) {
    switch ($key) {
      case 'name':
        return $this -> name;
      case 'LSobject':
      case 'linkAttribute':
      case 'linkAttributeValue':
      case 'linkAttributeOtherValues':
      case 'list_function':
      case 'getkeyvalue_function':
      case 'update_function':
      case 'remove_function':
      case 'rename_function':
      case 'canEdit_function':
      case 'canEdit_attribute':
        return $this -> getConfig($key);
      case 'linkAttributeValues':
        $linkAttributeValues = (is_array($this -> linkAttributeOtherValues)?$this -> linkAttributeOtherValues:array());
        if ($this -> linkAttributeValue)
          $linkAttributeValues[] = $this -> linkAttributeValue;
        return $linkAttributeValues;
      case 'relatedEditableAttribute':
        return $this -> getConfig(
          'canEdit_attribute',
          $this -> getConfig('linkAttribute', false)
        );
    }
  }

  /**
   * Check relation configuration
   *
   * @retval boolean True if relation is properly configured, False otherwise
   */
  public function checkConfig() {
    // Check LSobject parameter
    if (!$this -> LSobject) {
      LSerror :: addErrorCode(
        'LSrelations_07',
        array(
          'parameter' => 'LSobject',
          'relation' => $this -> name,
          'LSobject' => $this -> LSobject
        )
      );
      return false;
    }

    // Load related object type
    if (!LSsession :: loadLSobject($this -> LSobject)) {
      LSerror :: addErrorCode(
        'LSrelations_04',
        array(
          'relation' => $this -> name,
          'LSobject' => $this -> LSobject
        )
      );
      return false;
    }

    // Check if it's a simple relation
    if ($this -> linkAttribute && $this -> linkAttributeValue) {
      // Check linkAttribute refered to an existing related object type attribute
      if (!call_user_func(array($this -> LSobject, 'hasAttr'), $this -> linkAttribute)) {
        LSerror :: addErrorCode(
          'LSrelations_08',
          array(
            'parameter' => 'linkAttribute',
            'relation' => $this -> name,
            'LSobject' => $this -> LSobject
          )
        );
        return false;
      }

      // Check linkAttributeValue
      if ($this -> linkAttributeValue != 'dn' && !$this -> obj -> hasAttr($this -> linkAttributeValue)) {
        LSerror :: addErrorCode(
          'LSrelations_08',
          array(
            'parameter' => 'linkAttributeValue',
            'relation' => $this -> name,
            'LSobject' => $this -> LSobject
          )
        );
        return false;
      }

      return true;
    }

    // Advanced relation: check all required parameters refered to related objects
    // methods
    $required_parameters = array(
      'list_function', 'getkeyvalue_function', 'update_function',
      'remove_function', 'rename_function', 'canEdit_function',
    );
    foreach($required_parameters as $p) {
      // Check parameter is defined
      if (!$this -> $p) {
        LSerror :: addErrorCode(
          'LSrelations_07',
          array(
            'parameter' => $p,
            'relation' => $this -> name,
            'LSobject' => $this -> LSobject
          )
        );
        return false;
      }

      // Check parameter refered to an existing related object class method
      if (!method_exists($this -> LSobject, $this -> $p)) {
        LSerror :: addErrorCode(
          'LSrelations_01',
          array(
            'parameter' => $p,
            'function' => $this -> $p,
            'LSobject' => $this -> LSobject,
            'relation' => $this -> name,
          )
        );
        return false;
      }
    }
    return true;
  }

  /**
   * Check a relation exist
   *
   * @param[in] $object_type    string  The object type
   * @param[in] $relation_name  string  The relation name
   *
   * @retval boolean True if relation exist, false otherwise
   */
  public static function exists($object_type, $relation_name) {
    if ($object_type && LSsession :: loadLSobject($object_type)) {
      return is_array(LSconfig :: get("LSobjects.$object_type.LSrelation.$relation_name"));
    }
    return false;
  }

  /**
   * Get relation name
   *
   * @retval string The relation name
   */
  public function getName() {
    return $this -> name;
  }

  /**
   * Check if user can edit this relation
   *
   * @retval boolean True if user can edit this relation, false otherwise
   */
  public function canEdit() {
    return LSsession :: relationCanEdit($this -> obj -> getValue('dn'),$this -> obj -> getType(),$this -> name);
  }

  /**
   * Check if user can create a related object
   *
   * @retval boolean True if user can create a related object, false otherwise
   */
  public function canCreate() {
    return LSsession :: canCreate($this -> LSobject);
  }

  /**
   * List related objects
   *
   * @retval array|false An array of related objects (LSldapObject), of false in case of error
   */
  public function listRelatedObjects() {
    // Load related object type
    if (!LSsession :: loadLSobject($this -> LSobject)) {
      LSerror :: addErrorCode(
        'LSrelations_04',
        array(
          'relation' => $this -> name,
          'LSobject' => $this -> LSobject
        )
      );
      return false;
    }

    // Instanciate related object
    $objRel = new $this -> LSobject();

    // Use list_function
    if ($this -> list_function) {
      if (method_exists($this -> LSobject, $this -> list_function)) {
        return call_user_func_array(
          array($objRel, $this -> list_function),
          array(&$this -> obj)
        );
      }
      LSerror :: addErrorCode(
        'LSrelations_01',
        array(
          'parameter' => 'list_function',
          'function' => $this -> list_function,
          'LSobject' =>  $objRel -> getType(),
          'relation' => $this -> name,
        )
      );
      return False;
    }

    // Use linkAttribute & linkAttributeValue
    if ($this -> linkAttribute && $this -> linkAttributeValue) {
      return $objRel -> listObjectsInRelation(
        $this -> obj,
        $this -> linkAttribute,
        $this -> obj -> getType(),
        $this -> linkAttributeValues
      );
    }

    // Configuration problem
    LSerror :: addErrorCode(
      'LSrelations_05',
      array(
        'relation' => $this -> name,
        'LSobject' => $this -> LSobject,
        'action' => _('listing related objects')
      )
    );
    return false;
  }

  /**
   * Get the value to store to created the relation with $this -> obj
   *
   * @retval array List of value of the link attribute
   */
  public function getRelatedKeyValue() {
    // Load related object type
    if (!LSsession :: loadLSobject($this -> LSobject)) {
      LSerror :: addErrorCode(
        'LSrelations_04',
        array(
          'relation' => $this -> name,
          'LSobject' => $this -> LSobject
        )
      );
      return false;
    }

    // Instanciate related object
    $objRel = new $this -> LSobject();

    // Use getkeyvalue_function
    if ($this -> getkeyvalue_function) {
      if (method_exists($this -> LSobject, $this -> getkeyvalue_function)) {
        return call_user_func_array(
          array($objRel, $this -> getkeyvalue_function),
          array(&$this -> obj)
        );
      }
      LSerror :: addErrorCode(
        'LSrelations_01',
        array(
          'parameter' => 'getkeyvalue_function',
          'function' => $this -> getkeyvalue_function,
          'LSobject' =>  $objRel -> getType(),
          'relation' => $this -> name,
        )
      );
      return false;
    }

    // Use linkAttribute & linkAttributeValue
    if ($this -> linkAttribute && $this -> linkAttributeValue) {
      return $objRel -> getObjectKeyValueInRelation(
        $this -> obj,
        $this -> obj -> getType(),
        $this -> linkAttributeValue
      );
    }

    // Configuration problem
    LSerror :: addErrorCode(
      'LSrelations_05',
      array(
        'relation' => $this -> name,
        'LSobject' => $this -> LSobject,
        'action' => _('getting key value')
      )
    );
    return false;
  }

  /**
   * Check if user can edit the relation with the specified object
   *
   * @param[in] &$objRel LSldapObject A reference to the related object
   *
   * @retval boolean True if user can edit the relation with the specified object, false otherwise
   */
  public function canEditRelationWithObject(&$objRel) {
    if (!$this -> canEdit()) return;

    // Use canEdit_function
    if ($this -> canEdit_function) {
      if (method_exists($objRel, $this -> canEdit_function)) {
        return call_user_func(array($objRel, $this -> canEdit_function));
      }
      LSerror :: addErrorCode(
        'LSrelations_01',
        array(
          'parameter' => 'canEdit_function',
          'function' => $this -> canEdit_function,
          'LSobject' =>  $objRel -> getType(),
          'relation' => $this -> name,
        )
      );
      return False;
    }

    // Use related editable attribute
    if ($this -> relatedEditableAttribute) {
      return LSsession :: canEdit(
        $objRel -> getType(),
        $objRel -> getDn(),
        $this -> relatedEditableAttribute
      );
    }

    // Configuration problem
    LSerror :: addErrorCode(
      'LSrelations_05',
      array(
        'relation' => $this -> name,
        'LSobject' => $this -> LSobject,
        'action' => _('checking right on relation with specific object')
      )
    );
    return false;
  }

  /**
   * Remove relation with the specified object
   *
   * @param[in] &$objRel LSldapObject A reference to the related object
   *
   * @retval boolean True if relation removed, false otherwise
   */
  public function removeRelationWithObject(&$objRel) {
    // Use remove_function
    if ($this -> remove_function) {
      if (method_exists($this -> LSobject, $this -> remove_function)) {
        return call_user_func_array(
          array($objRel, $this -> remove_function),
          array(&$this -> obj)
        );
      }
      LSerror :: addErrorCode(
        'LSrelations_01',
        array(
          'parameter' => 'remove_function',
          'function' => $this -> remove_function,
          'LSobject' =>  $objRel -> getType(),
          'relation' => $this -> name,
        )
      );
      return False;
    }

    // Use linkAttribute & linkAttributeValue
    if ($this -> linkAttribute && $this -> linkAttributeValue) {
      return $objRel -> deleteOneObjectInRelation($this -> obj, $this -> linkAttribute, $this -> obj -> getType(), $this -> linkAttributeValue, null, $this -> linkAttributeValues);
    }

    // Configuration problem
    LSerror :: addErrorCode(
      'LSrelations_05',
      array(
        'relation' => $this -> name,
        'LSobject' => $this -> LSobject,
        'action' => _('removing relation with specific object')
      )
    );
    return false;
  }

  /**
   * Rename relation with the specified object
   *
   * @param[in] &$objRel LSldapObject A reference to the related object
   * @param[in] $oldKeyValue string The old key value of the relation
   *
   * @retval boolean True if relation rename, false otherwise
   */
  public function renameRelationWithObject(&$objRel, $oldKeyValue) {
    // Use rename_function
    if ($this -> rename_function) {
      if (method_exists($objRel,$this -> rename_function)) {
        return call_user_func_array(array($objRel, $this -> rename_function), array(&$this -> obj, $oldKeyValue));
      }
      LSerror :: addErrorCode(
        'LSrelations_01',
        array(
          'parameter' => 'rename_function',
          'function' => $this -> rename_function,
          'LSobject' =>  $objRel -> getType(),
          'relation' => $this -> name,
        )
      );
      return False;
    }

    // Use linkAttribute & linkAttributeValue
    if ($this -> linkAttribute && $this -> linkAttributeValue) {
      return $objRel -> renameOneObjectInRelation(
        $this -> obj,
        $oldKeyValue,
        $this -> linkAttribute,
        $this -> obj -> getType(),
        $this -> linkAttributeValue
      );
    }

    // Configuration problem
    LSerror :: addErrorCode(
      'LSrelations_05',
      array(
        'relation' => $this -> name,
        'LSobject' => $this -> LSobject,
        'action' => _('checking right on relation with specific object')
      )
    );
    return false;
  }

  /**
   * Update relation with the specified DN objects list
   *
   * @param[in] $listDns array Array of DN of the related objects
   *
   * @retval boolean True if relations updated, false otherwise
   */
  public function updateRelations($listDns) {
    // Load related objects type
    if (!LSsession :: loadLSobject($this -> LSobject)) {
      LSerror :: addErrorCode(
        'LSrelations_04',
        array(
          'relation' => $this -> name,
          'LSobject' => $this -> LSobject
        )
      );
      return false;
    }

    // Instanciate related object
    $objRel = new $this -> LSobject();

    // Use update_function
    if ($this -> update_function) {
      if (method_exists($objRel, $this -> update_function)) {
        return call_user_func_array(
          array($objRel, $this -> update_function),
          array(&$this -> obj, $listDns)
        );
      }
      LSerror :: addErrorCode(
        'LSrelations_01',
        array(
          'parameter' => 'update_function',
          'function' => $this -> update_function,
          'LSobject' =>  $objRel -> getType(),
          'relation' => $this -> name,
        )
      );
      return false;
    }

    // Use linkAttribute & linkAttributeValue
    if ($this -> linkAttribute && $this -> linkAttributeValue) {
      return $objRel -> updateObjectsInRelation(
        $this -> obj,
        $listDns,
        $this -> linkAttribute,
        $this -> obj -> getType(),
        $this -> linkAttributeValue,
        null,
        $this -> linkAttributeValues
      );
    }

    // Configuration problem
    LSerror :: addErrorCode(
      'LSrelations_05',
      array(
        'relation' => $this -> name,
        'LSobject' => $this -> LSobject,
        'action' => _('updating relations')
      )
    );
    return false;
  }

 /*
  * Load display dependencies
  *
  * @retval void
  */
  public static function loadDependenciesDisplay() {
    if (LSsession :: loadLSclass('LSselect')) {
      LSselect :: loadDependenciesDisplay();
    }
    LStemplate :: addJSscript('LSrelation.js');
    LStemplate :: addCssFile('LSrelation.css');

    LStemplate :: addJSconfigParam('LSrelation_labels', array(
      'close_confirm_text'      => _('Do you really want to delete'),
      'close_confirm_title'     => _('Warning'),
      'close_confirm_validate'  => _('Delete')
    ));
  }

 /*
  * Load LSrelations information of an object to display it on LSview.
  *
  * LSrelations information are provided to template by usind LSrelations variable.
  *
  * @param[in] $object LSldapObject L'objet dont on cherche les LSrelations
  *
  * @retval void
  */
  public static function displayInLSview($object) {
    if (!($object instanceof LSldapObject))
      return;

    if (!is_array($object -> getConfig('LSrelation')))
      return;

    $LSrelations=array();
    $LSrelations_JSparams=array();
    foreach($object -> getConfig('LSrelation') as $relationName => $relationConf) {
      // Check user access
      if (!LSsession :: relationCanAccess($object -> getValue('dn'), $object->getType(), $relationName)) {
        self :: log_debug("User have no access to relation $relationName of ".$object->getType());
        continue;
      }

      $return=array(
        'label' => __($relationConf['label']),
        'LSobject' => $relationConf['LSobject']
      );

      if (isset($relationConf['emptyText'])) {
        $return['emptyText'] = __($relationConf['emptyText']);
      }
      else {
        $return['emptyText'] = _('No object.');
      }

      $id=rand();
      $return['id']=$id;
      $LSrelations_JSparams[$id]=array(
        'emptyText' => $return['emptyText']
      );
      $_SESSION['LSrelation'][$id] = array(
        'relationName' => $relationName,
        'objectType' => $object -> getType(),
        'objectDn' => $object -> getDn(),
      );
      $relation = new LSrelation($object, $relationName);

      if ($relation -> canEdit()) {
        $return['actions'][] = array(
          'label' => _('Modify'),
          'url' => "object/select/$id",
          'action' => 'modify',
          'class' => 'LSrelation_modify',
          'data' => array(
            'relation-id' => $id,
          )
        );
      }
      if ($relation -> canCreate()) {
         $return['actions'][] = array(
          'label' => _('New'),
          'url' => 'object/'.$relationConf['LSobject'].'/create?LSrelation='.$relationName.'&amp;relatedLSobject='.$object->getType().'&amp;relatedLSobjectDN='.urlencode($object -> getValue('dn')),
          'action' => 'create',
          'class' => null,
        );
      }

      $list = $relation -> listRelatedObjects();
      if (is_array($list)) {
        foreach($list as $o) {
          $return['objectList'][] = array(
            'text' => $o -> getDisplayName(NULL,true),
            'dn' => $o -> getDn(),
            'canEdit' => $relation -> canEditRelationWithObject($o)
          );
        }
      }
      else {
        $return['objectList']=array();
      }
      $LSrelations[]=$return;
    }

    self :: loadDependenciesDisplay();
    LStemplate :: assign('LSrelations',$LSrelations);
    LStemplate :: addJSconfigParam('LSrelations',$LSrelations_JSparams);
  }

  /*
   * AJAX methods
   */

  /**
   * Helper to check AJAX method call and instanciate corresponding
   * LSldapObject and LSrelation objects.
   *
   * @param[in]  &$data array Reference to AJAX returned data array
   * @param[in]  $additional_parameters array|string|null List of additional required parameter
   *
   * @retval array|false Array with LSobject and LSrelation
   */
  public static function _ajax_check_call(&$data, &$conf, &$object, &$relation, $additional_required_parameters=null) {
    $data['success'] = false;
    // Check parameters
    if (!isset($_REQUEST['id'])) {
      self :: log_warning("Parameter 'id' is missing.");
      LSerror :: addErrorCode('LSsession_12');
      return false;
    }

    // Check additional required parameters
    if ($additional_required_parameters) {
      if (!is_array($additional_required_parameters))
        $additional_required_parameters = array($additional_required_parameters);
      foreach($additional_required_parameters as $p) {
        if (!isset($_REQUEST[$p])) {
          self :: log_warning("Parameter '$p' is missing.");
          LSerror :: addErrorCode('LSsession_12');
          return false;
        }
        $data[$p] = $_REQUEST[$p];
      }
    }

    // Check relation exists in session
    if (!isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
      self :: log_warning("No relation '".$_REQUEST['id']."' in session");
      return false;
    }

    // Load object type
    $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
    if (!LSsession ::loadLSobject($conf['objectType'])) {
      self :: log_warning("Fail to load '".$conf['objectType']."'");
      return false;
    }
    $data['id'] = $_REQUEST['id'];

    // Check relation exists
    if (!self :: exists($conf['objectType'], $conf['relationName'])) {
      self :: log_warning("Relation '".$conf['relationName']."' not found in ".$conf['objectType']." configuration");
      return false;
    }

    // Instanciate object and load its data
    $object = new $conf['objectType']();
    if (!$object -> loadData($conf['objectDn'])) {
      self :: log_warning("Fail to load data of '".$conf['objectDn']."'");
      return false;
    }

    // Instanciate relation
    $relation = new LSrelation($object, $conf['relationName']);

    // Check user access to it relation
    if (!$relation -> canEdit()) {
      LSerror :: addErrorCode('LSsession_11');
      return false;
    }

    self :: log_debug("_ajax_check_call(): ok");
    return true;
  }


  /**
   * Init LSselect for a relation
   *
   * @param[in]  &$data Reference to returned data array
   *
   * @retval void
   */
  public static function ajax_initSelection(&$data) {
    $conf = null;
    $object = null;
    $relation = null;
    if (!self :: _ajax_check_call($data, $conf, $object, $relation, 'href')) {
      return;
    }

    // Load LSselect
    if(!LSsession :: loadLSclass('LSselect', null, true)) {
      return;
    }

    // List related objects
    $list = $relation -> listRelatedObjects();
    if (!is_array($list)) {
      self :: log_warning('Fail to list related objects');
      return;
    }

    // Forge selected object list for LSselect
    $selected_objects = array();
    foreach($list as $o) {
      $selected_objects[$o -> getDn()] = array(
        'object_type' => $o -> getType(),
        'editableAttr' => $relation -> relatedEditableAttribute,
      );
    }

    // Init LSselect
    LSselect :: init(
      $_REQUEST['id'],
      array (
        $relation -> LSobject => array(
          'object_type' => $relation -> LSobject,
        )
      ),
      true,
      $selected_objects
    );

    // Set success
    $data['success'] = true;
  }

  /**
   * Update related object from LSselect result
   *
   * @param[in]  &$data Reference to returned data array
   *
   * @retval void
   */
  public static function ajax_updateFromSelection(&$data) {
    $conf = null;
    $object = null;
    $relation = null;
    if (!self :: _ajax_check_call($data, $conf, $object, $relation)) {
      return;
    }

    $LSobjectInRelation = $object->getConfig("LSrelation.".$conf['relationName'].".LSobject");
    $relationConf = $object->getConfig("LSrelation.".$conf['relationName']);

    // Load LSselect
    if(!LSsession :: loadLSclass('LSselect', null, true)) {
      return;
    }

    // Retreive selected object from LSselect
    $selected_objects = LSselect :: getSelectedObjects($_REQUEST['id']);
    if (!is_array($selected_objects)) {
      self :: log_warning("Fail to retreive selected object from LSselect");
      return;
    }
    self :: log_debug('Selected objects: '.varDump($selected_objects));

    // Update related objects
    if (!$relation -> updateRelations(array_keys($selected_objects))) {
      LSerror :: addErrorCode('LSrelations_03', $conf['relationName']);
      self :: log_warning("Fail to update objects in relation");
      return;
    }
    self :: log_debug('Related objects updated');

    // List related objects
    $list = $relation -> listRelatedObjects();
    if (is_array($list) && !empty($list)) {
      $data['html']="";
      foreach($list as $o) {
        if ($relation -> canEditRelationWithObject($o)) {
          $class=' LSrelation_editable';
        }
        else {
          $class='';
        }
        $data['html'].= "<li class='LSrelation'><a href='object/$LSobjectInRelation/".urlencode($o -> getDn())."' class='LSrelation$class' id='LSrelation_".$_REQUEST['id']."_".$o -> getDn()."'>".$o -> getDisplayName(NULL,true)."</a></li>\n";
      }
    }
    else {
      if (isset($relationConf['emptyText'])) {
        $data['html'] = "<li>".__($relationConf['emptyText'])."</li>\n";
      }
      else {
        $data['html'] = "<li>"._('No object.')."</li>\n";
      }
    }
    $data['success'] = true;
  }

  /**
   * Remove related object specify by DN
   *
   * @param[in]  &$data Reference to returned data array
   *
   * @retval void
   */
  public static function ajax_deleteByDn(&$data) {
    $conf = null;
    $object = null;
    $relation = null;
    if (!self :: _ajax_check_call($data, $conf, $object, $relation, 'dn')) {
      return;
    }

    // List related objects
    $list = $relation -> listRelatedObjects();
    if (!is_array($list)) {
      self :: log_warning('Fail to list related objects');
      LSerror :: addErrorCode('LSrelations_03',$conf['relationName']);
      return;
    }

    // For each related objects:
    // - check if DN match
    // - check user can edit relation with specific object
    // - remove relation
    $found = false;
    foreach($list as $o) {
      if($o -> getDn() == $_REQUEST['dn']) {
        if (!$relation -> canEditRelationWithObject($o)) {
          LSerror :: addErrorCode('LSsession_11');
          return;
        }
        if (!$relation -> removeRelationWithObject($o)) {
          LSerror :: addErrorCode('LSrelations_03', $conf['relationName']);
          return;
        }
        else {
          $found = true;
        }
        break;
      }
    }

    // Check object found
    if (!$found) {
      self :: log_warning("Object '".$_REQUEST['dn']."' not found in related objects list.");
      LSerror :: addErrorCode('LSrelations_03', $conf['relationName']);
      return;
    }

    // Set success
    $data['dn'] = $_REQUEST['dn'];
    $data['success'] = true;
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSrelations_01',
___("LSrelation : Invalid parameter '%{parameter}' of the relation %{relation}: objects %{LSobject} have no function '%{function}'.")
);
LSerror :: defineError('LSrelations_02',
___("LSrelation : Relation %{relation} of object type %{LSobject} unknown.")
);
LSerror :: defineError('LSrelations_03',
___("LSrelation : Error during relation update of the relation %{relation}.")
);
LSerror :: defineError('LSrelations_04',
___("LSrelation : Object type %{LSobject} unknown (Relation : %{relation}).")
);
LSerror :: defineError('LSrelations_05',
___("LSrelation : Incomplete configuration for LSrelation %{relation} of object type %{LSobject} for action : %{action}.")
);
LSerror :: defineError('LSrelations_06',
___("LSrelation : Invalid editable attribute for LSrelation %{relation} with LSobject %{LSobject}.")
);
LSerror :: defineError('LSrelations_07',
___("LSrelation : The configuration parameter '%{parameter}' of the relation %{relation} of %{LSobject} is missing.")
);
LSerror :: defineError('LSrelations_08',
___("LSrelation : The configuration parameter '%{parameter}' of the relation %{relation} of %{LSobject} is invalid.")
);
