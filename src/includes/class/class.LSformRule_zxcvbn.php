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

use ZxcvbnPhp\Zxcvbn;
LSsession :: includeFile(
  LSconfig :: get(
    'params.zxcvbn_autoload_path', 'Zxcvbn/autoload.php',
    'string', $options
  ), true
);

/**
 * Rule to validate password using ZXCVBN-PHP lib
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_zxcvbn extends LSformRule {

  // CLI parameters autocompleters
  protected static $cli_params_autocompleters = array(
    'minScore' => array('LScli', 'autocomplete_int'),
    'userDataAttrs' => null,
    'showWarning' => array('LScli', 'autocomplete_bool'),
    'showSuggestions' => array('LScli', 'autocomplete_bool'),
    'zxcvbn_autoload_path' => null,
  );

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
  public static function validate($value, $options=array(), &$formElement) {
    $zxcvbn = new Zxcvbn();
    $userData = array();
    $userDataAttrs = LSconfig :: get('params.userDataAttrs', array(), 'array', $options);
    if ($userDataAttrs) {
      foreach ($userDataAttrs as $attr) {
        $attr_values = $formElement -> attr_html -> attribute -> ldapObject -> getValue($attr, false, array());
        if (is_empty($attr_values)) continue;
        foreach($attr_values as $attr_value)
          if (!in_array($attr_value, $userData))
            $userData[] = $attr_value;
      }
    }
    self :: log_trace("User data: ".varDump($userData));
    $result = $zxcvbn->passwordStrength($value, $userData);
    self :: log_trace("Zxcvbn result: ".varDump($result));
    self :: log_debug("Zxcvbn score: ".$result['score']);

    $minScore = LSconfig :: get('params.minScore', 4, 'int', $options);
    if($result['score'] >= $minScore) {
      return True;
    }

    $errors = array();
    if (
      $result['feedback']['warning'] &&
      LSconfig :: get('params.showWarning', true, 'bool', $options)
    ) {
      $errors[] = $result['feedback']['warning'];
    }
    if (!$errors)
      $errors[] = _('The security of this password is too weak.');

    if (
      is_array($result['feedback']['suggestions']) &&
      LSconfig :: get('params.showSuggestions', true, 'bool', $options)
    ) {
      foreach($result['feedback']['suggestions'] as $msg)
        if ($msg)
          $errors[] = $msg;
    }

    throw new LSformRuleException($errors);
  }

}
