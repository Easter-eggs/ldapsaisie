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
 * Element texte d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments boolean des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_boolean extends LSformElement {

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();
    // value
    if (!$this -> isFreeze()) {
      $return['html'] = "<ul class='LSform'>\n";
      if (empty($this -> values)) {
        $return['html'] .= "<li class='LSformElement_boolean'>".$this -> getEmptyField()."</li>\n";
      }
      else {
        foreach ($this -> values as $value) {
          $return['html'] .= "<li class='LSformElement_boolean'><input type='radio' value='1' name='".$this -> name."[0]' ".(($this -> isTrue($this -> values))?'checked':'')." /> "._('Oui')."<input type='radio' value='0' name='".$this -> name."[0]' ".(($this -> isTrue($this -> values))?'':'checked')." /> "._('Non')."</li>\n";
        }
      }
      $return['html'] .= "</ul>\n";
      $GLOBALS['LSsession'] -> addJSscript('LSformElement_boolean.js');
    }
    else {
      $return['html'] = "<ul class='LSform LSformElement_text'>\n";
      if (empty($this -> values)) {
        $return['html'] .= "<li>"._('Aucune valeur definie')."</li>\n";
      }
      else {
        $return['html'] .= "<li>".(($this -> isTrue($this -> values))?_('Oui'):_('Non'))."</li>\n";
      }
      $return['html'] .= "</ul>\n";
    }
    return $return;
  }

 /**
  * Retourne le code HTML d'un champ vide
  *
  * @retval string Code HTML d'un champ vide.
  */
  function getEmptyField() {
    return "<input type='radio' value='1' name='".$this -> name."[0]' /> "._('Oui')."<input type='radio' value='0' name='".$this -> name."[0]' /> "._('Non');
  }
  
  /**
   * Determine si la valeur passé en paramètre correspond a True ou non
   * 
   * - true = si $data[] contient un champ à 1
   * - false = sinon
   *
   * @param[in] $data La valeur de l'attribut
   *
   * @retval boolean True ou False
   */
  function isTrue($data) {
    if(!is_array($data)) {
      $data=array($data);
    }
    if($data[0]==1) {
      return true;
    }
    return;
  }
  
}

?>
