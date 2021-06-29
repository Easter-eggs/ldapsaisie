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

class LSlang extends LSlog_staticLoggerClass {


  // Current lang and encoding
  private static $lang = NULL;
  private static $encoding = NULL;

  /**
   * Define current locale (and encoding)
   *
   * @param[in] $lang string|null     The lang (optional, default: default current LDAP
   *                                  server lang, or default lang)
   * @param[in] $encoding string|null The encoding (optional, default: default current LDAP
   *                                  server encoding, or default encoding)
   *
   * @retval void
   */
   public static function setLocale($lang=null, $encoding=null) {
     // Handle $lang parameter
     if (is_null($lang)) {
       if (isset($_REQUEST['lang'])) {
         $lang = $_REQUEST['lang'];
       }
       elseif (isset($_SESSION['LSlang'])) {
         $lang = $_SESSION['LSlang'];
       }
       elseif (isset(LSsession :: $ldapServer['lang'])) {
         $lang = LSsession :: $ldapServer['lang'];
       }
       else {
         $lang = LSconfig :: get('lang');
       }
     }

     // Handle $encoding parameter
     if (is_null($encoding)) {
       if (isset($_REQUEST['encoding'])) {
         $encoding = $_REQUEST['encoding'];
       }
       elseif (isset($_SESSION['LSencoding'])) {
         $encoding = $_SESSION['LSencoding'];
       }
       elseif (isset(LSsession :: $ldapServer['encoding'])) {
         $encoding = LSsession :: $ldapServer['encoding'];
       }
       else {
         $encoding = LSconfig :: get('encoding');
       }
     }

     // Set session and self variables
     $_SESSION['LSlang'] = self :: $lang = $lang;
     $_SESSION['LSencoding'] = self :: $encoding = $encoding;

     // Check
     if (self :: localeExist($lang, $encoding)) {
       self :: log_trace("setLocale($lang, $encoding): local '$lang.$encoding' exist, use it");
       if ($encoding) {
         $lang .= '.'.$encoding;
       }
       // Gettext firstly look the LANGUAGE env variable, so set it
       putenv("LANGUAGE=$lang");

       // Set the locale
       if (setlocale(LC_ALL, $lang) === false)
         self :: log_error("An error occured setting locale to '$lang'");

       // Configure and set the text domain
       $fullpath = bindtextdomain(LS_TEXT_DOMAIN, LS_I18N_DIR_PATH);
       self :: log_trace("setLocale($lang, $encoding): Text domain fullpath is '$fullpath'.");
       self :: log_trace("setLocale($lang, $encoding): Text domain is : ".textdomain(LS_TEXT_DOMAIN));

       // Include local translation file
       $lang_file = LS_I18N_DIR.'/'.$lang.'/lang.php';
       if (LSsession :: includeFile($lang_file, false, false))
         self :: log_trace("setLocale($lang, $encoding): lang file '$lang_file' loaded.");
       else
         self :: log_trace("setLocale($lang, $encoding): no lang file found ($lang_file).");

       // Include other local translation file(s)
       foreach(array(LS_I18N_DIR_PATH.'/'.$lang, LS_ROOT_DIR.'/'.LS_LOCAL_DIR.'/'.LS_I18N_DIR.'/'.$lang) as $lang_dir) {
         self :: log_trace("setLocale($lang, $encoding): lookup for translation file in '$lang_dir'");
         if (is_dir($lang_dir)) {
           foreach (listFiles($lang_dir, '/^lang\..+\.php$/') as $file) {
             $path = "$lang_dir/$file";
             self :: log_trace("setLocale($lang, $encoding): load translation file '$path'");
             include($path);
           }
         }
       }
     }
     else {
       if ($encoding && $lang) $lang .= '.'.$encoding;
       self :: log_error("The local '$lang' does not exists, use default one.");
     }
   }

  /**
   * Return list of available languages
   *
   * @param[in] $encoding  string|null  Specify encoding for lang selection. If null, use self :: encoding value,
   *                                    if false, do not filter on encoding, otherwise filter available lang for
   *                                    specified encoding (optional, default: null)
   * @params[in] $with_encoding         Return available lang list with encoding (optional, default: false)
   *
   * @retval array List of available languages.
   **/
   public static function getLangList($encoding=null, $with_encoding=false) {
     if (is_null($encoding))
      $encoding = self :: $encoding;
     if ($with_encoding)
       $list = array('en_US.UTF8');
     else
       $list = array('en_US');
     if ($encoding) {
       if ($with_encoding)
        $regex = '/^([a-zA-Z_]*\.'.$encoding.')$/';
      else
        $regex = '/^([a-zA-Z_]*)\.'.$encoding.'$/';
     }
     else {
       if ($with_encoding)
        $regex = '/^([a-zA-Z_]+\.[a-zA-Z0-9\-]+)$/';
       else
        $regex = '/^([a-zA-Z_]+)\.[a-zA-Z0-9\-]+$/';
     }
     self :: log_trace("getLangList(".varDump($encoding).", $with_encoding) : regex='$regex'");
     foreach(array(LS_I18N_DIR_PATH, LS_ROOT_DIR.'/'.LS_LOCAL_DIR.'/'.LS_I18N_DIR) as $lang_dir) {
       if (!is_dir($lang_dir))
       continue;
       if ($handle = opendir($lang_dir)) {
         while (false !== ($file = readdir($handle))) {
           if(is_dir("$lang_dir/$file")) {
             if (preg_match($regex, $file, $regs)) {
               if (!in_array($regs[1], $list)) {
                 $list[]=$regs[1];
               }
             }
           }
         }
       }
     }
     return $list;
   }

