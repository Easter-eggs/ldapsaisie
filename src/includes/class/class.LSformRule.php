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

  // CLI parameters autocompleters
  //
  // This array accept as key the parameter name and as value a callable
  // to autocomplete the parameter value. This callable will receive as
  // first parameter the prefix of the parameter value already enter by
  // user and must return
  protected static $cli_params_autocompleters = array();

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
      return array(
        getFData(_('Invalid syntax checking configuration: unknown rule %{rule}.'), $rule_name)
      );
    }

    $errors = false;
    try {
      if (! $rule_class :: validate_one_by_one) {
        if (!$rule_class :: validate($values, $options, $formElement))
          throw new LSformRuleException();
      }
      else {
        foreach ($values as $value) {
          if (!$rule_class :: validate($value, $options, $formElement))
            throw new LSformRuleException();
        }
      }
    }
    catch (LSformRuleException $e) {
      $errors = $e->errors;
      $msg = LSconfig :: get('msg', null, null, $options);
      if ($msg || !$errors) {
        $errors[] = ($msg?__($msg):_('Invalid value'));
      }
    }
    return ($errors?$errors:true);
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

  /**
   * CLI test_form_rule command
   *
   * @param[in] $command_args array Command arguments :
   *   - Positional arguments :
   *     - LSformRule type
   *     - values to test
   *   - Optional arguments :
   *     - -p|--param: LSformRule parameters (format: param=value)
   *
   * @retval boolean True on success, false otherwise
   **/
  public static function cli_test_form_rule($command_args) {
    $rule_name = null;
    $values = array();
    $params = array();
    for ($i=0; $i < count($command_args); $i++) {
      LScli :: unquote_word($command_args[$i]);
      if (in_array($command_args[$i], array('-p', '--param'))) {
        $i++;
        LScli :: unquote_word($command_args[$i]);
        $param_parts = explode('=', $command_args[$i]);
        if (count($param_parts) != 2)
          LScli :: usage('Invalid parameter string ('.$command_args[$i].').');
        if (array_key_exists($param_parts[0], $params))
          LScli :: usage('Parameter "'.$param_parts[0].'" already specified.');
        $params[$param_parts[0]] = $param_parts[1];
      }
      else if (is_null($rule_name)) {
        $rule_name = $command_args[$i];
      }
      else {
        $values[] = $command_args[$i];
      }
    }

    if (is_null($rule_name) || empty($values))
      LScli :: usage('You must provide LSformRule type and at least one value to test.');

    self :: log_trace("test_form_rule($rule_name): params=".varDump($params));
    $formElement = null;
    $errors = self :: validate_values($rule_name, $values, array('params' => $params), $formElement);
    if (is_array($errors)) {
      print "Test triggered errors :\n - ".implode("\n - ", $errors)."\n";
      return false;
    }
    else {
      print "No error detected in provided values.\n";
    }
    return true;
  }

  /**
   * Args autocompleter for CLI test_form_rule command
   *
   * @param[in] $command_args array List of already typed words of the command
   * @param[in] $comp_word_num int The command word number to autocomplete
   * @param[in] $comp_word string The command word to autocomplete
   * @param[in] $opts array List of global available options
   *
   * @retval array List of available options for the word to autocomplete
   **/
  public static function cli_test_form_rule_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
    $opts = array_merge($opts, array('-p', '--param'));

    // Handle positional args
    $rule_name = null;
    $rule_class = null;
    $rule_name_arg_num = null;
    $params = array();
    for ($i=0; $i < count($command_args); $i++) {
      switch ($command_args[$i]) {
        case '-p':
        case '--params':
          $i++;
          $quote_char = LScli :: unquote_word($command_args[$i]);
          $param_parts = explode('=', $command_args[$i]);
          if (count($param_parts) > 2)
            return;
          $params[$i] = array(
            'name' => $param_parts[0],
            'value' => (isset($param_parts[1])?$param_parts[1]:null),
            'quote_char' => $quote_char,
          );
          break;

        default:
          // If rule name not defined
          if (is_null($rule_name)) {
            // Defined it
            $rule_name_quote_char = LScli :: unquote_word($command_args[$i]);
            $rule_name = $command_args[$i];
            LScli :: unquote_word($rule_name);
            $rule_name_arg_num = $i;

            // Check rule type exists
            $rule_names = LScli :: autocomplete_LSformRule_name($rule_name);

            // Load it if exist and not trying to complete it
            if (in_array($rule_name, $rule_names) && $i != $comp_word_num) {
              $rule_class = "LSformRule_$rule_name";
              LSsession :: loadLSclass($rule_class, null, false);
            }
          }

          // Otherwise, its value to test: can't complete it
      }
    }
    self :: log_debug("rule type :'$rule_name' (#$rule_name_arg_num, class=$rule_class)");
    self :: log_debug("params :".varDump($params));

    // If rule name not already choiced (or currently autocomplete), add LSformRule types to available options
    if (!$rule_name || $rule_name_arg_num == $comp_word_num)
      $opts = array_merge($opts, LScli :: autocomplete_LSformRule_name($comp_word, $rule_name_quote_char));

    else if ($rule_class && array_key_exists($comp_word_num, $params) && class_exists($rule_class)) {
      if (is_null($params[$comp_word_num]['value'])) {
        // Auto-complete parameter name
        self :: log_debug("Auto-complete parameter name with prefix=".$params[$comp_word_num]['name']);
        return $rule_class :: cli_test_form_rule_param_name_autocompleter(
          $params[$comp_word_num]['name'], $params[$comp_word_num]['quote_char']
        );
      }
      else {
        // Auto-complete param value
        self :: log_debug("Auto-complete parameter ".$params[$comp_word_num]['name']." value with prefix=".$params[$comp_word_num]['value']);
        return $rule_class :: cli_test_form_rule_param_value_autocompleter(
          $params[$comp_word_num]['name'], $params[$comp_word_num]['value'], $params[$comp_word_num]['quote_char']
        );
      }
    }

    return LScli :: autocomplete_opts($opts, $comp_word);
  }

  /**
   * Args autocompleter for parameter name of CLI test_form_rule command
   *
   * @param[in] $prefix string Parameter name prefix (optional, default=empty string)
   * @param[in] $quote_char $quote_char string Quote character (optional, default=empty string)
   *
   * @retval array List of available options for the word to autocomplete
   **/
  public static function cli_test_form_rule_param_name_autocompleter($prefix='', $quote_char='') {
    $opts = LScli :: autocomplete_opts(array_keys(static :: $cli_params_autocompleters), $prefix);
    self :: log_debug("cli_test_form_rule_param_name_autocompleter($prefix): opts = ".varDump($opts));
    for($i=0; $i<count($opts); $i++)
      $opts[$i] .= '=';
    return LScli :: autocomplete_opts($opts, $prefix, true, $quote_char);
  }

  /**
   * Args autocompleter for parameter name of CLI test_form_rule command
   *
   * @param[in] $param_name string The parameter name
   * @param[in] $prefix string Parameter name prefix (optional, default=empty string)
   * @param[in] $quote_char $quote_char string Quote character (optional, default=empty string)
   *
   * @retval array List of available options for the word to autocomplete
   **/
  public static function cli_test_form_rule_param_value_autocompleter($param_name, $prefix='', $quote_char='') {
    if (
      !array_key_exists($param_name, static :: $cli_params_autocompleters) ||
      !is_callable(static :: $cli_params_autocompleters[$param_name])
    )
      return;
    $opts = call_user_func(static :: $cli_params_autocompleters[$param_name], $prefix);
    if (!is_array($opts))
      return;
    for($i=0; $i<count($opts); $i++)
      $opts[$i] = $param_name."=".$opts[$i];
    self :: log_debug("Options: ".varDump($opts));
    return LScli :: autocomplete_opts($opts, "$param_name=$prefix", true, $quote_char);
  }

}

class LSformRuleException extends Exception {

  public $errors = array();

  public function __construct($errors=array(), $code = 0, Throwable $previous = null) {
    $this -> errors = ensureIsArray($errors);
    $message = _("Invalid value");
    if ($this -> errors)
      $message .= ': '.implode(", ", $this -> errors);
    parent::__construct($message, $code, $previous);
  }
}

/**
 * Error Codes
 **/
LSerror :: defineError('LSformRule_01',
___("LSformRule_%{type}: Parameter %{param} is not found.")
);

// LScli
LScli :: add_command(
    'test_form_rule',
    array('LSformRule', 'cli_test_form_rule'),
    'Test LSformRule',
    '[LSformRule type] [-p param1=value] [value1] [value2]',
    array(
      '   - Positional arguments :',
      '     - LSformRule type',
      '     - values to test',
      '',
      '   - Optional arguments :',
      '     -p|--param    LSformRule parameter using format:',
      '                     param_name=param_value',
      '                   Multiple parameters could be specified by using this',
      '                   optional argument multiple time',
    ),
    true,
    array('LSformRule', 'cli_test_form_rule_args_autocompleter')
);
