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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Base d'une règle de validation de données
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule extends LSlog_staticLoggerClass {

  // Validate values one by one or all together
  const validate_one_by_one = True;

 /**
  * Validate form element values with specified rule
  *
  * @param  mixed $rule_name The LSformRule name
  * @param  mixed $value The values to validate
  * @param array $options Validation options
  * @param object $formElement The attached LSformElement object
  *
  * @return boolean True if value is valid, False otherwise
  */
  public static function validate_values($rule_name, $values, $options=array(), &$formElement) {
    // Compute PHP class name of the rule
    $rule_class = "LSformRule_".$rule_name;

    // Load PHP class (with error if fail)
    if (!LSsession :: loadLSclass($rule_class)) {
      LSerror :: addErrorCode('LSformRule_02', $rule_name);
      return False;
    }

    if (! $rule_class :: validate_one_by_one)
      return $rule_class :: validate($values, $options, $formElement);

    foreach ($values as $value) {
      if (!$rule_class :: validate($value, $options, $formElement))
        return False;
    }
    return True;
  }

 /**
  * Validate form element value
  *
  * @param  mixed $value The value to validate
  * @param array $options Validation options
  * @param object $formElement The attached LSformElement object
  *
  * @return boolean True if value is valid, False otherwise
  */
  public static function validate($value, $options=array(), &$formElement) {
    return false;
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSformRule_01',
___("LSformRule_%{type}: Parameter %{param} is not found.")
);
LSerror :: defineError('LSformRule_02',
___("LSformRule: Unknown rule type %{type}.")
);
