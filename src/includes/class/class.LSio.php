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
 * Manage Import LSldapObject
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSio extends LSlog_staticLoggerClass {

  /**
   * Check if the form was posted by check POST data
   *
   * @param[in] $action string The action name used as POST validate flag value
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true if the form was posted, false otherwise
   */
  public static function isSubmit($action) {
    if (isset($_POST['validate']) && ($_POST['validate']==$action))
      return true;
    return;
  }


  /**
   * Retrieve the post file
   *
   * @retval mixed The path of the temporary file, false on error
   */
  public static function getPostFile() {
    if (is_uploaded_file($_FILES['importfile']['tmp_name'])) {
      $fp = fopen($_FILES['importfile']['tmp_name'], "r");
      $buf = fread($fp, filesize($_FILES['importfile']['tmp_name']));
      fclose($fp);
      $tmp_file = LS_TMP_DIR_PATH.'importfile'.'_'.rand().'.tmp';
      if (move_uploaded_file($_FILES['importfile']['tmp_name'],$tmp_file)) {
        LSsession :: addTmpFile($buf,$tmp_file);
      }
      return $tmp_file;
    }
    return false;
  }

  /**
   * Retreive POST data
   *
   * This method retrieve and format POST data.
   *
   * The POST data are return as an array containing :
   *  - LSobject : The LSobject type if this import
   *  - ioFormat : The IOformat name choose by user
   *  - justTry : Boolean defining wether the user has chosen to enable
   *              just try mode (no modification)
   *  - updateIfExists : Boolean defining wether the user has chosen to
   *                     allow update on existing object.
   *  - importfile : The path of the temporary file to import
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed Array of POST data, false on error
   */
  public static function getPostData() {
    if (isset($_REQUEST['LSobject']) && isset($_POST['ioFormat'])) {
      $file=self::getPostFile();
      if ($file) {
        return array (
          'LSobject' => $_REQUEST['LSobject'],
          'ioFormat' => $_POST['ioFormat'],
          'justTry' => ($_POST['justTry']=='yes'),
          'updateIfExists' => ($_POST['updateIfExists']=='yes'),
          'importfile' => $file
        );
      }
    }
    return False;
  }

  /**
   * Import objects from POST data
   *
   * This method retreive, validate and import POST data.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Array of the import result
   * @see import()
   */
  public static function importFromPostData() {
    // Get data from $_POST
    $data = self::getPostData();
    if (!is_array($data)) {
      LSerror :: addErrorCode('LSio_01');
      return array(
        'success' => false,
        'imported' => array(),
        'updated' => array(),
        'errors' => array(),
      );
    }
    self :: log_trace("importFromPostData(): POST data=".varDump($data));

    return self :: import(
      $data['LSobject'], $data['ioFormat'], $data['importfile'],
      $data['updateIfExists'], $data['justTry']
    );
  }

  /**
   * Import objects
   *
   * The return value is an array :
   *
   *   array (
   *     'success' => boolean,
   *     'LSobject' => '[object type]',
   *     'ioFormat' => '[ioFormat name]',
   *     'justTry' => boolean,
   *     'updateIfExists' => boolean,
   *     'imported' => array (
   *       '[object1 dn]' => '[object1 display name]',
   *       '[object2 dn]' => '[object2 display name]',
   *       [...]
   *     ),
   *     'updated' => array (
   *       '[object3 dn]' => '[object3 display name]',
   *       '[object4 dn]' => '[object4 display name]',
   *       [...]
   *     ),
   *     'errors' => array (
   *       array (
   *         'data' =>  array ([object data as read from source file]),
   *         'errors' => array (
   *           'globals' => array (
   *             // Global error of this object importation that not
   *             // concerning only one attribute)
   *           ),
   *           'attrs' => array (
   *             '[attr1]' => array (
   *               '[error 1]',
   *               '[error 2]',
   *               [...]
   *             )
   *           )
   *         )
   *       ),
   *       [...]
   *     )
   *   )
   *
   * @param[in] $LSobject string The LSobject type
   * @param[in] $ioFormat string The LSioFormat name
   * @param[in] $input_file string|resource The input file path
   * @param[in] $updateIfExists boolean If true and object to import already exists, update it. If false,
   *                                    an error will be triggered. (optional, default: false)
   * @param[in] $justTry boolean If true, enable just-try mode: just check input data but do not really
   *                             import objects in LDAP directory. (optional, default: false)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Array of the import result
   */
  public static function import($LSobject, $ioFormat, $input_file, $updateIfExists=false, $justTry=false) {
    $return = array(
      'success' => false,
      'LSobject' => $LSobject,
      'ioFormat' => $ioFormat,
      'updateIfExists' => $updateIfExists,
      'justTry' => $justTry,
      'imported' => array(),
      'updated' => array(),
      'errors' => array(),
    );

    // Load LSobject
    if (!isset($LSobject) || !LSsession::loadLSobject($LSobject)) {
      LSerror :: addErrorCode('LSio_02');
      return $return;
    }

    // Validate ioFormat
    $object = new $LSobject();
    if(!$object -> isValidIOformat($ioFormat)) {
      LSerror :: addErrorCode('LSio_03',$ioFormat);
      return $return;
    }

    // Create LSioFormat object
    $ioFormat = new LSioFormat($LSobject,$ioFormat);
    if (!$ioFormat -> ready()) {
      LSerror :: addErrorCode('LSio_04');
      return $return;
    }

    // Load data in LSioFormat object
    if (!$ioFormat -> loadFile($input_file)) {
      LSerror :: addErrorCode('LSio_05');
      return $return;
    }
    self :: log_debug("import(): file loaded");

    // Retreive object from ioFormat
    $objectsData = $ioFormat -> getAll();
    $objectsInError = array();
    self :: log_trace("import(): objects data=".varDump($objectsData));

    // Browse inputed objects
    foreach($objectsData as $objData) {
      $globalErrors = array();
      // Instanciate an LSobject
      $object = new $LSobject();
      // Instanciate a creation LSform (in API mode)
      $form = $object -> getForm('create', null, true);
      // Set form data from inputed data
      if (!$form -> setPostData($objData, true)) {
        self :: log_debug('import(): Failed to setPostData on: '.print_r($objData,True));
        $globalErrors[] = _('Failed to set post data on creation form.');
      }
      // Validate form
      else if (!$form -> validate(true)) {
        self :: log_debug('import(): Failed to validate form on: '.print_r($objData,True));
        self :: log_debug('import(): Form errors: '.print_r($form->getErrors(),True));
        $globalErrors[] = _('Error validating creation form.');
      }
      // Validate data (just check mode)
      else if (!$object -> updateData('create', True)) {
        self :: log_debug('import(): fail to validate object data: '.varDump($objData));
        $globalErrors[] = _('Failed to validate object data.');
      }
      else {
        self :: log_debug('import(): Data is correct, retreive object DN');
        $dn = $object -> getDn();
        if (!$dn) {
          self :: log_debug('import(): fail to generate for this object: '.varDump($objData));
          $globalErrors[] = _('Failed to generate DN for this object.');
        }
        else {
          // Check if object already exists
          if (!LSldap :: exists($dn)) {
            // Creation mode
            self :: log_debug('import(): New object, perform creation');
            if ($justTry || $object -> updateData('create')) {
              self :: log_info('Object '.$object -> getDn().' imported');
              $return['imported'][$object -> getDn()] = $object -> getDisplayName();
              continue;
            }
            else {
              self :: log_error('Failed to updateData on : '.print_r($objData, True));
              $globalErrors[]=_('Error creating object on LDAP server.');
            }
          }
          // This object already exist, check 'updateIfExists' mode
          elseif (!$updateIfExists) {
            self :: log_debug('import(): Object '.$dn.' already exist');
            $globalErrors[] = getFData(_('An object already exist on LDAP server with DN %{dn}.'),$dn);
          }
          else {
            self :: log_info('Object '.$object -> getDn().' exist, perform update');

            // Restart import in update mode

            // Instanciate a new LSobject and load data from it's DN
            $object = new $LSobject();
            if (!$object -> loadData($dn)) {
              self :: log_debug('import(): Failed to load data of '.$dn);
              $globalErrors[] = getFData(_("Failed to load existing object %{dn} from LDAP server. Can't update object."));
            }
            else {
              // Instanciate a modify form (in API mode)
              $form = $object -> getForm('modify', null, true);
              // Set form data from inputed data
              if (!$form -> setPostData($objData, true)) {
                self :: log_debug('import(): Failed to setPostData on update form : '.print_r($objData, True));
                $globalErrors[] = _('Failed to set post data on update form.');
              }
              // Validate form
              else if (!$form -> validate(true)) {
                self :: log_debug('import(): Failed to validate update form on : '.print_r($objData, True));
                self :: log_debug('import(): Form errors : '.print_r($form->getErrors(), True));
                $globalErrors[] = _('Error validating update form.');
              }
              // Update data on LDAP server
              else if ($justTry || $object -> updateData('modify')) {
                self :: log_info('Object '.$object -> getDn().' updated');
                $return['updated'][$object -> getDn()] = $object -> getDisplayName();
                continue;
              }
              else {
                self :: log_error('Object '.$object -> getDn().': Failed to updateData (modify) on : '.print_r($objData, True));
                $globalErrors[] = _('Error updating object on LDAP server.');
              }
            }
          }
        }
      }
      $objectsInError[] = array(
        'data' => $objData,
        'errors' => array (
          'globals' => $globalErrors,
          'attrs' => $form->getErrors()
        )
      );
    }
    $return['errors'] = $objectsInError;
    $return['success'] = empty($objectsInError);
    return $return;
  }

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
      LSerror :: addErrorCode('LSio_03', $ioFormat);
      return false;
    }

    // Create LSioFormat object
    $ioFormat = new LSioFormat($object -> type, $ioFormat);
    if (!$ioFormat -> ready()) {
      LSerror :: addErrorCode('LSio_04');
      return false;
    }

    // Load LSsearch class (with warning)
    if (!LSsession :: loadLSclass('LSsearch', null, true)) {
      return false;
    }

    // Search objects
    $search = new LSsearch($object -> type, 'LSio');
    $search -> run();

    // Retreive objets
    $objects = $search -> listObjects();
    if (!is_array($objects)) {
      LSerror :: addErrorCode('LSio_06');
      return false;
    }
    self :: log_debug(count($objects)." object(s) found to export");

    // Export objects using LSioFormat object
    if (!$ioFormat -> exportObjects($objects, $stream)) {
      LSerror :: addErrorCode('LSio_07');
      return false;
    }
    self :: log_debug("export(): objects exported");
    return true;
  }

  /**
   * CLI import command
   *
   * @param[in] $command_args array Command arguments:
   *   - Positional arguments:
   *     - LSobject type
   *     - LSioFormat name
   *   - Optional arguments:
   *     - -i|--input: Input path ("-" == stdin)
   *     - -U|--update: Enable "update if exist"
   *     - -j|--just-try: Enable just-try mode
   *
   * @retval boolean True on succes, false otherwise
   **/
  public static function cli_import($command_args) {
    $objType = null;
    $ioFormat = null;
    $input = null;
    $updateIfExists = false;
    $justTry = false;
    for ($i=0; $i < count($command_args); $i++) {
      switch ($command_args[$i]) {
        case '-i':
        case '--input':
          $input = $command_args[++$i];
          break;
        case '-U':
        case '--update':
          $updateIfExists = true;
          break;
        case '-j':
        case '--just-try':
          $justTry = true;
          break;
        default:
          if (is_null($objType)) {
            $objType = $command_args[$i];
          }
          elseif (is_null($ioFormat)) {
            $ioFormat = $command_args[$i];
          }
          else
            LScli :: usage("Invalid '".$command_args[$i]."' parameter.");
      }
    }

    if (is_null($objType) || is_null($ioFormat))
      LScli :: usage('You must provide LSobject type, ioFormat.');

    if (is_null($input))
      LScli :: usage('You must provide input path using -i/--input parameter.');

    // Check output
    if ($input != '-' && !is_file($input))
      LScli :: usage("Input file '$input' does not exists.");

    // Handle input from stdin
    $input = ($input=='-'?'php://stdin':$input);

    // Run export
    $result = self :: import($objType, $ioFormat, $input, $updateIfExists, $justTry);

    self :: log_info(
      count($result['imported'])." object(s) imported, ".count($result['updated']).
      " object(s) updated and ".count($result['errors'])." error(s) occurred."
    );

    if ($result['errors']) {
      echo "Error(s):\n";
      foreach($result['errors'] as $idx => $obj) {
        echo " - Object #$idx:\n";
        if ($obj['errors']['globals']) {
          echo "   - Global errors:\n";
          foreach ($obj['errors']['globals'] as $error)
            echo "     - $error\n";
        }

        echo "   - Input data:\n";
        foreach ($obj['data'] as $key => $values) {
          echo "     - $key: ".(empty($values)?'No value':'"'.implode('", "', $values).'"')."\n";
        }
        if ($obj['errors']['attrs']) {
        echo "   - Attribute errors:\n";
          foreach ($obj['errors']['attrs'] as $attr => $error) {
            echo "     - $attr: $error\n";
          }
        }
      }
    }

    if ($result['imported']) {
      echo count($result['imported'])." imported object(s):\n";
      foreach($result['imported'] as $dn => $name)
        echo " - $name ($dn)\n";
    }

    if ($result['updated']) {
      echo count($result['updated'])." updated object(s):\n";
      foreach($result['updated'] as $dn => $name)
        echo " - $name ($dn)\n";
    }

    return $result['success'];
  }

  /**
   * Args autocompleter for CLI import command
   *
   * @param[in] $command_args array List of already typed words of the command
   * @param[in] $comp_word_num int The command word number to autocomplete
   * @param[in] $comp_word string The command word to autocomplete
   * @param[in] $opts array List of global available options
   *
   * @retval array List of available options for the word to autocomplete
   **/
  public static function cli_import_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
    $opts = array_merge($opts, array ('-i', '--input', '-U', '--update', '-j', '--just-try'));

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


