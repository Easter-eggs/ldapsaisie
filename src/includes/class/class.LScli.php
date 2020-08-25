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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * CLI Manager for LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LScli extends LSlog_staticLoggerClass {

  // Configured commands
  private static $commands = array();

  // Store current executed command
  private static $current_command = null;

  /**
   * Add a CLI command
   *
   * @param[in] $command            string          The CLI command name (required)
   * @param[in] $handler            callable        The CLI command handler (must be callable, required)
   * @param[in] $short_desc         string|false    A short description of what this command does (required)
   * @param[in] $usage_args         string|false    A short list of commands available arguments show in usage message
   *                                                (optional, default: false)
   * @param[in] $long_desc          string|false    A long description of what this command does (optional, default: false)
   * @param[in] $need_ldap_con      boolean         Permit to define if this command need connection to LDAP server (optional,
   *                                                default: true)
   * @param[in] $args_autocompleter callable|null   Allow override if a command already exists with the same name (optional,
   * @param[in] $override       boolean             Allow override if a command already exists with the same name (optional,
   *                                                default: false)
   *
   * @retval void
   **/
  public static function add_command($command, $handler, $short_desc, $usage_args=false, $long_desc=false,
                                     $need_ldap_con=true, $args_autocompleter=null, $override=false) {
    if (array_key_exists($command, self :: $commands) && !$override) {
      LSerror :: addErrorCode('LScli_01', $command);
      return False;
    }

    if (!is_callable($handler)) {
      LSerror :: addErrorCode('LScli_02', $command);
      return False;
    }

    self :: $commands[$command] = array (
      'handler' => $handler,
      'short_desc' => $short_desc,
      'usage_args' => $usage_args,
      'long_desc' => $long_desc,
      'need_ldap_con' => boolval($need_ldap_con),
      'args_autocompleter' => $args_autocompleter,
    );
    return True;
  }

  /**
   * Show usage message
   *
   * @param[in] $error string|false Error message to display before usage message (optional, default: false)
   * @retval void
   **/
  public static function usage($error=false) {
    global $argv;

    if ($error)
      echo "$error\n\n";

    echo "Usage : ".basename($argv[0])." [-h] [-qdC] command\n";
    echo "  -h                Show this message\n";
    echo "  -q|--quiet        Quiet mode: nothing log on console (but keep other logging handler)\n";
    echo "  -d|--debug        Debug mode (set log level to DEBUG, default: WARNING)\n";
    echo "  -v|--verbose      Verbose mode (set log level to INFO, default: WARNING)\n";
    echo "  --trace           Trace mode (set log level to TRACE, default: WARNING)\n";
    echo "  -C|--console      Log on console with same log level as other handlers (otherwise, log only errors)\n";
    echo "  -S|--ldap-server  Connect to a specific LDAP server: you could specify a LDAP\n";
    echo "                    server by its declaration order in configuration (default:\n";
    echo "                    first one).\n";
    echo "  -L|--load-class   Load specific class to permit access to its CLI commands\n";
    echo "  -A|--load-addons  Load specific addon to permit access to its CLI commands\n";
    echo "  command       Command to run\n";
    echo "\n";
    echo "Available commands :\n";

    foreach (self :: $commands as $command => $info) {
      if (self :: $current_command and self :: $current_command != $command)
        continue;
      echo "  $command : ".$info['short_desc']."\n";
      echo "    ".basename($argv[0])." $command ".($info['usage_args']?$info['usage_args']:'')."\n";
      if ($info['long_desc']) {
        if (is_array($info['long_desc']))
          $info['long_desc'] = implode("\n", $info['long_desc']);
        echo "\n    ".str_replace("\n", "\n    ", wordwrap($info['long_desc']))."\n";
      }
      echo "\n";
    }
    if (empty(self :: $commands))
      echo "  Currently no available command is declared.\n";

    exit(($error?1:0));
  }

  /**
   * Handle CLI arguments and run command (if provided)
   *
   * @retval void
   **/
  public static function handle_args() {
    if (php_sapi_name() != "cli") {
      self :: log_fatal('Try to use LScli :: handle_args() in non-CLI context.');
      return;
    }
    global $argv;
    $log_level = 'WARNING';
    $console_log = false;
    $quiet = false;
    $ldap_server_id = false;
    $ldap_server_subDn = false;
    $command = false;
    $command_args = array();
    self :: log_debug("handle_args :\n".varDump($argv));
    for ($i=1; $i < count($argv); $i++) {
      if (array_key_exists($argv[$i], self :: $commands)) {
        if (!$command)
                self :: $current_command = $command = $argv[$i];
        else
                $command_args[] = $argv[$i];
      }
      else {
        switch($argv[$i]) {
          case '-h':
          case '--help':
            self :: usage();
            break;
          case '-d':
          case '--debug':
            $log_level = 'DEBUG';
            break;
          case '-v':
          case '--verbose':
            $log_level = 'INFO';
            break;
          case '--trace':
            $log_level = 'TRACE';
            break;
          case '-q':
          case '--quiet':
            $quiet = true;
            break;
          case '-C':
          case '--console':
            $console_log = true;
            break;
          case '-S':
          case '--ldap-server':
            $i++;
            $ldap_server_id = intval($argv[$i]);
            if(!LSsession :: setLdapServer($ldap_server_id))
              self :: usage("Fail to select LDAP server #$ldap_server_id.");
            break;
          case '--sub-dn':
            $i++;
            $ldap_server_subDn = $argv[$i];
            break;
          case '-L':
          case '--load-class':
            $i++;
            $class = $argv[$i];
            if(!LSsession :: loadLSclass($class))
              self :: usage("Fail to load class '$class'.");
            break;
          case '-A':
          case '--load-addon':
            $i++;
            $addon = $argv[$i];
            if(!LSsession :: loadLSaddon($addon))
              self :: usage("Fail to load addon '$addon'.");
            break;
          case '--':
            $command_args = array_merge($command_args, array_slice($argv, $i));
            $i = count($argv);
            break;
          default:
            if ($command)
              $command_args[] = $argv[$i];
            else
              self :: usage(
                getFData(_("Invalid parameter \"%{parameter}\".\nNote: Command's parameter/argument must be place after the command."), $argv[$i])
              );
        }
      }
    }

    // Set log level
    LSlog :: setLevel($log_level);

    // Enable/disable log on console
    if ($quiet)
      // Quiet mode: disable log on console
      LSlog :: disableLogOnConsole();
    else
      // Enable console log:
      // - if $console_log: use same log level as other handlers
      // - otherwise: log only errors
      LSlog :: logOnConsole(($console_log?$log_level:'ERROR'));

    // If no LDAP server subDn selected, check if current LDAP server need it,
    // and select the first one
    if (!$ldap_server_subDn) {
      self :: need_ldap_con();
      $subDns = LSsession :: getSubDnLdapServer();
      if (is_array($subDns)) {
        asort($subDns);
        $ldap_server_subDn = key($subDns);
      }
    }

    // Select LDAP server subDn (if need)
    if ($ldap_server_subDn) {
      self :: need_ldap_con();
      if(!LSsession :: setSubDn($ldap_server_subDn))
        self :: usage("Fail to select sub DN '$ldap_server_subDn'.");
    }

    if (!$command) {
      self :: log_debug("LScli :: handle_args() : no detected command => show usage");
      self :: usage();
    }

    // Select LDAP server (if not already done with -S/--ldap-server parameter)
    if ($ldap_server_id === false && !LSsession :: setLdapServer(0))
      self :: log_fatal('Fail to select first LDAP server.');

    // Run command
    self :: run_command($command, $command_args);
  }

  /**
   * Run command
   *
   * @param[in] $command string The command name
   * @param[in] $command string The command arguments (optional, default: array())
   * @param[in] $exit boolean   If true, function will exit after command execution (optional, default: true)
   *
   * @retval void|boolean If $exit is False, return boolean casted command return
   **/
  public static function run_command($command, $command_args=array(), $exit=true) {
    if (php_sapi_name() != "cli") {
      self :: log_fatal('Try to use LScli :: run_command() in non-CLI context.');
      return;
    }
    if (!array_key_exists($command, self :: $commands)) {
      self :: log_warning("LScli :: run_command() : invalid command '$command'.");
      return false;
    }

    // Connect to LDAP server (if command need)
    if (self :: $commands[$command]['need_ldap_con']) {
      self :: need_ldap_con();
    }

    // Run command
    self :: log_debug("Run command $command with argument(s) '".implode("', '", $command_args)."'");
    try {
      $result = call_user_func(self :: $commands[$command]['handler'], $command_args);

      if ($exit)
        exit($result?0:1);
      return boolval($result);
    }
    catch(Exception $e) {
      LSlog :: exception($e, "An exception occured running CLI command $command");
    }
    if ($exit)
      exit(1);
    return false;
  }

  /**
   * Start LDAP connection (if not already connected)
   *
   * @retval void
   **/
  public static function need_ldap_con() {
    // Connect to LDAP server (if not already the case)
    if (!class_exists('LSldap') || !LSldap :: isConnected()) {
      if (!LSsession :: LSldapConnect())
        self :: log_fatal('Fail to connect to LDAP server.');
    }
  }

  /**
   * Run external command
   *
   * @param[in] $command              string|array  The command. It's could be an array of the command with its arguments.
   * @param[in] $data_stdin           string|null   The command arguments (optional, default: null)
   * @param[in] $escape_command_args  boolean       If true, the command will be escaped (optional, default: true)
   *
   * @retval false|array An array of return code, stdout and stderr result or False in case of fatal error
   **/
  public static function run_external_command($command, $data_stdin=null, $escape_command_args=true) {
    if (array($command))
      $command = implode(' ', $command);
    if ($escape_command_args)
      $command = escapeshellcmd($command);
    self :: log_debug("Run external command: '$command'");
    $descriptorspec = array(
      0 => array("pipe", "r"),  // stdin
      1 => array("pipe", "w"),  // stdout
      2 => array("pipe", "w"),  // stderr
    );
    $process = proc_open($command, $descriptorspec, $pipes);

    if (!is_resource($process)) {
      self :: log_error("Fail to run external command: '$command'");
      return false;
    }

    if (!is_null($data_stdin)) {
      fwrite($pipes[0], $data_stdin);
    }
    fclose($pipes[0]);

    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $return_value = proc_close($process);

    if (!empty($stderr) || $return_value != 0)
      self :: log_error("Externan command error:\nCommand : $command\nStdout :\n$stdout\n\n - Stderr :\n$stderr");
    else
      self :: log_debug("Externan command result:\n\tCommand : $command\n\tReturn code: $return_value\n\tOutput:\n\t\t- Stdout :\n$stdout\n\n\t\t- Stderr :\n$stderr");

    return array($return_value, $stdout, $stderr);
  }

  /**
   * CLI helper to ask for user confirmation
   *
   * @param[in] $question string The confirmation question (optional, default: "Are you sure?")
   *
   * @retval boolean True if user confirmed, false otherwise
   **/
  public static function confirm($question=null) {
    if (is_null($question))
      $question = "Are you sure?";
    echo "\n$question  Type 'yes' to continue: ";
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    if(trim($line) != 'yes'){
            echo "User cancel\n";
            return false;
    }
    echo "\n";
    return true;
  }

  /**
   * CLI command to handle BASH command autocompleter
   *
   * @param[in] $command_args array Command arguments
   *
   * @retval boolean True on succes, false otherwise
   **/
  public static function bash_autocomplete($command_args) {
    if (count($command_args) < 3)
      return;
    $comp_word_num = intval($command_args[0]);
    if ($comp_word_num <= 0) return;
    if ($command_args[1] != '--') return;

    $comp_words = array_slice($command_args, 2);
    $comp_word = (isset($comp_words[$comp_word_num])?$comp_words[$comp_word_num]:'');
    self :: log_debug("bash_autocomplete: words = '".implode("', '", $comp_words)."' | word to complete = #$comp_word_num == '$comp_word'");

    // List available options
    $opts = array(
      '-h', '--help',
      '-d', '--debug',
      '-v', '--verbose',
      '--trace',
      '-q', '--quiet',
      '-C', '--console',
      '-S', '--ldap-server',
      '-L', '--load-class',
      '-A', '--load-addon',
    );

    // Detect if command already enter, if LDAP server is selected and load specified class/addon
    $command = null;
    $command_arg_num = null;
    $command_args = array();
    $ldap_server_id = false;
    $ldap_server_subDn = false;
    for ($i=1; $i < count($comp_words); $i++) {
      $unescaped_comp_word = $comp_words[$i];
      self :: unquote_word($unescaped_comp_word);
      if (array_key_exists($unescaped_comp_word, self :: $commands)) {
        if (!$command) {
          $command = $comp_words[$i];
          self :: unquote_word($command);
          $command_arg_num = $i;
        }
        else
          $command_args[] = $comp_words[$i];
      }
      else {
        switch($unescaped_comp_word) {
          case '-S':
          case '--ldap-server':
            $i++;
            if ($i == $comp_word_num) {
              return self :: return_bash_autocomplete_list(
                self :: autocomplete_opts(array_keys(LSconfig :: get("ldap_servers", array())), $comp_word)
              );
            }
            if (!isset($comp_words[$i]))
              break;
            if (isset($comp_words[$i])) {
              $ldap_server_id = intval($comp_words[$i]);
              self :: unquote_word($ldap_server_id);
              if(!LSsession :: setLdapServer($ldap_server_id))
                self :: usage("Fail to select LDAP server #$ldap_server_id.");
            }
            break;
          case '--sub-dn':
            $i++;
            if ($i == $comp_word_num) {
              self :: need_ldap_con();
              $subDns = LSsession :: getSubDnLdapServer();
              if (!is_array($subDns))
                $subDns = array();
              return self :: return_bash_autocomplete_list(
                self :: autocomplete_opts(array_keys($subDns), $comp_word)
              );
            }
            if (!isset($comp_words[$i]))
              break;
            $ldap_server_subDn = $comp_words[$i];
            self :: unquote_word($ldap_server_subDn);
            self :: need_ldap_con();
            if(!LSsession :: setSubDn($ldap_server_subDn))
              self :: usage("Fail to select sub DN '$ldap_server_subDn'.");
            break;
          case '-L':
          case '--load-class':
            $i++;
            if ($i == $comp_word_num) {
              return self :: return_bash_autocomplete_list(
                self :: autocomplete_class_name($comp_word)
              );
            }
            if (!isset($comp_words[$i]))
              break;
            $class = $comp_words[$i];
            self :: unquote_word($class);
            if(!LSsession :: loadLSclass($class))
              self :: usage("Fail to load class '$class'.");
            break;
          case '-A':
          case '--load-addon':
            $i++;
            if ($i == $comp_word_num) {
              return self :: return_bash_autocomplete_list(
                self :: autocomplete_addon_name($comp_word)
              );
            }
            if (!isset($comp_words[$i]))
              break;
            $addon = $comp_words[$i];
            self :: unquote_word($addon);
            if(!LSsession :: loadLSaddon($addon))
              self :: usage("Fail to load addon '$addon'.");
            break;
          default:
            if (!in_array($comp_words[$i], $opts)) {
              $command_args[] = $comp_words[$i];
            }
        }
      }
    }

    // Is no subDn selected, check if LDAP server has subDn and select the first one if true
    if (!$ldap_server_subDn) {
      self :: need_ldap_con();
      $subDns = LSsession :: getSubDnLdapServer();
      if (is_array($subDns)) {
        asort($subDns);
        $subDn = key($subDns);
        if(!LSsession :: setSubDn($subDn))
          self :: usage("Fail to select sub DN '$subDn'.");
        $opts[] = '--sub-dn';
      }
    }

    // If command set and args autocompleter defined, use it
    if ($command && is_callable(self :: $commands[$command]['args_autocompleter'])) {
      $command_comp_word_num = $comp_word_num-$command_arg_num-1;
      self :: log_debug("Run CLI command $command autocompleter with cmd args='".implode("', '", $command_args)."', comp word #$command_comp_word_num = '$comp_word'");
      return self :: return_bash_autocomplete_list(
        call_user_func(
          self :: $commands[$command]['args_autocompleter'],
          $command_args,
          $command_comp_word_num,
          $comp_word,
          $opts
        )
      );
    }

    // If command not already choiced, add commands name to available options list
    if (!$command)
      $opts = array_merge($opts, array_keys(self :: $commands));

    return self :: return_bash_autocomplete_list(
      self :: autocomplete_opts($opts, $comp_word, true)
    );
  }

  /**
   * Print list of available autocomplete options as required by BASH
   *
   * @param[in] $list mixed List of available autocomplete options if it's an array
   *
   * @retval boolean True if $list is an array, false otherwise
   **/
  public static function return_bash_autocomplete_list($list) {
    if (is_array($list)) {
      echo implode("\n", $list);
      return true;
    }
    return false;
  }

  /**
   * Autocomplete class name
   *
   * @param[in] $prefix string Class name prefix (optional, default=empty string)
   *
   * @retval array List of matched class names
   **/
  public static function autocomplete_class_name($prefix='') {
    $classes = array();
    $quote_char = self :: unquote_word($prefix);
    $regex = "/^class\.($prefix.*)\.php$/";
    foreach(array(LS_ROOT_DIR."/".LS_CLASS_DIR, LS_ROOT_DIR."/".LS_LOCAL_DIR."/".LS_CLASS_DIR) as $dir_path) {
      foreach (listFiles($dir_path, $regex) as $file) {
        $class = $file[1];
        if (!in_array($class, $classes))
          $classes[] = self :: quote_word($class, $quote_char);
      }
    }
    return $classes;
  }

  /**
   * Autocomplete addon name
   *
   * @param[in] $prefix string Addon name prefix (optional, default=empty string)
   *
   * @retval array List of matched addon names
   **/
  public static function autocomplete_addon_name($prefix='') {
    $addons = array();
    $quote_char = self :: unquote_word($prefix);
    $regex = "/^LSaddons\.($prefix.*)\.php$/";
    foreach(array(LS_ROOT_DIR."/".LS_ADDONS_DIR, LS_ROOT_DIR."/".LS_LOCAL_DIR."/".LS_ADDONS_DIR) as $dir_path) {
      foreach (listFiles($dir_path, $regex) as $file) {
        $addon = $file[1];
        if (!in_array($addon, $addons))
          $addons[] = self :: quote_word($addon, $quote_char);
      }
    }
    return $addons;
  }

  /**
   * Autocomplete options
   *
   * @param[in] $opts           array     Available options
   * @param[in] $prefix         string    Option name prefix (optional, default=empty string)
   * @param[in] $case_sensitive boolean   Set to false if options are case insensitive (optional, default=true)
   * @param[in] $quote_char     boolean   Quote character (optional, if not set, $prefix will be unquoted and its
   *                                      quote char (if detected) will be used to quote options)
   *
   * @retval array List of matched options
   **/
  public static function autocomplete_opts($opts, $prefix='', $case_sensitive=true, $quote_char='') {
    if (!is_string($prefix) || strlen($prefix)==0)
      return $opts;
    if (!$quote_char)
      $quote_char = self :: unquote_word($prefix);

    if (!$case_sensitive)
      $prefix = strtolower($prefix);
    $matched_opts = array();
    foreach($opts as $key => $opt) {
      if (!$case_sensitive)
        $opt = strtolower($opt);
      self :: unquote_word($opt);
      if (substr($opt, 0, strlen($prefix)) == $prefix)
        $matched_opts[] = LScli :: quote_word($opt, $quote_char);
    }
    self :: log_debug("autocomplete_opts(".implode('|', $opts).", $prefix, case ".($case_sensitive?"sensitive":"insensitive").") : matched opts: ".print_r($matched_opts, true));
    return $matched_opts;
  }

  /**
   * Autocomplete integer option
   *
   * @param[in] $prefix         string    Option prefix (optional, default=empty string)
   *
   * @retval array List of available options
   **/
  public static function autocomplete_int($prefix='') {
    $opts = array();
    for ($i=0; $i < 10; $i++) {
      $opts[] = "$prefix$i";
    }
    return $opts;
  }

  /**
   * Autocomplete LSobject type option
   *
   * @param[in] $prefix         string    Option prefix (optional, default=empty string)
   * @param[in] $case_sensitive boolean   Set to false if options are case insensitive (optional, default=true)
   * @param[in] $quote_char     boolean   Quote character (optional, if not set, $prefix will be unquoted and its
   *                                      quote char (if detected) will be used to quote options)
   *
   * @retval array List of available options
   **/
  public static function autocomplete_LSobject_types($prefix='', $case_sensitive=true, $quote_char='') {
    $subdn_config = LSconfig :: get('subDn', null, null, LSsession :: $ldapServer);
    if (is_array($subdn_config)) {
      $types = array();
      $currentSubDn = LSsession :: getTopDn();
      foreach($subdn_config as $name => $config) {
        if ($name=='LSobject') {
          self :: need_ldap_con();
          if (is_array($config)) {
            foreach($config as $objectType => $objectConf) {
              if (LSsession :: loadLSobject($objectType)) {
                if ($subdnobject = new $objectType()) {
                  $tbl = $subdnobject -> getSelectArray(NULL,LSsession::getRootDn(),NULL,NULL,false,NULL,array('onlyAccessible' => False));
                  if (is_array($tbl) && array_key_exists($currentSubDn, $tbl)) {
                    if (is_array($objectConf['LSobjects'])) {
                      foreach($objectConf['LSobjects'] as $type) {
                        if (!in_array($type, $types))
                          $types[] = $type;
                      }
                    }
                    break;
                  }
                }
              }
            }
          }
        }
        else if (isset($config['dn']) && $config['dn'] == $currentSubDn) {
          if (is_array($config['LSobjects'])) {
            foreach($config['LSobjects'] as $type) {
              if (!in_array($type, $types))
                $types[] = $type;
            }
          }
          break;
        }
      }
    }
    else {
      $types = LSconfig :: get('LSaccess', array(), null, LSsession :: $ldapServer);
    }
    return self :: autocomplete_opts($types, $prefix, $case_sensitive, $quote_char);
  }

  /**
   * Autocomplete LSobject DN option
   *
   * @param[in] $objType        string    LSobject type
   * @param[in] $prefix         string    Option prefix (optional, default=empty string)
   * @param[in] $case_sensitive boolean   Set to false if options are case insensitive (optional, default=true)
   * @param[in] $quote_char     boolean   Quote character (optional, if not set, $prefix will be unquoted and its
   *                                      quote char (if detected) will be used to quote options)
   *
   * @retval array List of available options
   **/
  public static function autocomplete_LSobject_dn($objType, $prefix='', $case_sensitive=true, $quote_char='') {
    if (!LSsession ::loadLSobject($objType, false))
      return array();

    // Make sure to unquote prefix
    if (!$quote_char && $prefix)
      $quote_char = self :: unquote_word($prefix);

    $rdn_attr = LSconfig :: get("LSobjects.$objType.rdn");
    if (!$rdn_attr || strlen($prefix) < (strlen($rdn_attr)+2) || substr($prefix, 0, (strlen($rdn_attr)+1)) != "$rdn_attr=")
      return array(LScli :: quote_word("$rdn_attr=", $quote_char));

    // Split prefix by comma to keep only RDN
    $prefix_parts = explode(',', $prefix);
    $prefix_rdn = $prefix_parts[0];

    // Search objects
    self :: need_ldap_con();
    $obj = new $objType();
    $objs = $obj -> listObjectsName("($prefix_rdn*)");
    if (is_array($objs)) {
      $dns = array_keys($objs);
      self :: log_debug("Matching $objType DNs with prefix '$prefix_rdn': ".implode(', ', $dns));
      return self :: autocomplete_opts($dns, $prefix, $case_sensitive, $quote_char);
    }
    return array(LScli :: quote_word("$rdn_attr=", $quote_char));
  }

  /**
   * Unquote a word
   *
   * @param[in] &$word string  Reference of the word to unquote
   *
   * @retval string The quote character or an empty string if word if not quoted
   */
  public static function unquote_word(&$word) {
    if (in_array($word[0], array('"', "'"))) {
      $quote_char = $word[0];
      $word = substr($word, 1);
      if ($word[strlen($word)-1] == $quote_char)
        $word = substr($word, 0, -1);
      return $quote_char;
    }
    return '';
  }

  /**
   * Quote a word
   *
   * @param[in] $word string  The word to quote
   * @param[in] $quote_char string  The quote character. If not defined or empty, the input word
   *                                will be returned unmodified.
   *
   * @retval string The quoted word
   */
  public static function quote_word($word, $quote_char) {
    if (!$quote_char) return $word;
    return $quote_char . str_replace($quote_char, "\\$quote_char", $word) . $quote_char;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LScli_01',
___("LScli : The CLI command '%{command}' already exists.")
);
LSerror :: defineError('LScli_02',
___("LScli : The CLI command '%{command}' handler is not callable.")
);

/*
 * Register LScli commands
 */
LScli :: add_command(
  'bash_autocomplete',
  array('LScli', 'bash_autocomplete'),
  'Handle BASH completion',
  '[arg num to autocomplete] -- [command args]',
  null,
  false
);
