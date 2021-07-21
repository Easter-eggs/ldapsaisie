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
 * Validation rule for an integer value
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_integer extends LSformRule{

  // CLI parameters autocompleters
  protected static $cli_params_autocompleters = array(
    'positive' => array('LScli', 'autocomplete_bool'),
    'negative' => array('LScli', 'autocomplete_bool'),
    'minHeight' => array('LScli', 'autocomplete_int'),
    'maxHeight' => array('LScli', 'autocomplete_int'),
  );

  /**
   * Verification value.
   *
   * @param string $values The value
   * @param array $options Validation options
   *                              - Maximum value : $options['params']['max']
   *                              - Minimum value : $options['params']['min']
   *                              - Allow only negative value : $options['params']['negative']
   *                              - Allow only positive value : $options['params']['positive']
   * @param object $formElement The formElement object
   *
   * @return boolean true if the value is valided, false otherwise
   */
  public static function validate($value, $options=array(), &$formElement) {
    $max = LSconfig :: get('params.max', null, 'int', $options);
    if(is_int($max) && $max != 0 && $value > $max) {
      self :: log_debug("value is too higth ($value > $max)");
      return;
    }

    $min = LSconfig :: get('params.min', null, 'int', $options);
    if(is_int($min) && $min != 0 && $value < $min) {
      self :: log_debug("value is too low ($value < $min)");
      return;
    }

    if(LSconfig :: get('params.negative', false, 'bool', $options)) {
      $regex = '/^-[0-9]*$/';
    }
    elseif(LSconfig :: get('params.positive', false, 'bool', $options)) {
      $regex = '/^[0-9]*$/';
    }
    else {
      $regex = '/^-?[0-9]*$/';
    }
    LSsession :: loadLSclass('LSformRule_regex');
    if (!LSformRule_regex :: validate($value,$regex,$formElement)) {
      self :: log_debug("value '$value' does not respect regex '$regex'");
      return;
    }
    return true;
  }

}
