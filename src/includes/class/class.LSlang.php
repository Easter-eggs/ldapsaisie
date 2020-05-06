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

class LSlang {


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
       LSlog :: debug("LSsession :: setLocale() : Use local '$lang.$encoding'");
       if ($encoding) {
         $lang .= '.'.$encoding;
       }
       // Gettext firstly look the LANGUAGE env variable, so set it
       putenv("LANGUAGE=$lang");

       // Set the locale
       if (setlocale(LC_ALL, $lang) === false)
         LSlog :: error("An error occured setting locale to '$lang'");

       // Configure and set the text domain
       $fullpath = bindtextdomain(LS_TEXT_DOMAIN, LS_I18N_DIR_PATH);
       LSlog :: debug("Text domain fullpath is '$fullpath'.");
       LSlog :: debug("Text domain is : ".textdomain(LS_TEXT_DOMAIN));

       // Include local translation file
       LSsession :: includeFile(LS_I18N_DIR.'/'.$lang.'/lang.php');

       // Include other local translation file(s)
       foreach(array(LS_I18N_DIR_PATH.'/'.$lang, LS_LOCAL_DIR.'/'.LS_I18N_DIR.'/'.$lang) as $lang_dir) {
         if (is_dir($lang_dir)) {
           foreach (listFiles($lang_dir, '/^lang.+\.php$/') as $file) {
             $path = "$lang_dir/$file";
             LSlog :: debug("LSession :: setLocale() : Local '$lang.$encoding' : load translation file '$path'");
             include($path);
           }
         }
       }
     }
     else {
       if ($encoding && $lang) $lang .= '.'.$encoding;
       LSlog :: error("The local '$lang' does not exists, use default one.");
     }
   }

  /**
   * Return list of available languages
   *
   * @retval array List of available languages.
   **/
   public static function getLangList() {
     $list = array('en_US');
     if (self :: $encoding) {
       $regex = '/^([a-zA-Z_]*)\.'.self :: $encoding.'$/';
     }
     else {
       $regex = '/^([a-zA-Z_]*)$/';
     }
     foreach(array(LS_I18N_DIR_PATH, LS_LOCAL_DIR.'/'.LS_I18N_DIR) as $lang_dir) {
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
     foreach(array(LS_I18N_DIR_PATH, LS_LOCAL_DIR.'/'.LS_I18N_DIR) as $lang_dir)
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
if (php_sapi_name() != "cli") return;

/**
 * CLI generate_lang_file command
 *
 * @param[in] $command_args array Command arguments
 *
 * @retval boolean True on succes, false otherwise
 **/
 global $available_onlys, $available_withouts;
 $available_onlys = array("config", "templates", "addons");
 $available_withouts = array_merge($available_onlys, array("select-list"));
function cli_generate_lang_file($command_args) {
  // Use global variables to share it with sub-functions
  global $available_onlys, $available_withouts, $data, $translations, $interactive,
  $copyoriginalvalue, $format, $curdir, $additionalfileformat, $copyoriginalvalue, $lang;

  // Store existing translations
  $translations = array();
  // Store output translations
  $data = array();

  // Parameters
  $only = null;
  $withouts = array();
  $copyoriginalvalue = False;
  $interactive = False;
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



  function debug($msg) {
    LSlog :: debug("generate_lang_file() : $msg");
  }

  function add($msg, $context=null) {
    global $lang, $data, $translations, $interactive, $copyoriginalvalue, $format;
    debug("add($msg, $context)");
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
      if ($context)
        fwrite(STDERR, "\n# $context\n");
      if ($copyoriginalvalue) {
        fwrite(STDERR, "\"$msg\"\n\n => Please enter translated string (or leave empty to copy original string) : ");
        $in = trim(fgets(STDIN));
        if ($in)
          $translation = $in;
        else
          $translation = $msg;
      }
      else {
        fwrite(STDERR, "\"$msg\"\n\n => Please enter translated string (or 'c' to copy original message, 'i' to ignore this message, leave empty to pass) : ");
        $in = trim(fgets(STDIN));
        if ($in) {
          if ($in=="i")
            return True;
          if ($in=="c")
            $translation = $msg;
          else
            $translation = $in;
        }
      }
    }
    $data[$msg] = array (
      'translation' => $translation,
      'contexts' => ($context?array($context):array()),
    );
  }

  function addFromLSconfig($pattern, $value='value', $excludes=array()) {
    debug("addFromLSconfig($pattern, array(".implode(',', $excludes)."))");
    $keys = LSconfig :: getMatchingKeys($pattern);
    debug("addFromLSconfig : ".count($keys)." matching key(s)");
    foreach ($keys as $key => $value) {
      debug("addFromLSconfig : $key = $value");
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
    debug("Load $path lang file");
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

  /*
   * Manage configuration parameters
   */
  if (!in_array('config', $withouts) && (!$only || $only == 'config')) {
    // LDAP Servers
    $objects = array();
    foreach(LSconfig :: keys('ldap_servers') as $ldap_server_id) {
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

    debug('LSobjects list : '.implode(', ', $objects));

    // LSobject
    foreach($objects as $obj) {
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

              if (
                    LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.components.$c.type") == 'select_list' &&
                    LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.components.$c.options.translate_labels", "True", "bool") &&
                    !in_array('select-list', $withouts)
                 )
              {
                foreach(LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values", array()) as $pkey => $plabel) {
                  if (is_string($pkey)) {
                    if ($pkey == 'OTHER_OBJECT')
                      continue;
                    elseif ($pkey == 'OTHER_ATTRIBUTE') {
                      if (is_string($plabel))
                        continue;
                      elseif (is_array($plabel)) {
                        if (isset($plabel['json_component_key']))
                          addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values.OTHER_ATTRIBUTE.json_component_label");
                        else
                          addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values.OTHER_ATTRIBUTE.*");
                      }
                    }
                    else
                      add($plabel, "LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values.$pkey");
                  }
                  elseif (is_int($pkey) && is_array($plabel)) {
                    // Sub possible values
                    addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values.$pkey.label");
                    foreach(LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values.$pkey.possible_values", array()) as $ppkey => $pplabel) {
                      if ($ppkey == 'OTHER_OBJECT')
                        continue;
                      elseif ($ppkey == 'OTHER_ATTRIBUTE') {
                        if (is_string($pplabel))
                          continue;
                        elseif (is_array($pplabel)) {
                          if (isset($pplabel['json_component_key']))
                            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values.OTHER_ATTRIBUTE.json_component_label");
                          else
                            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values.OTHER_ATTRIBUTE.*");
                        }
                      }
                      elseif(is_string($pplabel)) {
                        add($pplabel, "LSobjects.$obj.attrs.$attr.html_options.components.$c.options.possible_values.$pkey.possible_values.$ppkey");
                      }
                    }
                  }
                }
              }
            }
            break;
          case 'labeledValue':
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.labels.*");
            break;
          case 'password':
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.mail.subject");
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.mail.msg");
            break;
          case 'select_list':
          case 'select_box':
            if (LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.translate_labels", "True", "bool") && !in_array('select-list', $withouts)) {
              foreach(LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.possible_values", array()) as $pkey => $plabel) {
                if (is_string($pkey)) {
                  if ($pkey == 'OTHER_OBJECT')
                    continue;
                  elseif ($pkey == 'OTHER_ATTRIBUTE') {
                    if (is_string($plabel))
                      continue;
                    elseif (is_array($plabel)) {
                      if (isset($plabel['json_component_key']))
                        addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.possible_values.OTHER_ATTRIBUTE.json_component_label");
                      else
                        addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.possible_values.OTHER_ATTRIBUTE.*");
                    }
                  }
                  else
                    add($plabel, "LSobjects.$obj.attrs.$attr.html_options.possible_values.$pkey");
                }
                elseif (is_int($pkey) && is_array($plabel)) {
                  // Sub possible values
                  addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.possible_values.$pkey.label");
                  foreach(LSconfig :: get("LSobjects.$obj.attrs.$attr.html_options.possible_values.$pkey.possible_values", array()) as $ppkey => $pplabel) {
                    if ($ppkey == 'OTHER_OBJECT')
                      continue;
                    elseif ($ppkey == 'OTHER_ATTRIBUTE') {
                      if (is_string($pplabel))
                        continue;
                      elseif (is_array($pplabel)) {
                        if (isset($pplabel['json_component_key']))
                          addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.possible_values.OTHER_ATTRIBUTE.json_component_label");
                        else
                          addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.possible_values.OTHER_ATTRIBUTE.*");
                      }
                    }
                    elseif(is_string($pplabel)) {
                      add($pplabel, "LSobjects.$obj.attrs.$attr.html_options.possible_values.$pkey.possible_values.$ppkey");
                    }
                  }
                }
              }
            }
            break;
          case 'valueWithUnit':
            addFromLSconfig("LSobjects.$obj.attrs.$attr.html_options.units.*");
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
      debug("parse_template_file($file) : start ...");
      $count = 0;
      foreach(file($file) as $line) {
        $count ++;
        if (preg_match_all('/\{ *tr +msg=["\']([^\}]+)["\'] *\}/',$line,$matches)) {
          foreach($matches[1] as $t) {
            debug("  - \"$t\" # Line $count");
            add($t, absolute2relative_path($file).":$count");
          }
        }
      }
      debug("parse_template_file($file) : done.");
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
    find_and_parse_template_file(LS_ROOT_DIR.'/'.LS_TEMPLATES_DIR);
    find_and_parse_template_file(LS_ROOT_DIR.'/'.LS_LOCAL_DIR.LS_TEMPLATES_DIR);
  }

  /*
   * Manage addons files
   */

  if (!in_array('addons', $withouts) && (!$only || $only == 'addons')) {
    function parse_addon_file($file) {
      debug("parse_addon_file($file)");
      $count = 0;
      foreach(file($file) as $line) {
        $count++;
        $offset=0;
        while ($pos = strpos($line,'__(',$offset)) {
          $quote='';
          $res='';
          for ($i=$pos+3;$i<strlen($line);$i++) {
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
                $offset=$i;
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
                // End quote char detected : set offset for next detection and break this one
                $offset=$i;
                break;
              }
              else {
                // End quote char not detected : append current char to result
                $res.=$line[$i];
              }
            }
          }
          if (!empty($res)) add($res, absolute2relative_path($file).":$count");
        }
      }
    }

    function find_and_parse_addon_file($dir) {
      if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
          while (($file = readdir($dh)) !== false) {
            if (preg_match('/^LSaddons\.(.+)\.php$/',$file)) {
              parse_addon_file($dir.'/'.$file);
            }
          }
          closedir($dh);
        }
      }
    }

    find_and_parse_addon_file(LS_ROOT_DIR.'/'.LS_ADDONS_DIR);
    find_and_parse_addon_file(LS_ROOT_DIR.'/'.LS_LOCAL_DIR.LS_ADDONS_DIR);
  }

  // Sort resulting strings
  ksort($data);

  /*
   * Handle output file format
   */
  function output_php($fd) {
    global $additionalfileformat, $data, $copyoriginalvalue;
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
    global $data, $copyoriginalvalue;
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
    try {
      debug("Open output file ($output)");
      $fd = fopen($output, 'w');
    }
    catch(Exception $e) {
      LSlog :: error('Error occured opening output file : '.$e->getMessage(), "\n");
    }
    if (!$fd) {
      LSlog :: error("Use stdout out instead.\n");
      $fd = STDOUT;
      $output = false;
    }
  }
  else
    $fd = STDOUT;

  // Generate output
  debug("Output format : $format");
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
    debug("Close output file ($output)");
    fclose($fd);
  }

  return true;
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
  false   // This command does not need LDAP connection
);
