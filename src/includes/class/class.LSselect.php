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

class LSselect extends LSlog_staticLoggerClass {

 /*
  * Méthode chargeant les dépendances d'affichage
  *
  * @retval void
  */
  public static function loadDependenciesDisplay() {
    if (LSsession :: loadLSclass('LSsmoothbox')) {
      LSsmoothbox :: loadDependenciesDisplay();
    }
    LStemplate :: addJSscript('LSselect.js');
    LStemplate :: addCssFile('LSselect.css');
  }

  /**
   * Init a LSobjects selection
   * @param[in] $id        string                     The LSselect ID
   * @param[in] $LSobjects array                      Selectable LSobject types configuration. Must be an array
   *                                                  with object type as key and configuration as value with the
   *                                                  following info:
   *                                                  - object_type: the LSobject type (same as key, required)
   *                                                  - display_name_format: display name LSformat (optional, default: object type default)
   *                                                  - filter: LDAP filter string for selectable objects (optional, default: no filter)
   *                                                  - onlyAccessible: filter on only accessible objects (optional, default: false)
   *                                                  - editableAttr: attribute name of object that must be writable to the object be selectable (optional)
   * @param[in] $multiple   boolean                   True if this selection permit to select more than one object, False otherwise (optional,
   *                                                  default: false)
   * @param[in] $current_selected_objects array|null  Array of current selected objects (optional, see setSelectedObjects for format specification)
   * @return void
   */
  public static function init($id, $LSobjects, $multiple=false, $current_selected_objects=null) {
    if ( !isset($_SESSION['LSselect']) || !is_array($_SESSION['LSselect']))
      $_SESSION['LSselect'] = array();
    $_SESSION['LSselect'][$id] = array (
      'LSobjects' => $LSobjects,
      'multiple' => $multiple,
      'selected_objects' => array(),
    );
    if (is_array($current_selected_objects))
      self :: setSelectedObjects($id, $current_selected_objects);
    self :: log_debug("Initialized with id=$id: multiple=".($multiple?'yes':'no')." ".count($_SESSION['LSselect'][$id]['selected_objects'])." selected object(s).");
  }

  /**
   * Check a LSselect exist by ID
   *
   * @param[in] $id string The LSselect ID
   *
   * @retval boolean
   */
  public static function exists($id) {
    if (isset($_SESSION['LSselect']) && is_array($_SESSION['LSselect']) &&
        isset($_SESSION['LSselect'][$id]) && is_array($_SESSION['LSselect'][$id]))
      return true;
    return false;
  }

  /**
   * Return a configuration parameter (or default value)
   *
   * @param[] $id string      The LSselect ID
   * @param[] $param string   The configuration parameter
   * @param[] $default mixed  The default value (optional, default : null)
   * @param[] $cast           Cast string|null resulting value in specific type
   *                          (optional, default : null=disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  public static function getConfig($id, $key, $default=null, $cast=null) {
    if (!self :: exists($id))
      return false;
    return LSconfig :: get($key, $default, $cast, $_SESSION['LSselect'][$id]);
  }

  /**
   * Check if LSselect exist and is multiple
   *
   * @param[in] $id string The LSselect ID
   *
   * @retval boolean
   */
  public static function isMultiple($id) {
    return self :: getConfig($id, 'multiple', false, 'bool');
  }