/*
 * LSio_implodeValues template function
 *
 * This function permit to implode field values during
 * template processing. This function take as parameters
 * (in $params) :
 * - $values : the field's values to implode
 *
 * @param[in] $params The template function parameters
 * @param[in] $template Smarty object
 *
 * @retval void
 **/
function LSio_implodeValues($params, $template) {
  extract($params);

  if (isset($values) && is_array($values)) {
    echo implode(',',$values);
  }
}
LStemplate :: registerFunction('LSio_implodeValues','LSio_implodeValues');


LSerror :: defineError('LSio_01',
___("LSio: Post data not found or not completed.")
);
LSerror :: defineError('LSio_02',
___("LSio: object type invalid.")
);
LSerror :: defineError('LSio_03',
___("LSio: input/output format %{format} invalid.")
);
LSerror :: defineError('LSio_04',
___("LSio: Fail to initialize input/output driver.")
);
LSerror :: defineError('LSio_05',
___("LSio: Fail to load objects's data from input file.")
);
LSerror :: defineError('LSio_06',
___("LSio: Fail to load objects's data to export from LDAP directory.")
);
LSerror :: defineError('LSio_07',
___("LSio: Fail to export objects's data.")
);

// Defined CLI commands functions only on CLI context
if (php_sapi_name() != 'cli')
    return true;  // Always return true to avoid some warning in log

// LScli
LScli :: add_command(
    'import',
    array('LSio', 'cli_import'),
    'Import LSobject',
    '[object type] [ioFormat name] -i /path/to/input.file',
    array(
    '   - Positional arguments :',
    '     - LSobject type',
    '     - LSioFormat name',
    '',
    '   - Optional arguments :',
    '     - -i|--input    The input file path. Use "-" for STDIN',
    '     - -U|--update   Enable "update if exist" mode',
    '     - -j|--just-try Enable just-try mode',
  ),
  true,
  array('LSio', 'cli_import_args_autocompleter')
);

// LScli
LScli :: add_command(
    'export',
    array('LSio', 'cli_export'),
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
  array('LSio', 'cli_export_args_autocompleter')
);
