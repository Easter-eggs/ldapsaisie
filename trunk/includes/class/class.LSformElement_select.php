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

class LSformElement_select extends LSformElement {

 /*
  * Retourn les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();
    // value
    if (!$this -> isFreeze()) {
      if ($this -> params['multiple']==0) {
        $multiple_tag='';
      }
      else {
        $multiple_tag='multiple';
      }
        
      $return['html'] = "<select name='".$this -> name."[]' $multiple_tag class='LSform'>\n";
      foreach ($this -> params['text_possible_values'] as $choice_value => $choice_text) {
        if (in_array($choice_value, $this -> values)) {
          $selected=' selected';
        }
        else {
          $selected='';
        }
        $return['html'].="<option value=\"".$choice_value."\"$selected>$choice_text</option>\n";
      }
      $return['html'].="</select>\n";
    }
    else {
      $return['html']="<ul class='LSform'>\n";
      foreach ($this -> values as $value) {
        $return['html'].="<li class='LSform'>".$this -> params['text_possible_values'][$value]."</strong></li>";
      }
      $return['html'].="</ul>\n";
    }
    return $return;
  }

}

?>