  /**
   * Get LSsearch object corresponding to the specified selection
   *
   * @param[in] $id string The LSselect ID
   * @param[in] $object_type string|null The object type of the search (optional,
   *                                     default: first one object type configured)
   *
   * @retval LSsearch|false The LSsearch object, or false in case of error
   */
  public static function &getSearch($id, $object_type=null) {
    // Check parameters
    if (!self :: exists($id)) {
      self :: log_debug("getSearch($id): does not exists.");
      return false;
    }
    if (is_null($object_type))
      $object_type = array_keys($_SESSION['LSselect'][$id]['LSobjects'])[0];
    elseif  (!array_key_exists($object_type, $_SESSION['LSselect'][$id]['LSobjects'])) {
      self :: log_debug("getSearch($id): this selection does not joined '$object_type' objects.");
      return false;
    }

    // Load LSobject type & LSsearch
    if ( !LSsession :: loadLSobject($object_type) || !LSsession :: loadLSclass('LSsearch', null, true) ) {
      self :: log_debug("getSearch($id): fail to load $object_type object type or LSsearch class");
      return false;
    }

    // Instanciate object
    $search = new LSsearch($object_type, "LSselect::$id");

    /*
     * Set parameters from config
     */

    // filter (optional)
    $filter = self :: getConfig($id, "LSobjects.$object_type.filter");
    if ($filter) $search -> setParam('filter', $filter);

    // display_name_format (optional)
    $display_name_format = self :: getConfig($id, "LSobjects.$object_type.display_name_format");
    if ($display_name_format) $search -> setParam('displayFormat', $display_name_format);

    // onlyAccessible (default: false)
    $search -> setParam(
      'onlyAccessible',
      self :: getConfig($id, "LSobjects.$object_type.onlyAccessible", false, 'bool')
    );

    // Add LSsearchEntry customInfos
    $search -> setParam('customInfos', array (
      'selectable' => array (
        'function' => array('LSselect', 'selectable'),
        'args' => $id,
      ),
      'selected' => array (
        'function' => array('LSselect', 'selected'),
        'args' => $id,
        'cache' => false,
      ),
    ));

    return $search;
  }

  /**
   * Get selectable object types
   *
   * @param[in] $id string The LSselect ID
   *
   * @retval array|false Array of selectable object types with name as key
   *                     and label as value, or false if LSselect doesn't exists.
   */
  public static function getSelectableObjectTypes($id) {
    if (!self :: exists($id))
      return false;
    $selectable_objects = array();
    foreach ($_SESSION['LSselect'][$id]['LSobjects'] as $type => $conf)
      if (LSsession :: loadLSobject($type))
        $selectable_objects[$type] = LSldapObject :: getLabel($type);
    return $selectable_objects;
  }

  /**
   * Get selectable objects info
   *
   * @param[in] $id string The LSselect ID
   *
   * @retval array|false Array of selectable object info with objects's DN as key
   *                     and array of object's info as value. Objects's info returned
   *                     currently contains only the object type (object_type). if
   *                     LSselect specified doesn't exists, this method return false.
   */
  public static function getSelectedObjects($id) {
    if (!self :: exists($id))
      return false;
    if (is_array($_SESSION['LSselect'][$id]['selected_objects']))
      return $_SESSION['LSselect'][$id]['selected_objects'];
    return false;
  }

  /**
   * Set selectable objects info
   *
   * @param[in] $id string The LSselect ID
   * @param[in] $selected_objects array Array of selectable object info with objects's DN
   *                                    as key and array of object's info as value. Objects's
   *                                    info currently contains only the object type (key=object_type).
   *
   * @retval array|false Array of selectable object info with objects's DN as key
   *                     and array of object's info as value. Objects's info returned
   *                     currently contains only the object type (object_type). if
   *                     LSselect specified doesn't exists, this method return false.
   *
   * @retval void
   */
  public static function setSelectedObjects($id, $selected_objects) {
    if (!self :: exists($id))
      return;
    if (!is_array($selected_objects))
      return;
    $_SESSION['LSselect'][$id]['selected_objects'] = array();
    foreach($selected_objects as $dn => $info) {
      if (!is_array($info) || !isset($info['object_type'])) {
        self :: log_warning("setSelectedObjects($id): invalid object info for dn='$dn'");
        continue;
      }
      if (self :: checkObjectIsSelectable($id, $info['object_type'], $dn))
        $_SESSION['LSselect'][$id]['selected_objects'][$dn] = $info;
      else {
        self :: log_warning("setSelectedObjects($id): object type='".$info['object_type']."' and dn='$dn' is not selectable".varDump($_SESSION['LSselect'][$id]));
      }
    }
    self :: log_debug("id=$id: updated with ".count($_SESSION['LSselect'][$id]['selected_objects'])." selected object(s).");
  }

