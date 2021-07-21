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

/**
 * Check the number of values of the attribute against min/max limits
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_numberOfValues extends LSformRule {

  // Validate values one by one or all together
  const validate_one_by_one = False;

  // CLI parameters autocompleters
  protected static $cli_params_autocompleters = array(
    'min' => array('LScli', 'autocomplete_int'),
    'max' => array('LScli', 'autocomplete_int'),
  );

  /**
   * Validate value
   *
   * @param string $values The value to validate
   * @param array $options Validation options
   * @param object $formElement The related formElement object
   *
   * @return boolean true if the value is valide, false if not
   */
  public static function validate($value, $options=array(), &$formElement) {
    $max_values = LSconfig :: get('params.max', null, 'int', $options);
    $min_values = LSconfig :: get('params.min', null, 'int', $options);
    if(is_null($max_values) && is_null($min_values)) {
      LSerror :: addErrorCode('LSformRule_01',array('type' => 'numberOfValues', 'param' => _('max (or min)')));
      return;
    }
    if (!is_null($max_values) && !is_null($min_values) && $max_values < $min_values) {
      LSerror :: addErrorCode('LSformRule_numberOfValues_01');
      return;
    }

    $count = count($value);
    if (!is_null($min_values) && $count < $min_values)
      throw new LSformRuleException(
        getFData(
          ngettext(
            'At least one value is required.',
            'At least %{min} values are required.',
            $min_values
          ),
          $count
        )
      );

    if (!is_null($max_values) && $count > $max_values)
      throw new LSformRuleException(
        getFData(
          ngettext(
            'Maximum one value is allowed.',
            'Maximum %{max} values are allowed.',
            $count
          ),
          $max_values
        )
      );
    return True;
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSformRule_numberOfValues_01',
___("LSformRule_numberOfValues: Parameter max could not be lower than parameter min.")
);
