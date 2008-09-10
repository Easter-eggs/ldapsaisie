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
 * Cette classe définis les éléments select des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_select_object extends LSformElement {

 /**
  * Retourn les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
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
    $id=rand();
    
    
    if (!$this -> isFreeze()) {
      $addBtn="<li class='LSformElement_select_object_addBtn'>
      <a href='select.php?LSobject=".$this -> selectableObject."' class='LSformElement_select_object LSformElement_select_object_addBtn' id='a_LSformElement_select_object_".$this -> name."_$id'>"._('Modifier')."</a>\n
      <input type='hidden' name='LSformElement_select_object_objecttype' id='LSformElement_select_object_objecttype_$id' value='".$this -> selectableObject."' />\n
      </li>\n";
      $delete=" <img src='templates/images/delete.png' alt='"._('Supprimer')."' class='LSformElement_select_object_deleteBtn'/>";
      $class='LSformElement_select_object';
    }
    else {
      $class='';
      $delete='';
      $addBtn='';
    }
    
    $return['html']="<ul class='LSform ".$class."' id='LSformElement_select_object_".$this -> name."_$id'>\n";
    $return['html'].=$addBtn;
    foreach ($this -> values as $value => $txt) {
      $return['html'].="<li class='".$class."'><a href='view.php?LSobject=".$this -> selectableObject."&amp;dn=".$value."' title='"._('Voir')." ' class='LSformElement_select_object'>".$txt."</a><input type='hidden' class='LSformElement_select_object' name='".$this -> name."[]' value='".$value."' />$delete</li>\n";
    }
    $return['html'].="</ul>\n";
    if (!$this -> isFreeze()) {
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
   * Défini le type d'objet sélectionnable
   * 
   * @param[in] $object string Le type d'object
   * 
   * @retval void
   **/
  function setSelectableObject($object) {
    $this -> selectableObject = $object;
  }
  
  /**
   * Exporte les valeurs de l'�l�ment
   * 
   * @retval Array Les valeurs de l'�lement
   */
  function exportValues(){
    $retval=array();
    if (is_array($this -> values)) {
      foreach($this -> values as $val => $name) {
        $retval[] = $val;
      }
    }
    return $retval;
  }



}

?>
