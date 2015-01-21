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
  function getDisplay($refresh=NULL){
    LSsession :: addCssFile('LSformElement_select_object.css');
    if ($refresh) {
      $this -> values = $this -> getValuesFromSession();
    }
    $return = $this -> getLabelInfos();

    if (!$this -> isFreeze()) {
      LSsession :: addJSconfigParam(
        $this -> name,
        array(
          'object_type' => $this -> selectableObject,
          'addBtn' => _('Modify'),
          'deleteBtns' => _('Delete'),
          'up_label' => _('Move up'),
          'down_label' => _('Move down'),
          'ordered' => (($this -> params['html_options']['ordered'])?1:0),
          'multiple' => (($this -> params['multiple'])?1:0),
          'filter64' => (($this -> params['html_options']['selectable_object']['filter'])?base64_encode($this -> params['html_options']['selectable_object']['filter']):''),
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
      if (LSsession :: loadLSclass('LSselect')) {
        LSselect :: loadDependenciesDisplay();
      }
    }

    if ((!isset($this -> params['html_options']['sort']) || $this -> params['html_options']['sort']) && !$this -> params['html_options']['ordered']) {
      uasort($this -> values,array($this,'_sortTwoValues'));
    }

    $return['html'] = $this -> fetchTemplate(NULL,array(
      'selectableObject' => $this -> selectableObject,
      'unrecognizedValues' => $this -> attr_html -> unrecognizedValues,
      'unrecognizedValueLabel' => _("%{value} (unrecognized value)")
    ));
    return $return;
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
    if (isset($this -> params['html_options']['sortDirection']) && $this -> params['html_options']['sortDirection']=='DESC') {
      $dir=-1;
    }
    else {
      $dir=1;
    }
    if ($va == $vb) return 0;
    $val = strcoll(strtolower($va), strtolower($vb));
    return $val*$dir;
  }
  
  /*
   * Return the values of the object form the session variable
   */
  function getValuesFromSession() {
    return $this -> attr_html -> getValuesFromSession();
  }
  
  /**
   * Defined the type of object witch is selectionable
   * 
   * @param[in] $object string The type of object
   * 
   * @retval void
   **/
  function setSelectableObject($object) {
    $this -> selectableObject = $object;
  }
  
  /**
   * Export the values of the element
   * 
   * @retval Array The values of the element
   */
  function exportValues(){
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
  function setValueFromPostData($data) {
    LSformElement::setValueFromPostData($data);
    $this -> values = $this -> attr_html -> refreshForm($this -> values,true);
    return true;
  }

  /**
   * Search the selectionable objects with a pattern
   * 
   * @param[in] $pattern The pattern of the search
   * 
   * @retval array(dn -> displayName) Found objects
   */
  function searchAdd ($pattern) {
    if (is_array($this -> params['html_options']['selectable_object'])) {
      if (LSsession :: loadLSobject($this -> params['html_options']['selectable_object']['object_type'])) {
        $obj = new $this -> params['html_options']['selectable_object']['object_type']();
        $ret = $obj -> getSelectArray($pattern,NULL,$this -> params['html_options']['selectable_object']['display_name_format'],false,true,$this -> params['html_options']['selectable_object']['filter']);
        if (is_array($ret)) {
          return $ret;
        }
      }
    }
    return array();
  }
 
  /**
   * This ajax method is used to refresh the value display
   * in the form element after the modify window is closed.
   *
   * @param[in] $data The address to the array of data witch will be return by the ajax request
   * 
   * @retval void
   **/
  public static function ajax_refresh(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $form = $object -> getForm($_REQUEST['idform']);
        $field=$form -> getElement($_REQUEST['attribute']);
        $val = $field -> getValuesFromSession();
        if ( $val ) {
          $data = array(
            'objects'    => $val
          );
        }
      }
    }
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

?>
