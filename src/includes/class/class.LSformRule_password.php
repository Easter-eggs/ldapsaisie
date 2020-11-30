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
 * Règle de validation d'un mot de passe
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_password extends LSformRule {

  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation
   *                          - 'minLength' : la longueur maximale
   *                          - 'maxLength' : la longueur minimale
   *                          - 'prohibitedValues' : Un tableau de valeurs interdites
   *                          - 'regex' : une ou plusieurs expressions régulières
   *                                      devant matche
   *                          - 'minValidRegex' : le nombre minimun d'expressions
   *                                              régulières à valider
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  public static function validate($value, $options=array(), &$formElement) {
    $maxLength = LSconfig :: get('params.maxLength', null, 'int', $options);
    if(!is_null($maxLength) && $maxLength != 0 && strlen($value) > $maxLength) {
      self :: log_debug("password is too long (".strlen($value)." > $maxLength)");
      return;
    }

    $minLength = LSconfig :: get('params.minLength', null, 'int', $options);
    if(!is_null($minLength) && $minLength != 0 && strlen($value) < $minLength) {
      self :: log_debug("password is too short (".strlen($value)." < $minLength)");
      return;
    }

    $regex = ensureIsArray(LSconfig :: get('params.regex', null, null, $options));
    if($regex) {
      $minValidRegex = LSconfig :: get('params.minValidRegex', count($regex), 'int', $options);
      if ($minValidRegex == 0 || $minValidRegex > count($regex))
        $minValidRegex = count($regex);
      self :: log_debug("password must match with $minValidRegex regex on ".count($regex));

      $valid = 0;
      foreach($regex as $r) {
        if ($r[0] != '/') {
          LSerror :: addErrorCode('LSformRule_password_01');
          continue;
        }
        if (preg_match($r, $value)) {
	        self :: log_debug("password match with regex '$r'");
          $valid++;
       	}
      	else
	        self :: log_debug("password does not match with regex '$r'");
      }
      if ($valid < $minValidRegex) {
        self :: log_warning("password match with only $valid regex on ".count($regex).". $minValidRegex valid regex is required");
        return;
      }
    }

    $prohibitedValues = ensureIsArray(LSconfig :: get('params.prohibitedValues', null, null, $options));
    if(in_array($value, $prohibitedValues)) {
      self :: log_debug("this password is prohibited");
      return;
    }

    return true;
  }

}


/*
 * Error Codes
 */
LSerror :: defineError('LSformRule_password_01',
___("LSformRule_password : Invalid regex configured : %{regex}. You must use PCRE (begining by '/' caracter).")
);
