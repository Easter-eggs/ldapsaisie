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
 * Element select d'un formulaire pour LdapSaisie
 *
 * Cette classe dÃ©finis les Ã©lÃ©ments select des formulaires.
 * Elle Ã©tant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_select_object extends LSformElement {

 /**
  * Retourn les infos d'affichage de l'Ã©lÃ©ment
  * 
  * Cette mÃ©thode retourne les informations d'affichage de l'Ã©lement
  *
  * @retval array
  */
  function getDisplay($refresh=NULL){
    $GLOBALS['LSsession'] -> addCssFile('LSformElement_select_object.css');
    if ($refresh) {
      $this -> values = $this -> attr_html -> getValuesFromSession();
    }
    $return = $this -> getLabelInfos();
    // value
    
    $params=array();
    if (!$this -> isFreeze()) {
      $params['attr_name'] = $this -> name;
      $params['object_type'] = $this -> selectableObject;
      $params['addBtn'] = _('Modifier');
      $params['deleteBtns'] = _('Supprimer');
      $params['multiple'] = ($this -> params['multiple'])?1:0;
      $params['noValueLabel'] = _('Aucune valeur definie');
    }
    
    $ul_id="LSformElement_select_object_".$this -> name;
    $params['freeze'] = $this -> isFreeze();
    $GLOBALS['LSsession'] -> addJSconfigParam($ul_id,$params);
    
    $return['html']="<ul class='LSform LSformElement_select_object' id='$ul_id'>\n";
    if (empty($this -> values)) {
      $return['html'] .= "<li>"._('Aucune valeur definie')."</li>\n";
    }
    else {
      foreach ($this -> values as $value => $txt) {
        $return['html'].="<li><a href='view.php?LSobject=".$this -> selectableObject."&amp;dn=".$value."' title='"._('Voir')." ' class='LSformElement_select_object'>".$txt."</a><input type='hidden' class='LSformElement_select_object' name='".$this -> name."[]' value='".$value."' /></li>\n";
      }     
    }
    $return['html'].="</ul>\n";
    if (!$this -> isFreeze()) {
      $GLOBALS['LSsession'] -> addJSscript('LSformElement_select_object_field.js');
      $GLOBALS['LSsession'] -> addJSscript('LSformElement_select_object.js');
      $GLOBALS['LSsession'] -> addJSscript('LSform.js');
      $GLOBALS['LSsession'] -> addJSscript('LSselect.js');
      $GLOBALS['LSsession'] -> addCssFile('LSselect.css');
      $GLOBALS['LSsession'] -> addJSscript('LSsmoothbox.js');
      $GLOBALS['LSsession'] -> addCssFile('LSsmoothbox.css');
      $GLOBALS['LSsession'] -> addJSscript('LSconfirmBox.js');
      $GLOBALS['LSsession'] -> addCssFile('LSconfirmBox.css');
    }
    return $return;
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

}

?>