  /**
   * Check if an object is selectable
   *
   * @param[in] $id string The LSselect ID
   * @param[in] $object_type string The object type
   * @param[in] $object_dn string The object DN
   *
   * @retval boolean True if object is selectable, false otherwise
   */
  public static function checkObjectIsSelectable($id, $object_type, $object_dn) {
    if (!self :: exists($id)) {
      self :: log_warning("checkObjectIsSelectable($id, $object_type, $object_dn): LSselect $id doesn't exists");
      return false;
    }
    if (!array_key_exists($object_type, $_SESSION['LSselect'][$id]['LSobjects'])) {
      self :: log_warning("checkObjectIsSelectable($id, $object_type, $object_dn): object type $object_type not selectabled");
      return false;
    }

    // Load LSobject type
    if ( !LSsession :: loadLSobject($object_type) ) {
      self :: log_warning("checkObjectIsSelectable($id, $object_type, $object_dn): fail to load object type $object_type");
      return false;
    }

    // Instanciate object and load object data from DN
    $object = new $object_type();
    if (!$object -> loadData($object_dn, self :: getConfig($id, "LSobjects.$object_type.filter", null))) {
      self :: log_warning("checkObjectIsSelectable($id, $object_type, $object_dn): object $object_dn not found (or does not match with selection filter)");
      return false;
    }

    // Handle onlyAccessible parameter
    if (self :: getConfig($id, "LSobjects.$object_type.onlyAccessible", false, 'bool')) {
      if (!LSsession :: canAccess($object_type, $object_dn)) {
        self :: log_warning("checkObjectIsSelectable($id, $object_type, $object_dn): object $object_dn not accessible");
        return false;
      }
    }

    self :: log_debug("checkObjectIsSelectable($id, $object_type, $object_dn): object selectable");
    return true;
  }

  /*
   * AJAX methods
   */

  /**
   * Add a selected object to selection
   *
   * Request parameters:
   *  - LSselect_id: The LSselect ID
   *  - object_type: The selected object type
   *  - object_dn: The selected object DN
   *
   * Data in answer:
   *  - success: True if object added to selection, false otherwise
   *
   * @param[in]  &$data Reference to returned data array
   *
   * @retval void
   */
  public static function ajax_addSelectedObject(&$data) {
    $data['success'] = false;
    if (!isset($_REQUEST['LSselect_id']) || !isset($_REQUEST['object_type']) || !isset($_REQUEST['object_dn'])) {
      self :: log_warning('ajax_addSelectedObject(): missing parameter.');
      LSerror :: addErrorCode('LSsession_12');
      return;
    }
    $id = $_REQUEST['LSselect_id'];
    $dn = $_REQUEST['object_dn'];
    $type = $_REQUEST['object_type'];

    if (!self :: checkObjectIsSelectable($id, $type, $dn)) {
      self :: log_warning("ajax_addSelectedObject($id): object type='$type' dn='$dn' is not selectable.");
      return;
    }

    self :: log_debug("id=$id: add $type '$dn'");
    if (!$_SESSION['LSselect'][$id]['multiple']) {
      $_SESSION['LSselect'][$id]['selected_objects'] = array(
        $dn => array('object_type' => $type),
      );
      self :: log_debug("ajax_addSelectedObject($id): $dn replace current selected object.");
    }
    else if (!array_key_exists($dn, $_SESSION['LSselect'][$id]['selected_objects'])) {
      $_SESSION['LSselect'][$id]['selected_objects'][$dn] = array('object_type' => $type);
      self :: log_debug("ajax_addSelectedObject($id): $dn added to current selected objects.");
    }
    else {
      self :: log_warning("ajax_addSelectedObject($id): $dn already present in selected objects.");
    }
    $data['success'] = true;
  }

