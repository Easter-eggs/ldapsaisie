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
 * CLI Manager for LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LScli {

  // Configured commands
  private static $commands = array();

  // Store current executed command
  private static $current_command = null;

  /**
   * Add a CLI command
   *
   * @param[in] $command    string        The CLI command name (required)
   * @param[in] $handler    callable      The CLI command handler (must be callable, required)
   * @param[in] $short_desc string|false  A short description of what this command does (required)
   * @param[in] $usage_args string|false  A short list of commands available arguments show in usage message (optional, default: false)
   * @param[in] $long_desc  string|false  A long description of what this command does (optional, default: false)
   * @param[in] $override   boolean       Allow override if a command already exists with the same name (optional, default: false)
   **/
  public static function add_command($command, $handler, $short_desc, $usage_args=false, $long_desc=false, $override=false) {
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
    echo "  -q|--quiet        Quiet mode\n";
    echo "  -d|--debug        Debug mode\n";
    echo "  -C|--console      Log on console\n";
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
    global $argv;
    $log_level = 'INFO';
    $ldap_server_id = false;
    $command = false;
    $command_args = array();
    LSlog :: debug("handle_args :\n".varDump($argv));
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
          case '-q':
          case '--quiet':
            $log_level = 'WARNING';
            break;
          case '-C':
          case '--console':
            LSlog :: logOnConsole();
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

    if (!$command) {
      LSlog :: debug("LScli :: handle_args() : no detected command => show usage");
      self :: usage();
    }

    // Connect to LDAP server
    if ($ldap_server_id === false && !LSsession :: setLdapServer(0))
      LSlog :: fatal('Fail to select first LDAP server.');
    if (!LSsession :: LSldapConnect())
      LSlog :: fatal('Fail to connect to LDAP server.');

    // Run command
    LSlog :: debug('Run '.basename($argv[0])." command $command with argument(s) '".implode("', '", $command_args)."'");
    try {
      $result = call_user_func(self :: $commands[$command]['handler'], $command_args);

      exit($result?0:1);
    }
    catch(Exception $e) {
      LSlog :: exception($e, "An exception occured running CLI command $command");
      exit(1);
    }
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
