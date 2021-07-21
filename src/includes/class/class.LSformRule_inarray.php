<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
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

/**
 * Règle de validation à partir d'une liste de valeur possible
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_inarray extends LSformRule {

  // CLI parameters autocompleters
  protected static $cli_params_autocompleters = array(
    'possible_values' => null,
  );

  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation :
   *                              - Regex : $option['params']['possible_values'] ou $option
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  public static function validate($value, $options=array(), &$formElement) {
    $possible_values = LSconfig :: get('params.possible_values', null, null, $options);
    if (!is_array($possible_values)) {
      LSerror :: addErrorCode('LSformRule_inarray_01');
      return;
    }
    if (!in_array($value, $possible_values))
      return false;
    return true;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSformRule_inarray_01',
___("LSformRule_inarray : Possible values has not been configured to validate data.")
);
