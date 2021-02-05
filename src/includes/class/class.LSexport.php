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
LSsession::loadLSclass('LSioFormat');

/**
 * Manage export LSldapObject
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSexport extends LSlog_staticLoggerClass {

  /**
   * Export objects
   *
   * @param[in] $LSobject LSldapObject An instance of the object type
   * @param[in] $ioFormat string The LSioFormat name
   * @param[in] $stream resource|null The output stream (optional, default: STDOUT)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True on success, False otherwise
   */
  public static function export($object, $ioFormat, $stream=null) {
    // Load LSobject
    if (is_string($object)) {
      if (!LSsession::loadLSobject($object, true)) {  // Load with warning
        return false;
      }
      $object = new $object();
    }

    // Validate ioFormat
    if(!$object -> isValidIOformat($ioFormat)) {
      LSerror :: addErrorCode('LSexport_01', $ioFormat);
      return false;
    }

    // Create LSioFormat object
    $ioFormat = new LSioFormat($object -> type, $ioFormat);
    if (!$ioFormat -> ready()) {
      LSerror :: addErrorCode('LSexport_02');
      return false;
    }

    // Load LSsearch class (with warning)
    if (!LSsession :: loadLSclass('LSsearch', null, true)) {
      return false;
    }

    // Search objects
    $search = new LSsearch($object -> type, 'LSexport');
    $search -> run();

    // Retreive objets
    $objects = $search -> listObjects();
    if (!is_array($objects)) {
      LSerror :: addErrorCode('LSexport_03');
      return false;
    }
    self :: log_debug(count($objects)." object(s) found to export");

    // Export objects using LSioFormat object
    if (!$ioFormat -> exportObjects($objects, $stream)) {
      LSerror :: addErrorCode('LSexport_04');
      return false;
    }
    self :: log_debug("export(): objects exported");
    return true;
  }

  /**
   * CLI export command
   *
   * @param[in] $command_args array Command arguments:
   *   - Positional arguments:
   *     - LSobject type
   *     - LSioFormat name
   *   - Optional arguments:
   *     - -o|--output: Output path ("-" == stdout, default: "-")
   *
   * @retval boolean True on succes, false otherwise
   **/
  public static function cli_export($command_args) {
    $objType = null;
    $ioFormat = null;
    $output = '-';
    for ($i=0; $i < count($command_args); $i++) {
      switch ($command_args[$i]) {
        case '-o':
        case '--output':
          $output = $command_args[++$i];
          break;
        default:
          if (is_null($objType)) {
            $objType = $command_args[$i];
          }
          elseif (is_null($ioFormat)) {
            $ioFormat = $command_args[$i];
          }
          else
            LScli :: usage("Invalid $arg parameter.");
      }
    }

    if (is_null($objType) || is_null($ioFormat))
      LScli :: usage('You must provide LSobject type, ioFormat.');

    // Check output
    if ($output != '-' && file_exists($output))
      LScli :: usage("Output file '$output' already exists.");

    // Open output stream
    $stream = fopen(($output=='-'?'php://stdout':$output), "w");
    if ($stream === false)
      LSlog :: fatal("Fail to open output file '$output'.");

    // Run export
    return self :: export($objType, $ioFormat, $stream);
  }

  /**
   * Args autocompleter for CLI export command
   *
   * @param[in] $command_args array List of already typed words of the command
   * @param[in] $comp_word_num int The command word number to autocomplete
   * @param[in] $comp_word string The command word to autocomplete
   * @param[in] $opts array List of global available options
   *
   * @retval array List of available options for the word to autocomplete
   **/
  public static function cli_export_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
    $opts = array_merge($opts, array ('-o', '--output'));

    // Handle positional args
    $objType = null;
    $objType_arg_num = null;
    $ioFormat = null;
    $ioFormat_arg_num = null;
    for ($i=0; $i < count($command_args); $i++) {
      if (!in_array($command_args[$i], $opts)) {
        // If object type not defined
        if (is_null($objType)) {
          // Defined it
          $objType = $command_args[$i];
          LScli :: unquote_word($objType);
          $objType_arg_num = $i;

          // Check object type exists
          $objTypes = LScli :: autocomplete_LSobject_types($objType);

          // Load it if exist and not trying to complete it
          if (in_array($objType, $objTypes) && $i != $comp_word_num) {
            LSsession :: loadLSobject($objType, false);
          }
        }
        elseif (is_null($ioFormat)) {
          $ioFormat = $command_args[$i];
          LScli :: unquote_word($ioFormat);
          $ioFormat_arg_num = $i;
        }
      }
    }

    // If objType not already choiced (or currently autocomplete), add LSobject types to available options
    if (!$objType || $objType_arg_num == $comp_word_num)
      $opts = array_merge($opts, LScli :: autocomplete_LSobject_types($comp_word));

    // If dn not alreay choiced (or currently autocomplete), try autocomplete it
    elseif (!$ioFormat || $ioFormat_arg_num == $comp_word_num)
      $opts = array_merge($opts, LScli :: autocomplete_LSobject_ioFormat($objType, $comp_word));

    return LScli :: autocomplete_opts($opts, $comp_word);
  }

}
LSerror :: defineError('LSexport_01',
___("LSexport: input/output format %{format} invalid.")
);
LSerror :: defineError('LSexport_02',
___("LSexport: Fail to initialize input/output driver.")
);
LSerror :: defineError('LSexport_03',
___("LSexport: Fail to load objects's data to export from LDAP directory.")
);
LSerror :: defineError('LSexport_04',
___("LSexport: Fail to export objects's data.")
);

// Defined CLI commands functions only on CLI context
if (php_sapi_name() != 'cli')
    return true;  // Always return true to avoid some warning in log

// LScli
LScli :: add_command(
    'export',
    array('LSexport', 'cli_export'),
    'Export LSobject',
    '[object type] [ioFormat name] -o /path/to/output.file',
    array(
    '   - Positional arguments :',
    '     - LSobject type',
    '     - LSioFormat name',
    '',
    '   - Optional arguments :',
    '     - -o|--output:  The output file path. Use "-" for STDOUT (optional, default: "-")',
  ),
  true,
  array('LSexport', 'cli_export_args_autocompleter')
);