  /**
   * Return current language
   *
   * @param[in] boolean If true, only return the two first characters of the language
   *                    (For instance, 'fr' for 'fr_FR')
   *
   * @retval string The current language (ex: fr_FR, or fr if $short==true)
   **/
   public static function getLang($short=false) {
     if ($short) {
       return strtolower(substr(self :: $lang, 0, 2));
     }
     return self :: $lang;
   }

  /**
   * Return current encoding
   *
   * @retval string The current encoding (ex: UTF8)
   **/
   public static function getEncoding() {
     return self :: $encoding;
   }

  /**
   * Check a locale exists
   *
   * @param[in] $lang string The language (ex: fr_FR)
   * @param[in] $encoding string The encoding (ex: UTF8)
   *
   * @retval boolean True if the locale is available, False otherwise
   **/
   public static function localeExist($lang, $encoding) {
     if ( !$lang && !$encoding ) {
       return;
     }
     $locale=$lang.(($encoding)?'.'.$encoding:'');
     if ($locale == 'en_US.UTF8') {
       return true;
     }
     foreach(array(LS_I18N_DIR_PATH, LS_ROOT_DIR.'/'.LS_LOCAL_DIR.'/'.LS_I18N_DIR) as $lang_dir)
       if (is_dir("$lang_dir/$locale"))
         return true;
     return false;
   }

}

/*
 ***********************************************
 * Generate translation file CLI methods
 *
 * Only load in CLI context
 ***********************************************
 */
if (php_sapi_name() != "cli") return true;

/**
 * CLI generate_lang_file command
 *
 * @param[in] $command_args array Command arguments
 *
 * @retval boolean True on succes, false otherwise
 **/
 global $LSlang_cli_logger, $available_onlys, $available_withouts;

 $available_onlys = array("config", "templates", "addons", "auth_methods", "includes");
 $available_withouts = array_merge($available_onlys, array("select-list"));
