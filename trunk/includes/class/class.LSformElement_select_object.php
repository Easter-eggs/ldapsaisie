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
 * Element select d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments select des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * Options HTML : 
 * // *************************************
 * 'html_options' => array (
 *   selectable_object => array (
 *     'object_type' => '[Type d'LSobject selectionnable]',
 *     'display_name_format' => '[LSformat du nom d'affichage des LSobjects]',
 *     'value_attribute' => '[LSformat de la valeur clé référant à un LSobject donnée]'
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
  * Retourn les infos d'affichage de l'Ã©lÃ©ment
  * 
  * Cette mÃ©thode retourne les informations d'affichage de l'Ã©lement
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
          'multiple' => (($this -> params['multiple'])?1:0),
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
    $return['html'] = $this -> fetchTemplate(NULL,array('selectableObject' => $this -> selectableObject));
    return $return;
  }
  
  /*
   * Retourne les valeurs de l'objet à partir de la variable Session
   */
  function getValuesFromSession() {
    return $this -> attr_html -> getValuesFromSession();
  }
  
  /**
   * DÃ©fini le type d'objet sÃ©lectionnable
   * 
   * @param[in] $object string Le type d'object
   * 
   * @retval void
   **/
  function setSelectableObject($object) {
    $this -> selectableObject = $object;
  }
  
  /**
   * Exporte les valeurs de l'élément
   * 
   * @retval Array Les valeurs de l'élement
   */
  function exportValues(){
    $values = $this -> attr_html -> getValuesFromFormValues($this -> values);
    return $values;
  }

  /**
   * Définis la valeur de l'élément à partir des données 
   * envoyées en POST du formulaire
   *
   * Cette méthode définis la valeur de l'élément à partir des données 
   * envoyées en POST du formulaire.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array La futur valeur de l'élément
   *
   * @retval boolean Retourne True
   */
  function setValueFromPostData($data) {
    LSformElement::setValueFromPostData($data);
    $this -> values = $this -> attr_html -> refreshForm($this -> values,true);
    return true;
  }

  /**
   * Recherche les objets sélectionnables à partir du pattern fournis
   * 
   * @param[in] $pattern Pattern de recherche
   * 
   * @retval array(dn -> displayName) Les objets trouvés
   */
  function searchAdd ($pattern) {
    if (is_array($this -> params['html_options']['selectable_object'])) {
      if (LSsession :: loadLSobject($this -> params['html_options']['selectable_object']['object_type'])) {
        $obj = new $this -> params['html_options']['selectable_object']['object_type']();
        $ret = $obj -> getSelectArray($pattern,NULL,$this -> params['html_options']['selectable_object']['display_name_format']);
        if (is_array($ret)) {
          return $ret;
        }
      }
    }
    return array();
  }
  
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
