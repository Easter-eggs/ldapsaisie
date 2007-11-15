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
  * Affiche l'élément
  * 
  * Cette méthode affiche l'élement
  *
  * @retval void
  */
  function display(){
		echo "\t<tr>\n";
		$this -> displayLabel();
		// value
		if (!$this -> isFreeze()) {
			echo "\t\t<td>\n";
			echo "\t\t\t<select name='".$this -> name."' multiple>\n";
			foreach ($this -> params['text_possible_values'] as $choice_value => $choice_text) {
				if (in_array($choice_value, $this -> values)) {
					$selected=' selected';
				}
				else {
					$selected='';
				}
				echo "\t\t\t\t<option value=\"".$choice_value."\"$selected>$choice_text</option>\n";
			}
			echo "\t\t\t</select>\n";
			echo "\t\t</td>\n";
		}
		else {
			echo "\t\t<td>\n";
			echo "\t\t\t<ul>\n";
			foreach ($params['possible_values'] as $choice_value => $choice_text) {
				if (in_array($choice_value, $this -> value)) {
					echo "<li><strong>$choice_text</strong></li>";
				}
				else {
					echo "<li>$choice_text</li>";
				}
			}
			echo "\t\t\t</ul>\n";
			echo "\t\t</td>\n";
		}
		echo "\t</tr>\n";
	}
  
}

?>