function cli_generate_lang_file($command_args) {
  // Use global variables to share it with sub-functions
  global $LSlang_cli_logger, $available_onlys, $available_withouts, $data, $translations, $interactive,
  $interactive_exit, $copyoriginalvalue, $format, $curdir, $additionalfileformat, $copyoriginalvalue, $lang;

  // Initialize logger (if not already initialized by another CLI command)
  if (!isset($LSlang_cli_logger))
    $LSlang_cli_logger = LSlog :: get_logger('generate_lang_file');

  // Store existing translations
  $translations = array();
  // Store output translations
  $data = array();

  // Parameters
  $only = null;
  $withouts = array();
  $include_upstream = false;
  $copyoriginalvalue = False;
  $interactive = False;
  $interactive_exit = False; // Exit flag set when user type 'q'
  $output = False;
  $additionalfileformat = False;
  $lang = null;
  $encoding = null;
  $available_formats = array('php', 'pot');
  $format=$available_formats[0];

  $debug = false;
  $load_files = array();

  // Change directory
  $curdir = getcwd();
  chdir(dirname(__FILE__).'/../');

  function relative2absolute_path($path) {
    if ($path[0] == '/')
      return $path;
    global $curdir;
    return realpath($curdir)."/".$path;
  }

  function absolute2relative_path($path) {
    if ($path[0] == '/')
      $path = realpath($path);
    if (substr($path, 0, strlen(LS_ROOT_DIR)) == LS_ROOT_DIR)
      return substr($path, strlen(LS_ROOT_DIR)+1);
    return $path;
  }

  for ($i=0; $i < count($command_args); $i++) {
    switch ($command_args[$i]) {
      case '--without':
      case '-W':
        $i++;
        $without = strtolower($command_args[$i]);
        if (!in_array($without, $available_withouts))
          LScli :: usage("Invalid -W/--without parameter. Must be one of the following values : '".implode("','", $available_withouts)."'.");
        elseif ($only)
          LScli :: usage("You could not use only -W/--without parameter combined with -O/--only parameter.");
        $withouts[] = $without;
        break;

      case '--only':
      case '-O':
        $i++;
        if ($only)
          LScli :: usage("You could specify only on -O/--only parameter.");
        $only = strtolower($command_args[$i]);
        if (!in_array($only, $available_onlys))
          LScli :: usage("Invalid -O/--only parameter. Must be one of the following values : '".implode("','", $available_onlys)."'.");
        elseif ($without)
          LScli :: usage("You could not use only -O/--only parameter combined with -W/--without parameter.");
        break;

      case '-I':
      case '--include-upstream':
        $include_upstream=True;
        break;

      case '--copy-original-value':
      case '-c':
        $copyoriginalvalue=True;
        break;

      case '--interactive':
      case '-i':
        $interactive=True;
        break;

      case '--additional-file-format':
      case '-a':
        $additionalfileformat=True;
        break;

      case '--lang':
      case '-l':
        $i++;
        $parse_lang = explode('.', $command_args[$i]);
        if (count($parse_lang) == 2) {
          $lang = $parse_lang[0];
          $encoding = $parse_lang[1];
        }
        else {
          LScli :: usage("Invalid --lang parameter. Must be compose in format : [lang].[encoding]");
        }
        break;

      case '--output':
      case '-o':
        $i++;
        $output = $command_args[$i];
        break;

      case '--format':
      case '-f':
        $i++;
        $format = strtolower($command_args[$i]);
        if (!in_array($format, $available_formats)) {
          LScli :: usage("Invalid -f/--format parameter. Must be one of the following values : '".implode("','", $available_formats)."'.");
        }
        break;

      case '--debug':
      case '-d':
        $debug = true;
        break;

      default:
        $path = relative2absolute_path($command_args[$i]);
        if (is_file($path))
          $load_files[] = $path;
        else
          LScli :: usage($command_args[$i]." : Invalid parameter or lang file to load.");
    }
  }

  function interactive_ask($context, $msg) {
    global $copyoriginalvalue, $interactive_exit;

    if ($interactive_exit) {
      if ($copyoriginalvalue)
        return $msg;
      return true;
    }

    // Format question
    $empty_action = ($copyoriginalvalue?'copy original message':'pass');
    $question ="\"$msg\"\n\n => Please enter translated string";
    $question .= " (i";
    if (!$copyoriginalvalue)
      $question .= "/c";
    $question .= "/q/? or leave empty to $empty_action): ";

    while (true) {
      if ($context)
        fwrite(STDERR, "\n# $context\n");
      fwrite(STDERR, $question);
      $in = trim(fgets(STDIN));
      switch($in) {
        case 'q': // Exit interactive mode
        case 'Q':
          $interactive_exit = true;
          return True;
        case 'i': // Ignore
        case 'I':
          return True;
        case 'c':
        case 'C': // Copy
          if (!$copyoriginalvalue)
            return $msg;
        case '?': // Help message
          fwrite(STDERR, "Available choices:\n");
          fwrite(STDERR, " - i: ignore this message\n");
          if (!$copyoriginalvalue)
            fwrite(STDERR, " - c: copy original message\n");
          fwrite(STDERR, " - q: quit interactive mode and ignore all following untranslated messages\n");
          fwrite(STDERR, " - ?: Show this message\n");
          fwrite(STDERR, "Or leave empty to $empty_action.\n");
          break;
        case "": // Empty
          // On copy orignal value mode, return $msg
          if ($copyoriginalvalue)
            return $msg;
          // Otherwise, leave translation empty
          return "";
        default:
          // Return user input
          return $in;
      }
    }
    // Supposed to never happen
    return true;
  }

  function add($msg, $context=null) {
    global $LSlang_cli_logger, $lang, $data, $translations, $interactive, $interactive_exit, $copyoriginalvalue, $format;
    $LSlang_cli_logger -> trace("add($msg, $context)");
    if ($msg == '')
      return;
    if (!is_null($lang) && _($msg) != "$msg")
      return;

    // Message already exists ?
    if (array_key_exists($msg, $data)) {
      if ($context && !in_array($context, $data[$msg]['contexts']))
        $data[$msg]['contexts'][] = $context;
      return True;
    }

    // Handle translation
    $translation = "";
    if (array_key_exists($msg, $translations)) {
      $translation = $translations[$msg];
    }
    elseif (!is_null($lang) && _($msg) != $msg) {
      $translation = _($msg);
    }
    elseif ($interactive && $format != 'pot') {
      $translation = interactive_ask($context, $msg);
      if (!is_string($translation))
        return true;
    }
    $data[$msg] = array (
      'translation' => $translation,
      'contexts' => ($context?array($context):array()),
    );
  }

  function addFromLSconfig($pattern, $value='value', $excludes=array()) {
    global $LSlang_cli_logger;
    $LSlang_cli_logger -> trace("addFromLSconfig($pattern, array(".implode(',', $excludes)."))");
    $keys = LSconfig :: getMatchingKeys($pattern);
    $LSlang_cli_logger -> trace("addFromLSconfig : ".count($keys)." matching key(s)");
    foreach ($keys as $key => $value) {
      $LSlang_cli_logger -> trace("addFromLSconfig : $key = ".varDump($value));
      if ($value == 'key') {
        // Get the last key parts as value and all other as key
        $key_parts = explode('.', $key);
        $value = $key_parts[count($key_parts)-1];
        $key = implode('.', array_slice($key_parts, 0, count($key_parts)-1));
      }
      if (!in_array($value, $excludes) && is_string($value))
        add($value, $key);
    }
  }

  // Load translation files
  foreach($load_files as $path) {
    $LSlang_cli_logger -> debug("Load $path lang file");
    @include($path);
    foreach($GLOBALS['LSlang'] as $msg => $trans) {
      $translations[$msg]=$trans;
    }
  }

  // Initialize session
  LSlang :: setLocale($lang, $encoding);

  // Load lang string if lang was specify
  if ($lang && $encoding && isset($GLOBALS['LSlang']) && is_array($GLOBALS['LSlang'])) {
    foreach($GLOBALS['LSlang'] as $msg => $trans) {
      $translations[$msg] = $trans;
    }
  }

  function addPossibleValuesFromLSconfig($context, $withouts, $level=0) {
    global $LSlang_cli_logger;
    $LSlang_cli_logger -> trace("addPossibleValuesFromLSconfig($context)");
    if (in_array('select-list', $withouts))
      return true;
    if (!LSconfig :: get("$context.translate_labels", True, "bool"))
      return true;
    foreach(LSconfig :: get("$context.possible_values", array()) as $pkey => $plabel) {
      if (is_array($plabel)) {
        // Sub possible values
        // Check level
        if ($level > 1) {
          $LSlang_cli_logger -> warning(
            "addPossibleValuesFromLSconfig($context): Level to hight to handle sub possible values of $context.possible_values.$pkey"
          );
          return true;
        }
        addFromLSconfig("$context.possible_values.$pkey.label");
        $LSlang_cli_logger -> trace("addPossibleValuesFromLSconfig($context): handle sub possible values of $context.possible_values.$pkey");
        addPossibleValuesFromLSconfig("$context.possible_values.$pkey", $withouts, $level+1);
      }
      else {
        switch ($pkey) {
          case 'OTHER_OBJECT':
            $LSlang_cli_logger -> trace("addPossibleValuesFromLSconfig($context): ignore $context.possible_values.$pkey (OTHER_OBJECT)");
            break;
          case 'OTHER_ATTRIBUTE':
            if (is_array($plabel)) {
              if (isset($plabel['json_component_key']))
                addFromLSconfig("$context.possible_values.OTHER_ATTRIBUTE.json_component_label");
              else
                addFromLSconfig("$context.possible_values.OTHER_ATTRIBUTE.*");
            }
            else {
              $LSlang_cli_logger -> warning("addPossibleValuesFromLSconfig($context): invalid $context.possible_values.OTHER_ATTRIBUTE config => Must be an array.");
            }
            break;
          default:
            add($plabel, "$context.possible_values.$pkey");
            break;
        }
      }
    }
  }

  /*
   * Manage configuration parameters
   */
  if (!in_array('config', $withouts) && (!$only || $only == 'config')) {
    // LDAP Servers
    $objects = array();
    $LSlang_cli_logger -> info("Looking for string to translate configuration of LDAP servers");
    foreach(LSconfig :: keys('ldap_servers') as $ldap_server_id) {
      $LSlang_cli_logger -> debug("Looking for string to translate configuration of LDAP server #$ldap_server_id");
      addFromLSconfig("ldap_servers.$ldap_server_id.name");
      addFromLSconfig("ldap_servers.$ldap_server_id.subDnLabel");
      addFromLSconfig("ldap_servers.$ldap_server_id.recoverPassword.recoveryHashMail.subject");
      addFromLSconfig("ldap_servers.$ldap_server_id.recoverPassword.recoveryHashMail.msg");
      addFromLSconfig("ldap_servers.$ldap_server_id.recoverPassword.newPasswordMail.subject");
      addFromLSconfig("ldap_servers.$ldap_server_id.recoverPassword.newPasswordMail.msg");
      addFromLSconfig("ldap_servers.$ldap_server_id.subDn.*", 'key', array("LSobject"));
      addFromLSconfig("ldap_servers.$ldap_server_id.LSprofiles.*.label");

      // LSaccess
      foreach (LSconfig :: get("ldap_servers.$ldap_server_id.LSaccess", array()) as $LSobject) {
        if (is_string($LSobject) && !in_array($LSobject, $objects) && LSsession :: loadLSobject($LSobject)) {
          $objects[] = $LSobject;
        }
      }

      // Sub DN LSobjects
      foreach (LSconfig :: getMatchingKeys("ldap_servers.$ldap_server_id.subDn.*.LSobjects.*") as $LSobject)
        if (is_string($LSobject) && !in_array($LSobject, $objects) && LSsession :: loadLSobject($LSobject))
          $objects[] = $LSobject;

    }

    $LSlang_cli_logger -> debug('LSobjects list : '.implode(', ', $objects));

    // LSobject
    foreach($objects as $obj) {
      $LSlang_cli_logger -> info("Looking for string to translate configuration of object type $obj");
      addFromLSconfig("LSobjects.$obj.label");

      // LSrelation
      addFromLSconfig("LSobjects.$obj.LSrelation.*.label");
      addFromLSconfig("LSobjects.$obj.LSrelation.*.emptyText");

      // Custom Actions
      addFromLSconfig("LSobjects.$obj.customActions.*.label");
      addFromLSconfig("LSobjects.$obj.customActions.*.helpInfo");
      addFromLSconfig("LSobjects.$obj.customActions.*.question_format");
      addFromLSconfig("LSobjects.$obj.customActions.*.onSuccessMsgFormat");

      // LSform
      addFromLSconfig("LSobjects.$obj.LSform.layout.*.label");
      addFromLSconfig("LSobjects.$obj.LSform.dataEntryForm.*.label");

      // LSsearch
      addFromLSconfig("LSobjects.$obj.LSsearch.predefinedFilters.*");
      addFromLSconfig("LSobjects.$obj.LSsearch.extraDisplayedColumns.*.label");
      addFromLSconfig("LSobjects.$obj.LSsearch.customActions.*.label");
      addFromLSconfig("LSobjects.$obj.LSsearch.customActions.*.question_format");
      addFromLSconfig("LSobjects.$obj.LSsearch.customActions.*.onSuccessMsgFormat");

      // Attributes
      foreach(LSconfig :: keys("LSobjects.$obj.attrs") as $attr) {
        addFromLSconfig("LSobjects.$obj.attrs.$attr.label");
        addFromLSconfig("LSobjects.$obj.attrs.$attr.help_info");
        addFromLSconfig("LSobjects.$obj.attrs.$attr.no_value_label");
        addFromLSconfig("LSobjects.$obj.attrs.$attr.check_data.*.msg");
        addFromLSconfig("LSobjects.$obj.attrs.$attr.validation.*.msg");

        // HTML Options
        $html_type = LSconfig :: get("LSobjects.$obj.attrs.$attr.html_type");
        switch($html_type) {
          case 'boolean':
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.true_label");
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.false_label");
            break;
          case 'jsonCompositeAttribute':
            $components = LSconfig :: keys("LSobjects.$obj.attrs.$attr.html_options.components");
            foreach($components as $c) {
              addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.label");
              addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.help_info");
              addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.check_data.*.msg");

              if (LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.components.$c.type") == 'select_list')
                addPossibleValuesFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.options", $withouts);
            }
            break;
          case 'labeledValue':
            if (LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.translate_labels", True, "bool"))
              addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.labels.*");
            break;
          case 'password':
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.mail.subject");
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.mail.msg");
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.confirmChangeQuestion");
            break;
          case 'select_list':
          case 'select_box':
            addPossibleValuesFromLSconfig("LSobjects.$obj.attrs.$attr.html_options", $withouts);
            break;
          case 'valueWithUnit':
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.units.*");
            break;
          case 'date':
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.special_values.*");
            break;
        }
      }
    }
  }

  /*
   * Manage template file
   */
  if (!in_array('templates', $withouts) && (!$only || $only == 'templates')) {
    function parse_template_file($file) {
      global $LSlang_cli_logger;
      $LSlang_cli_logger -> debug("Looking for string to translate in '$file' template file");
      $count = 0;
      foreach(file($file) as $line) {
        $count ++;
        if (preg_match_all('/\{ *tr +msg=["\']([^\}]+)["\'] *\}/',$line,$matches)) {
          foreach($matches[1] as $t) {
            $t = preg_replace('/[\'"]\|escape\:.*$/', '', $t);
            $LSlang_cli_logger -> trace("  - \"$t\" # Line $count");
            add($t, absolute2relative_path($file).":$count");
          }
        }
      }
      $LSlang_cli_logger -> trace("parse_template_file($file) : done.");
    }

    function find_and_parse_template_file($dir) {
      if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
          while (($file = readdir($dh)) !== false) {
            if ($file=='.' || $file=='..') continue;
            if (is_dir($dir.'/'.$file)) {
              find_and_parse_template_file($dir.'/'.$file);
            }
            elseif (is_file($dir."/".$file) && preg_match('/\.tpl$/',$file)) {
              parse_template_file($dir.'/'.$file);
            }
          }
          closedir($dh);
        }
      }
    }
    $LSlang_cli_logger -> info("Looking for string to translate in templates file");
    if ($include_upstream) find_and_parse_template_file(LS_ROOT_DIR.'/'.LS_TEMPLATES_DIR);
    find_and_parse_template_file(LS_ROOT_DIR.'/'.LS_LOCAL_DIR.LS_TEMPLATES_DIR);
  }

  /*
  * Manage custom PHP code/config files
   */
  function parse_php_file($file) {
    global $LSlang_cli_logger;
    $LSlang_cli_logger -> debug("Looking for string to translate in '$file' PHP file");
    $count = 0;
    $quote='';
    $res='';
    foreach(file($file) as $line) {
      $count++;
      $LSlang_cli_logger -> trace("Handle line #$count of '$file' PHP file");
      $offset=0;
      while ($pos = strpos($line,'__(',$offset)) {
        $LSlang_cli_logger -> trace("$file:$count: detect keyword at position #$pos ('$line')");
        for ($i=$pos+3;$i<strlen($line);$i++) {
          $offset=$i; // Always increase offset to avoid infinity-loop
          if (empty($quote)) {
            // Quote char not detected : try to detect it
            if ($line[$i]=='\\' || $line[$i]==" " || $line[$i]=="\t") {
              // Space or escape char : pass
              $i++;
            }
            elseif ($line[$i]=='"' || $line[$i]=="'") {
              // Quote detected
              $quote=$line[$i];
            }
            elseif ($line[$i]=='$' || $line[$i]==')') {
              // Variable translation not possible or end function call detected
              break;
            }
            else {
              // Unknown case : continue
              $i++;
            }
          }
          elseif (!empty($quote)) {
            // Quote char already detected : try to detect end quote char
            if ($line[$i]=='\\') {
              // Escape char detected : pass this char and the following one
              $res.=$line[$i];
              $i++;
              $res.=$line[$i];
            }
            elseif ($line[$i]==$quote) {
              // End quote char detected : reset quote char detection and break detection
              $quote='';
              break;
            }
            else {
              // End quote char not detected : append current char to result
              $res.=$line[$i];
            }
          }
        }
        // Include detected string if not empty and quote char was detected and reseted
        if (!empty($res) && empty($quote)) {
          add($res, absolute2relative_path($file).":$count");
          $res='';
        }
      }
    }
  }

  function find_and_parse_php_file($dir, $filename_regex) {
    if (is_dir($dir)) {
      if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
          if (preg_match($filename_regex, $file)) {
            parse_php_file($dir.'/'.$file);
          }
        }
        closedir($dh);
      }
    }
  }

  /*
   * Manage includes files
   */
  if (!in_array('includes', $withouts) && (!$only || $only == 'includes')) {
    // Note: Upstream code most only use gettext translation, do not handle it here
    if ($include_upstream) find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_INCLUDE_DIR, '/^(.+)\.php$/');
    find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_LOCAL_DIR.LS_INCLUDE_DIR, '/^(.+)\.php$/');
    if ($include_upstream) find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_CLASS_DIR, '/^class\.(.+)\.php$/');
    find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_LOCAL_DIR.LS_CLASS_DIR, '/^class\.(.+)\.php$/');
  }

  /*
   * Manage addons files
   */
  if (!in_array('addons', $withouts) && (!$only || $only == 'addons')) {
    $LSlang_cli_logger -> info("Looking for string to translate in LSaddons PHP code");
    if ($include_upstream) find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_ADDONS_DIR, '/^LSaddons\.(.+)\.php$/');
    find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_LOCAL_DIR.LS_ADDONS_DIR, '/^LSaddons\.(.+)\.php$/');
    $LSlang_cli_logger -> info("Looking for string to translate in LSaddons configuration files");
    if ($include_upstream) find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_CONF_DIR.'/LSaddons', '/^config\.LSaddons\.(.+)\.php$$/');
    find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_LOCAL_DIR.LS_CONF_DIR.'/LSaddons', '/^config\.LSaddons\.(.+)\.php$$/');
  }

  /*
   * Manage auth methods files
   */
  if (!in_array('auth_methods', $withouts) && (!$only || $only == 'auth_methods')) {
    $LSlang_cli_logger -> info("Looking for string to translate in LSauthMethods configuration files");
    if ($include_upstream) find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_CONF_DIR.'/LSauth', '/^config\.(.+)\.php$$/');
    find_and_parse_php_file(LS_ROOT_DIR.'/'.LS_LOCAL_DIR.LS_CONF_DIR.'/LSauth', '/^config\.(.+)\.php$$/');
  }

  // Sort resulting strings
  ksort($data);

  /*
   * Handle output file format
   */
  function output_php($fd) {
    global $LSlang_cli_logger, $additionalfileformat, $data, $copyoriginalvalue;
    fwrite($fd, "<?php\n\n");

    if (!$additionalfileformat) fwrite($fd, "\$GLOBALS['LSlang'] = array (\n");

    foreach($data as $key => $key_data) {
      if ($copyoriginalvalue && $key_data['translation'] == "") {
        $val = $key;
      }
      else
        $val = $key_data['translation'];
      $key=str_replace('"','\\"',$key);
      $val=str_replace('"','\\"',$val);
      foreach ($key_data['contexts'] as $context)
        fwrite($fd, "\n# $context");
      if ($additionalfileformat) {
        fwrite($fd, "\n\$GLOBALS['LSlang'][\"$key\"] = \"$val\";\n");
      }
      else {
        fwrite($fd, "\n\"$key\" =>\n  \"$val\",\n");
      }
    }

    if (!$additionalfileformat) fwrite($fd, "\n);\n");
  }

  function clean_for_pot_file($val) {
    $val = str_replace('"', '\\"', $val);
    return str_replace("\n", "\\n", $val);
  }

  function output_pot($fd) {
    global $LSlang_cli_logger, $data, $copyoriginalvalue;
    foreach($data as $key => $key_data) {
      if ($copyoriginalvalue && $key_data['translation'] == "") {
        $val = $key;
      }
      else
        $val = $key_data['translation'];
      foreach ($key_data['contexts'] as $context)
        fwrite($fd, "#: $context\n");
      $key = clean_for_pot_file($key);
      $val = clean_for_pot_file($val);
      fwrite($fd, "msgid \"$key\"\nmsgstr \"$val\"\n\n");
    }
  }

  // Determine where to write result
  if ($output) {
    $output = relative2absolute_path($output);
    $LSlang_cli_logger -> info("Write result in output file ($output)");
    try {
      $LSlang_cli_logger -> debug("Open output file ($output)");
      $fd = fopen($output, 'w');
    }
    catch(Exception $e) {
      $LSlang_cli_logger -> error('Error occured opening output file : '.$e->getMessage(), "\n");
    }
    if (!$fd) {
      $LSlang_cli_logger -> error("Use stdout out instead.\n");
      $fd = STDOUT;
      $output = false;
    }
  }
  else
    $fd = STDOUT;

  // Generate output
  $LSlang_cli_logger -> debug("Output format : $format");
  switch($format) {
    case 'pot':
      output_pot($fd);
      break;
    case 'php':
    default:
      output_php($fd);
      break;
  }

  // Close output file (is specified)
  if ($output && $fd != STDOUT) {
    $LSlang_cli_logger -> debug("Close output file ($output)");
    fclose($fd);
  }

  return true;
}

