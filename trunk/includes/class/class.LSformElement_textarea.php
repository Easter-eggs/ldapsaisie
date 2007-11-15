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
 * Cette classe d�finis les �l�ments textarea des formulaires.
 * Elle �tant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_textarea extends LSformElement {

 /*
  * Affiche l'�l�ment
  * 
  * Cette m�thode affiche l'�lement
  *
  * @retval void
  */
  function display(){
		echo "\t<tr>\n";
		$this -> displayLabel();
		// value
		if (!$this -> isFreeze()) {
			echo "\t\t<td>\n";

			if (empty($this -> values)) {
				echo "\t\t\t<textarea name='".$this -> name."[]'></textarea>\n";
			}
			else {
				foreach($this -> values as $value) {
					echo "\t\t\t<textarea name='".$this -> name."[]'>".$value."</textarea>\n";
				}
			}

			echo "\t\t</td>\n";
		}
		else {
			echo "\t\t<td>\n";

			if (empty($this -> values)) {
				echo "\t\t\t\t<li>"._('Aucunes valeur definie')."</li>\n";
			}
			else {
				foreach ($this -> values as $value) {
					echo "\t\t\t\t<li>".$value."</li>\n";
				}
			}

			echo "\t\t</td>\n";
		}	
		echo "\t</tr>\n";
  }
    
}

?>
