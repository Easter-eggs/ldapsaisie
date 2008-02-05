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
 * Element textarea d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments textarea des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_textarea extends LSformElement {

 /*
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
				$return['html'] = "<li>".$this -> getEmptyField()."</li>\n";
			}
			else {
				$multiple = $this -> getMultipleData();
				foreach($this -> values as $value) {
					$id = "LSform_".$this -> name."_".rand();
					$return['html'].="<li><textarea name='".$this -> name."[]' id='".$id."'>".$value."</textarea>\n".$multiple."</li>";
				}
			}
			$return['html'] .= "</ul>\n";
		}
		else {
			$return['html'] = "<ul class='LSform'>\n";
			if (empty($this -> values)) {
				$return['html'].="<li>"._('Aucunes valeur definie')."</li>\n";
			}
			else {
				foreach ($this -> values as $value) {
					$return['html'].="<li>".$value."</li>\n";
				}
			}
			$return['html'] .= "</ul>\n";
		}	
		return $return;
  }

 /*
 	* Retourne le code HTML d'un champ vide
	*
	* @retval string Code HTML d'un champ vide.
	*/
	function getEmptyField() {
		$multiple = $this -> getMultipleData();
		return "<textarea name='".$this -> name."[]' id='LSform".$this -> name."_".rand()."'></textarea>\n".$multiple;
  }

}

?>