/**
 * Args autocompleter for CLI command generate_lang_file
 *
 * @param[in] $comp_words array List of already typed words of the command
 * @param[in] $comp_word_num int The command word number to autocomplete
 * @param[in] $comp_word string The command word to autocomplete
 * @param[in] $opts array List of global available options
 *
 * @retval array List of available options for the word to autocomplete
 **/
function cli_generate_lang_file_args_autocompleter($comp_words, $comp_word_num, $comp_word, $opts) {
  global $available_withouts, $available_onlys;
  switch ($comp_words[$comp_word_num-1]) {
    case '-W':
    case '--without':
      return LScli :: autocomplete_opts($available_withouts, $comp_word);
      break;
    case '-O':
    case '--only':
      return LScli :: autocomplete_opts($available_onlys, $comp_word);
      break;
    case '-l':
    case '--lang':
      return LScli :: autocomplete_opts(LSlang :: getLangList(false, true), $comp_word);
      break;
    case '-o':
    case '--output':
      return array();
      break;
    case '-f':
    case '--format':
      return LScli :: autocomplete_opts(array('php', 'pot'), $comp_word);
      break;
  }
  $opts = array_merge(
    $opts,
    array (
      '-W', '--without',
      '-O', '--only',
      '-c', '--copy-original-value',
      '-i', '--interactive',
      '-a', '--additional-file-format',
      '-l', '--lang',
      '-o', '--output',
      '-f', '--format',
      '-I', '--include-upstream',
    )
  );
  return LScli :: autocomplete_opts($opts, $comp_word);
}

