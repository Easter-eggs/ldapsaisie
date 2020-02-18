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
  public static function validate ($value,$options=array(),$formElement) {
    $maxLength = LSconfig :: get('params.maxLength', null, 'int', $options);
    if(is_int($maxLength) && strlen($value) > $maxLength)
      return;

    $minLength = LSconfig :: get('params.minLength', null, 'int', $options);
    if(is_int($minLength) && strlen($value) < $minLength)
      return;

    $regex = LSconfig :: get('params.regex', null, null, $options);
    if(!is_null($regex)) {
      if (!is_array($regex))
        $regex = array($regex);

      $minValidRegex = LSconfig :: get('params.minValidRegex', count($regex), 'int', $options);
      if ($minValidRegex == 0 || $minValidRegex > count($regex))
        $minValidRegex = count($regex);

      $valid=0;
      foreach($regex as $r) {
        if ($r[0] != '/') {
          LSerror :: addErrorCode('LSformRule_password_01');
          continue;
        }
        if (preg_match($r, $value))
          $valid++;
      }
      if ($valid < $minValidRegex)
        return;
    }

    $prohibitedValues = LSconfig :: get('params.prohibitedValues', null, null, $options);
    if(is_array($prohibitedValues) && in_array($value, $prohibitedValues))
      return;

    return true;
  }

}


/*
 * Error Codes
 */
LSerror :: defineError('LSformRule_password_01',
_("LSformRule_password : Invalid regex configured : %{regex}. You must use PCRE (begining by '/' caracter).")
);

