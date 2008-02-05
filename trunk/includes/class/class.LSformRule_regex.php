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
 * R�gle de validation d'expression r�guli�re.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_regex extends LSformRule {
  
 	/**
   * V�rification de la valeur.
 	 *
	 * @param string $values Valeur � v�rifier
	 * @param array $options Options de validation : 
	 * 															- Regex : $option['params']['regex'] ou $option
	 *
	 * @return boolean true si la valeur est valide, false sinon
	 */ 
  function validate($value,$option) {
		if (is_array($option)) {
			if (isset($option['params']['regex'])) {
				$regex=$option['params']['regex'];
			}
			else {
				$GLOBALS['LSerror'] -> addErrorCode(301);
				return;
			}
		}
		else {
			$regex=$option;
		}
    if (!preg_match($regex, $value)) {
      return false;
	 }
	 return true;
  }

}

?>