LScli :: add_command(
  'generate_lang_file',
  'cli_generate_lang_file',
  'Generate lang.php file',
  'l [lang] [-o output.file] [file1] [file2] [-h] [options]',
  array(
    "  -W/--without                Disable specified messages. Must be one of",
    "                              the following values :",
    "                               - ".implode("\n                               - ", $available_withouts),
    "  -O/--only                   Only handle specified messages. Must be one",
    "                              of the following values :",
    "                               - ".implode("\n                               - ", $available_onlys),
    "  -I/--include-upstream       Include upstream code to message lookup",
    "  -c/--copy-original-value    Copy original value as translated value when",
    "                              no translated value exists",
    "  -i/--interactive            Interactive mode : ask user to enter",
    "                              translated on each translation needed",
    "  -a/--additional-file-format Additional file format output",
    "  -l/--lang                   Load the specify lang",
    "                              Format: [lang].[encoding]",
    "  -o/--output                 Output file (default: stdout)",
    "  -f/--format                 Output file format : php or pot",
    "                              (default: php)",
  ),
  false,  // This command does not need LDAP connection
  'cli_generate_lang_file_args_autocompleter'
);

/**
 * CLI generate_ldapsaisie_pot command
 *
 * @param[in] $command_args array Command arguments
 *
 * @retval boolean True on succes, false otherwise
 **/
