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
 * Element ssh_key d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments ssh_key des formulaires.
 * Elle étend la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_ssh_key extends LSformElement {

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $GLOBALS['LSsession'] -> addCssFile('LSformElement_ssh_key.css');
    $return = $this -> getLabelInfos();
    // value
    $return['html'] = "<ul class='LSform'>\n";
    if (!$this -> isFreeze()) {
      if (empty($this -> values)) {
        $return['html'] .= "<li>".$this -> getEmptyField()."</li>\n";
      }
      else {
        foreach($this -> values as $value) {
          $multiple = $this -> getMultipleData();
          $id = "LSform_".$this -> name."_".rand();
          $return['html'].="<li class='LSformElement_ssh_key'><textarea name='".$this -> name."[]' id='".$id."' class='LSform LSformElement_ssh_key'>".$value."</textarea>\n".$multiple."</li>";
        }
      }
    }
    else {
      if (empty($this -> values)) {
        $return['html'].="<li>"._('Aucune valeur definie')."</li>\n";
      }
      else {
        $GLOBALS['LSsession'] -> addJSscript('LSformElement_ssh_key.js');
        foreach ($this -> values as $value) {
          if (ereg('^ssh-([a-z]+) (.*)== (.*)$',$value,$regs)) {
            $return['html'].="<li><span class='LSformElement_ssh_key_short_display' title='"._("Cliquer pour afficher la valeur complète")."'>".substr($regs[2],0,10)."...</span> (Type : ".$regs[1].") <a href='mailto:".$regs[3]."'>".$regs[3]."</a><p class='LSformElement_ssh_key_value'>".$value."</p></li>\n";
          }
          else {
            $return['html'].="<li><span class='LSformElement_ssh_key_short_display'>".substr($value,0,15)."...</span> ("._('Type non reconnu').")<p class='LSformElement_ssh_key_value'>".$value."</p></li>\n";
          }
        }
      }
    }
    $return['html'] .= "</ul>\n"; 
    return $return;
  }

 /**
  * Retourne le code HTML d'un champ vide
  *
  * @retval string Code HTML d'un champ vide.
  */
  function getEmptyField() {
    $multiple = $this -> getMultipleData();
    return "<textarea name='".$this -> name."[]' id='LSform".$this -> name."_".rand()."' class='LSform LSformElement_ssh_key'></textarea>\n".$multiple;
  }

}

?>
