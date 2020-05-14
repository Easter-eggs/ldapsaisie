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
   * @param[in] $command        string        The CLI command name (required)
   * @param[in] $handler        callable      The CLI command handler (must be callable, required)
   * @param[in] $short_desc     string|false  A short description of what this command does (required)
   * @param[in] $usage_args     string|false  A short list of commands available arguments show in usage message
   *                                          (optional, default: false)
   * @param[in] $long_desc      string|false  A long description of what this command does (optional, default: false)
   * @param[in] $need_ldap_con  boolean       Permit to define if this command need connection to LDAP server (optional,
   *                                          default: true)
   * @param[in] $override       boolean       Allow override if a command already exists with the same name (optional,
   *                                          default: false)
   **/
  public static function add_command($command, $handler, $short_desc, $usage_args=false, $long_desc=false,
                                     $need_ldap_con=true, $override=false) {
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
    $command = false;
    $command_args = array();
    self :: log_debug("handle_args :\n".varDump($argv));
    for ($i=1; $i < count($argv); $i++) {
      if (array_key_exists($argv[$i], self :: $commands)) {
        if (!$command)
                self :: $current_command = $command = $argv[$i];
        else
                self :: usage(_("Only one command could be executed !"));
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
      if (!class_exists('LSldap') || !LSldap :: isConnected())
        if (!LSsession :: LSldapConnect())
          self :: log_fatal('Fail to connect to LDAP server.');
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

}

/*
 * Error Codes
 */
LSerror :: defineError('LScli_01',
_("LScli : The CLI command '%{command}' already exists.")
);
LSerror :: defineError('LScli_02',
_("LScli : The CLI command '%{command}' handler is not callable.")
);
