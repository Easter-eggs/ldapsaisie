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

LSsession :: loadLSclass('LSformElement');

/**
 * Select object element for LdapSaisie form
 *
 * This class define select elements for form. It extends the generic class LSformElement.
 *
 * HTML options :
 * // *************************************
 * 'html_options' => array (
 *   selectable_object => array (
 *     'object_type' => '[Type of LSobject witch is selectable]',
 *     'display_name_format' => '[LSformat of the display name of the LSobjects]',
 *     'value_attribute' => '[The attribute name whitch is used as the key value of one LSobject]'
 *   )
 * ),
 * // *************************************
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_select_object extends LSformElement {

  var $fieldTemplate = 'LSformElement_select_object_field.tpl';
  var $template = 'LSformElement_select_object.tpl';

 /**
  * Return display informations of the element
  *
  * This method return the display informations of the element.
  *
  * @retval array
  */
  public function getDisplay($refresh=NULL){
    LSsession :: addCssFile('LSformElement_select_object.css');
    if ($refresh) {
      $this -> values = $this -> getValuesFromLSselect();
    }
    $return = $this -> getLabelInfos();

    if (!$this -> isFreeze()) {
      LSsession :: addJSconfigParam(
        $this -> name,
        array(
          'LSselect_id' => $this -> attr_html -> getLSselectId(),
          'addBtn' => _('Modify'),
          'deleteBtns' => _('Delete'),
          'up_label' => _('Move up'),
          'down_label' => _('Move down'),
          'ordered' => $this -> getParam('html_options.ordered', 0, 'int'),
          'multiple' => $this -> getParam('multiple', 0, 'int'),
          'filter64' => base64_encode($this -> getParam('html_options.selectable_object.filter', '', 'string')),
          'noValueLabel' => _('No set value'),
          'noResultLabel' => _('No result')
        )
      );

      LSsession :: addHelpInfos (
        'LSformElement_select_object',
        array(
          'searchAdd' => _("Fast Add"),
          'add' => _("Display advanced search and selection panel."),
          'delete' => _("Delete")
        )
      );

      LSsession :: addJSscript('LSformElement_select_object_field.js');
      LSsession :: addJSscript('LSformElement_select_object.js');
      if (LSsession :: loadLSclass('LSselect') && $this -> initLSselect()) {
        LSselect :: loadDependenciesDisplay();
      }
    }

    if ($this -> getParam('html_options.sort', true) && !$this -> getParam('html_options.ordered', false, 'bool')) {
      uasort($this -> values, array($this, '_sortTwoValues'));
    }

    $return['html'] = $this -> fetchTemplate(NULL,array(
      'unrecognizedValues' => $this -> attr_html -> unrecognizedValues,
      'unrecognizedValueLabel' => _("%{value} (unrecognized value)")
    ));
    return $return;
  }

  /**
   * Init LSselect
   *
   * @retval boolean True if LSselect is initialized, false otherwise
   */
  private function initLSselect() {
    // Retreive selectable objects configuratio from HTML attr
    $objs = null;
    $confs = $this -> attr_html -> getSelectableObjectsConfig($objs);
    if (!is_array($confs)) {
      self :: log_warning($this -> name.": html_options.selectable_object not defined");
      return false;
    }

    // Build selectable objects type list as required by LSselect
    $select_conf = array();
    foreach ($confs as $obj_type => $conf) {
      $select_conf[$obj_type] = array(
        'object_type' => $conf['object_type'],
        'display_name_format' => $conf['display_name_format'],
        'filter' => $conf['filter'],
        'onlyAccessible' => $conf['onlyAccessible'],
      );
    }

    // Init LSselect
    LSselect :: init(
      $this -> attr_html -> getLSselectId(),
      $select_conf,
      boolval($this -> getParam('multiple', 0, 'int')),
      $this -> values
    );
    return True;
  }

   /**
   * Function use with uasort to sort two values
   *
   * @param[in] $va string One value
   * @param[in] $vb string One value
   *
   * @retval int Value for uasort
   **/
  private function _sortTwoValues(&$va,&$vb) {
    if ($this -> getParam('html_options.sortDirection') == 'DESC') {
      $dir=-1;
    }
    else {
      $dir=1;
    }
    if ($va == $vb) return 0;
    $val = strcoll(strtolower($va['name']), strtolower($vb['name']));
    return $val*$dir;
  }

  /*
   * Return the values of the object form the session variable
   */
  public function getValuesFromLSselect() {
    return $this -> attr_html -> getValuesFromLSselect();
  }

  /**
   * Export the values of the element
   *
   * @retval Array The values of the element
   */
  public function exportValues(){
    $values = $this -> attr_html -> getValuesFromFormValues($this -> values);
    return $values;
  }

  /**
   * Defined the value of the element from the data sent in POST with the form.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array The new value of the element
   *
   * @retval boolean Return True
   */
  public function setValueFromPostData($data) {
    parent :: setValueFromPostData($data);
    self :: log_debug("setValueFromPostData(): input values=".varDump($this -> values));
    $this -> values = $this -> attr_html -> refreshForm($this -> values, true);
    self :: log_debug("setValueFromPostData(): final values=".varDump($this -> values));
    return true;
  }

  /**
   * Search the selectionable objects with a pattern
   *
   * @param[in] $pattern The pattern of the search
   *
   * @retval array(dn -> displayName) Found objects
   */
  public function searchAdd($pattern) {
    $objs = array();
    $confs = $this -> attr_html -> getSelectableObjectsConfig($objs);
    if (!is_array($confs))
      return;
    $selectable_objects = array();
    foreach($confs as $object_type => $conf) {
      $obj_type = $this -> getParam('html_options.selectable_object.object_type');
      $sparams = array();
      $sparams['onlyAccessible'] = $conf['onlyAccessible'];
      $objects = $objs[$object_type] -> getSelectArray(
        $pattern,
        NULL,
        $conf['display_name_format'],
        false,
        true,
        $conf['filter'],
        $sparams
      );
      self :: log_debug($objects);
      if (!is_array($objects)) {
        self :: log_warning("searchAdd($pattern): error occured looking for matching $object_type objects");
        continue;
      }
      foreach($objects as $dn => $name) {
        $selectable_objects[$dn] = array (
          'object_type' => $object_type,
          'name' => $name,
        );
      }
    }
    return $selectable_objects;
  }

  /**
   * This ajax method is used to refresh the value display
   * in the form element after the modify LSselect window is closed.
   *
   * @param[in] $data The address to the array of data witch will be return by the ajax request
   *
   * @retval void
   **/
  public static function ajax_refresh(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        if ($_REQUEST['idform'] == 'create' || ($_REQUEST['objectdn'] && $object -> loadData($_REQUEST['objectdn']))) {
          $form = $object -> getForm($_REQUEST['idform']);
          $field = $form -> getElement($_REQUEST['attribute']);
          $val = $field -> getValuesFromLSselect();
          if ( is_array($val) ) {
            $data = array(
              'objects'    => $val
            );
          }
          else
            self :: log_debug('ajax_refresh(): invalid return of $field -> getValuesFromLSselect()');
        }
        else
          self :: log_error("ajax_refresh(): Fail to load data of object ".$_REQUEST['objecttype']." from DN '".$_REQUEST['objectdn']."'");
      }
      else
        self :: log_error("ajax_refresh(): Fail to load object type '".$_REQUEST['objecttype']."'");
    }
    else
      self :: log_error("ajax_refresh(): some parameter(s) are missing");
  }

  /**
   * This ajax method is used by the search-and-add function of the form element.
   *
   * @param[in] $data The address to the array of data witch will be return by the ajax request
   *
   * @retval void
   **/
  public static function ajax_searchAdd(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['pattern'])) && (isset($_REQUEST['idform'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $form = $object -> getForm($_REQUEST['idform']);
        $field=$form -> getElement($_REQUEST['attribute']);
        $data['objects'] = $field -> searchAdd($_REQUEST['pattern']);
      }
    }
  }

}
