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
 * Règle de validation d'expression régulière.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_regex extends LSformRule {
  
  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation : 
   *                              - Regex : $option['params']['regex'] ou $option
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */ 
  function validate($value,$option,$formElement) {
    if (is_array($option)) {
      if (isset($option['params']['regex'])) {
        $regex=$option['params']['regex'];
      }
      else {
        LSerror :: addErrorCode('LSformRule_regex_01');
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

/*
 * Error Codes
 */
$GLOBALS['LSerror_code']['LSformRule_regex_01'] = array(
  'msg' => _("LSformRule_regex : Regex has not been configured to validate data.")
);

?>