  /**
   * Drop a selected object in selection
   *
   * Request parameters:
   *  - LSselect_id: The LSselect ID
   *  - object_dn: The selected object DN
   *
   * Data in answer:
   *  - success: True if object added to selection, false otherwise
   *
   * @param[in]  &$data Reference to returned data array
   *
   * @retval void
   */
  public static function ajax_dropSelectedObject(&$data) {
    $data['success'] = false;
    if (!isset($_REQUEST['LSselect_id']) || !isset($_REQUEST['object_dn'])) {
      self :: log_warning('ajax_dropSelectedObject(): missing parameter.');
      LSerror :: addErrorCode('LSsession_12');
      return;
    }
    $id = $_REQUEST['LSselect_id'];
    $dn = $_REQUEST['object_dn'];

    if (!self :: exists($id)) {
      self :: log_warning("ajax_dropSelectedObject($id): invalid LSselect ID '$id'.");
      return;
    }
    self :: log_debug("id=$id: remove '$dn'");
    if (array_key_exists($dn, $_SESSION['LSselect'][$id]['selected_objects'])) {
      unset($_SESSION['LSselect'][$id]['selected_objects'][$dn]);
      self :: log_debug("ajax_dropSelectedObject($id): $dn removed from selected objects.");
    }
    else {
      self :: log_warning("ajax_dropSelectedObject($id): $dn not present in selected objects.");
    }
    $data['success'] = true;
  }

  /**
   * Update selected objects of the selection
   *
   * Request parameters:
   *  - LSselect_id: The LSselect ID
   *  - selected_objects: Array of selected object info (see setSelectedObjects for format)
   *
   * Data in answer: any
   *
   * @param[in]  &$data Reference to returned data array
   *
   * @retval void
   */
  public static function ajax_updateSelectedObjects(&$data) {
    if (isset($_REQUEST['LSselect_id']) && isset($_REQUEST['selected_objects']) ) {
      $selected_objects = json_decode($_REQUEST['selected_objects'], true);
      if (is_array($selected_objects)) {
        self :: log_debug('ajax_updateSelectedObjects(): set selected objects: '.varDump($selected_objects));
        self :: setSelectedObjects($_REQUEST['LSselect_id'], $selected_objects);
      }
      else
        self :: log_warning('ajax_updateSelectedObjects(): fail to decode JSON values.');
      $data = array(
        'selected_objects' => $selected_objects
      );
    }
    else {
      LSerror :: addErrorCode('LSsession_12');
    }
  }

  /**
   * Get selected objects of the selection
   *
   * Request parameters:
   *  - LSselect_id: The LSselect ID
   *
   * Data in answer:
   *  - objects: The selected objects info (see getSelectedObjects for format)
   *
   * @param[in]  &$data Reference to returned data array
   *
   * @retval void
   */
  public static function ajax_getSelectedObjects(&$data) {
    if (isset($_REQUEST['LSselect_id'])) {
      $data=array(
        'objects' => self :: getSelectedObjects($_REQUEST['LSselect_id'])
      );
    }
    else {
      LSerror :: addErrorCode('LSsession_12');
    }
  }

  /*
   * LSsearchEntry customInfos helpers
   */

  /**
   * LSsearchEntry selectable customInfos method : check if object is selectable
   *
   * @param[in] $obj LSsearchEntry  The LSsearchEntry object
   * @param[in] $id string The LSselect ID
   *
   * @retval boolean True if object is selectable, False otherwise
   */
  public static function selectable($obj, $id) {
    $editableAttr = self :: getConfig($id, "LSobjects.".$obj->type.".editableAttr");
    if (!$editableAttr)
      return true;
    if ($editableAttr && call_user_func(array($obj->type, 'hasAttr'), $editableAttr)) {
      return (LSsession::canEdit($obj->type, $obj->dn, $editableAttr))?1:0;
    }
    return false;
  }

  /**
   * LSsearchEntry selected customInfos method : check if object is selected
   *
   * @param[in] $obj LSsearchEntry  The LSsearchEntry object
   * @param[in] $id string The LSselect ID
   *
   * @retval boolean True if object is selected, False otherwise
   */
  public static function selected($obj, $id) {
    if (self :: exists($id) &&
        is_array($_SESSION['LSselect'][$id]['selected_objects']) &&
        array_key_exists($obj->dn, $_SESSION['LSselect'][$id]['selected_objects']))
      return true;
    return false;
  }

}
