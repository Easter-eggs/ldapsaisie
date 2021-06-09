<?php
/*******************************************************************************
 * Copyright (C) 2021 Easter-eggs
 * https://ldapsaisie.org
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

LSsession :: loadLSclass('LSformElement_supannLabeledValue');
LSsession :: loadLSaddon('supann');

/**
 * Element supannAdressePostalePrivee d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannAdressePostalePrivee des formulaires.
 * Elle etant la classe basic LSformElement_supannLabeledValue.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannAdressePostalePrivee extends LSformElement_supannLabeledValue {

	var $supannLabelNomenclatureTable = 'adressePostalePriveeLabel';
  var $valueFieldType = 'textarea';


 /**
  * Parse une valeur
  *
  * @param[in] $value La valeur
  *
  * @retval array Un tableau cle->valeur contenant value, translated et label
  **/
	public function parseValue($value) {
		$retval = parent :: parseValue($value);
		$retval['value'] = str_replace('$', "\n", $retval['value']);
		$retval['translated'] = str_replace('$', "\n", $retval['translated']);
	  return $retval;
	}


  /**
   * Recupère la valeur de l'élement passée en POST
   *
   * Cette méthode s'occupe de remplacer les retour à la ligne dans les valeur de l'attribut
   * pas des caractères '$'.

   * @see LSformElement::getPostData()
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
	public function getPostData(&$return, $onlyIfPresent=false) {
		$retval = parent :: getPostData($return, $onlyIfPresent);
	  if (isset($return[$this -> name])) {
			$fixed_values = array();
	    foreach($return[$this -> name] as $value)
				$fixed_values[] = str_replace("\n", "$", $value);
			$return[$this -> name] = $fixed_values;
	  }
	  return $retval;
	}

}
