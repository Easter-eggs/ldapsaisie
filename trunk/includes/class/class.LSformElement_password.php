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
 * Element password d'un formulaire pour LdapSaisie
 *
 * Cette classe d�finis les �l�ments password des formulaires.
 * Elle �tant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_password extends LSformElement {

	/**
   * Recup�re la valeur de l'�lement pass�e en POST
   *
   * Cette m�thode v�rifie la pr�sence en POST de la valeur de l'�l�ment et la r�cup�re
   * pour la mettre dans le tableau passer en param�tre avec en clef le nom de l'�l�ment
   *
   * @param[] array Pointeur sur le tableau qui recup�rera la valeur.
   *
   * @retval boolean true si la valeur est pr�sente en POST, false sinon
   */
  function getPostData(&$return) {
		// R�cup�re la valeur dans _POST, et les v�rifie avec la fonction g�n�rale
		$retval = parent :: getPostData($return);
		// Si une valeur est recup�r�e
    if ($retval) {
			$val = $this -> form -> ldapObject -> attrs[$this -> name] -> getValue();	
    	if( (empty($return[$this -> name][0]) ) && ( ! empty( $val ) ) ) {
				unset($return[$this -> name]);
				$this -> form -> _notUpdate[$this -> name] == true;
				return true;
			}
		}
    return $retval;
  }

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
			echo "\t\t\t<ul>\n";

			if (empty($this -> values)) {
				echo "\t\t\t\t<li><input type='password' name='".$this -> name."[]' \"></li>\n";
			}
			else {
				foreach ($this -> values as $value) {
					echo "\t\t\t\t<li><input type='password' name='".$this -> name."[]'/></li>\n";
				}
			}

			echo "\t\t\t</ul>\n";
			echo "\t\t\t* "._('Modification uniquement').".";
			echo "\t\t</td>\n";
		}
		else {
			echo "\t\t<td>\n";
			echo "\t\t\t<ul>\n";

			if (empty($this -> values)) {
				echo "\t\t\t\t<li>"._('Aucunes valeur definie')."</li>\n";
			}
			else {
				foreach ($this -> values as $value) {
					echo "\t\t\t\t<li>".$value."</li>\n";
				}
			}

			echo "\t\t\t</ul>\n";
			echo "\t\t</td>\n";
		}
		echo "\t</tr>\n";
	}
	    
}
	
?>