function cli_generate_ldapsaisie_pot($command_args) {
  global $LSlang_cli_logger;
  // Initialize logger (if not already initialized by another CLI command)
  if (!isset($LSlang_cli_logger))
    $LSlang_cli_logger = LSlog :: get_logger('generate_ldapsaisie_pot');

  // Clean php file in tmp directory
  if (is_dir(LS_TMP_DIR_PATH)) {
    foreach(listFiles(LS_TMP_DIR_PATH, '/\.php$/') as $file) {
      $tmp_file = LS_TMP_DIR_PATH.$file;
      $LSlang_cli_logger -> debug("Remove temporary file '$tmp_file'");
      if (!unlink($tmp_file)) {
        $LSlang_cli_logger -> fatal("Fail to delete temporary file '$tmp_file'.");
      }
    }
  }

  // List PHP files to parse
  $php_files = LScli :: run_external_command(
    array('find', escapeshellarg(LS_ROOT_DIR), '-name', "'*.php'"),
    null,   // no STDIN data
    false   // do not escape command args (already done)
  );
  if (!is_array($php_files) || $php_files[0] != 0) {
    $LSlang_cli_logger -> fatal("Fail to list PHP files.");
  }

  // Extract messages from LdapSaisie PHP files using xgettext
  $result = LScli :: run_external_command(
    array(
      "xgettext",
      "--from-code utf-8",
      "--language=PHP",
      "-o", LS_I18N_DIR_PATH."/ldapsaisie-main.pot",      // Output
      "--omit-header",                                    // No POT header
      "--keyword=__",                                     // Handle custom __() translation function
      "--keyword=___",                                    // Handle custom ___() translation function
      "--files=-"                                         // Read files to parse from STDIN
    ),
    $php_files[1]                                         // Pass PHP files list via STDIN
  );
  if (!is_array($result) || $result[0] != 0)
    $LSlang_cli_logger -> fatal("Fail to extract messages from PHP files using xgettext.");


  // Extract other messages from LdapSaisie templates files
  $result = LScli :: run_command(
    'generate_lang_file',
    array (
      "-o", LS_I18N_DIR_PATH."/ldapsaisie-templates.pot",
      "-f", "pot",
      "--only", "templates",
      "--include-upstream",
    ),
    false // do not exit
  );
  if (!$result)
    $LSlang_cli_logger -> fatal("Fail to extract messages from template files using generate_lang_file command.");

  // Merge previous results in ldapsaisie.pot file using msgcat
  $result = LScli :: run_external_command(array(
    'msgcat',
    LS_I18N_DIR_PATH."/ldapsaisie-main.pot",
    LS_I18N_DIR_PATH."/ldapsaisie-templates.pot",
    "-o", LS_I18N_DIR_PATH."/ldapsaisie.pot",
  ));
  if (!is_array($result) || $result[0] != 0)
    $LSlang_cli_logger -> fatal("Fail to merge messages using msgcat.");

  return true;
}
LScli :: add_command(
  'generate_ldapsaisie_pot',
  'cli_generate_ldapsaisie_pot',
  'Generate ldapsaisie.pot files :',
  null,
  array(
    "This command generate 3 POT files:",
    " - ".LS_I18N_DIR_PATH."/ldapsaisie-main.pot",
    "   => contains messages from PHP files",
    " - ".LS_I18N_DIR_PATH."/ldapsaisie-templates.pot",
    "   => contains messages from templates files",
    " - ".LS_I18N_DIR_PATH."/ldapsaisie.pot",
    "   => contains all messages",
  ),
  false   // This command does not need LDAP connection
);
