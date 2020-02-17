#!/usr/bin/php
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

error_reporting(E_ERROR);

// Change directory
$curdir=getcwd();
chdir(dirname(__FILE__).'/../');

require_once('core.php');
require_once('conf/config.inc.php');

$available_onlys = array("config", "templates", "addons");
$only = null;
$available_withouts = array_merge($available_onlys, array("select-list"));
$withouts = array();
$copyoriginalvalue=False;
$interactive=False;
$output=False;
$additionalfileformat=False;
$lang=False;
$encoding=False;
$available_formats=array('php', 'pot');
$format=$available_formats[0];
$translations=array();
$debug = false;
$load_files = array();
function usage($error=false, $exit_code=0) {
  global $argv, $available_withouts, $available_onlys;
  if ($error)
    echo "$error\n\n";
  echo "Usage : ".$argv[0]." [file1] [file2] [-h] [options]\n";
  echo "  -W/--without                Disable specified messages. Must be one of the following values :\n";
  echo "                              '".implode("','", $available_withouts)."'\n";
  echo "  -O/--only                   Only handle specified messages. Must be one of the following values :\n";
  echo "                              '".implode("','", $available_onlys)."'\n";
  echo "  -c/--copy-original-value    Copy original value as translated value when no translated value exists\n";
  echo "  -i/--interactive            Interactive mode : ask user to enter translated on each translation needed\n";
  echo "  -a/--additional-file-format Additional file format output\n";
  echo "  -l/--lang                   Load this specify lang (format : [lang].[encoding])\n";
  echo "  -o/--output                 Output file (default : stdout)\n";
  echo "  -f/--format                 Output file format : php or pot (default : php)\n";
  echo "  -d/--debug                  Enable debug mode\n";
  exit($exit_code);
}

function realtive_path($path) {
  if ($path[0] == '/')
    return $path;
  global $curdir;
  return realpath($curdir)."/".$path;
}

if ($argc > 1) {
  for ($i=1;$i<$argc;$i++) {
    if($argv[$i]=='--without' || $argv[$i]=='-W') {
      $i++;
      $without = strtolower($argv[$i]);
      if (!in_array($without, $available_withouts))
        die("Invalid -W/--without parameter. Must be one of the following values : '".implode("','", $available_withouts)."'.\n");
      elseif ($only)
        die("You could not use only -W/--without parameter combined with -O/--only parameter.\n");
      $withouts[] = $without;
    }
    elseif($argv[$i]=='--only' || $argv[$i]=='-O') {
      $i++;
      if ($only)
        die("You could specify only on -O/--only parameter.\n");
      $only = strtolower($argv[$i]);
      if (!in_array($only, $available_onlys))
        die("Invalid -O/--only parameter. Must be one of the following values : '".implode("','", $available_onlys)."'.\n");
      elseif ($without)
        die("You could not use only -O/--only parameter combined with -W/--without parameter.\n");
    }
    elseif($argv[$i]=='--copy-original-value' || $argv[$i]=='-c') {
      $copyoriginalvalue=True;
    }
    elseif($argv[$i]=='--interactive' || $argv[$i]=='-i') {
      $interactive=True;
    }
    elseif($argv[$i]=='--additional-file-format' || $argv[$i]=='-a') {
      $additionalfileformat=True;
    }
    elseif($argv[$i]=='--lang' || $argv[$i]=='-l') {
      $i++;
      $parse_lang=explode('.',$argv[$i]);
      if (count($parse_lang)==2) {
        $lang=$parse_lang[0];
        $encoding=$parse_lang[1];
      }
      else {
        die("Invalid --lang parameter. Must be compose in format : [lang].[encoding]\n");
      }
    }
    elseif($argv[$i]=='--output' || $argv[$i]=='-o') {
      $i++;
      $output = $argv[$i];
    }
    elseif($argv[$i]=='--format' || $argv[$i]=='-f') {
      $i++;
      $format = strtolower($argv[$i]);
      if (!in_array($format, $available_formats)) {
        die("Invalid -f/--format parameter. Must be one of the following values : '".implode("','", $available_formats)."'.\n");
      }
    }
    elseif($argv[$i]=='--debug' || $argv[$i]=='-d') {
      $debug = true;
    }
    elseif($argv[$i]=='-h') {
      usage();
    }
    else {
      $path = realtive_path($argv[$i]);
      if (is_file($path))
        $load_files[] = $path;
      else
        usage($argv[$i]." : Invalid lang file to load.", 1);
    }
  }
}

$data=array();

function debug($msg) {
  global $debug, $output;
  if (!$debug) return true;
  $fd = ($output?STDOUT: STDERR);
  fwrite($fd, "$msg\n");
}

function add($msg, $context=null) {
  debug("add($msg, $context)");
  if ($msg!='' && _($msg) == "$msg") {
    global $data, $translations, $interactive, $copyoriginalvalue, $format;

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
    elseif (_($msg) != $msg) {
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
LSsession :: initialize($lang,$encoding);

// Load lang string if lang was specify
if ($lang && $encoding) {
  foreach($GLOBALS['LSlang'] as $msg => $trans) {
    $translations[$msg]=$trans;
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
          add($t, "$file:$count");
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

  find_and_parse_template_file(LS_TEMPLATES_DIR);
  find_and_parse_template_file(LS_LOCAL_DIR.LS_TEMPLATES_DIR);
}

/*
 * Manage addons files
 */

if (!in_array('addons', $withouts) && (!$only || $only == 'addons')) {
  function parse_addon_file($file) {
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
        if (!empty($res)) add($res, "$file:$count");
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

  find_and_parse_addon_file(LS_ADDONS_DIR);
  find_and_parse_addon_file(LS_LOCAL_DIR.LS_ADDONS_DIR);
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
  $output = realtive_path($output);
  try {
    debug("Open output file ($output)");
    $fd = fopen($output, 'w');
  }
  catch(Exception $e) {
    fwrite(STDERR, 'Error occured opening output file : '.$e->getMessage(), "\n");
  }
  if (!$fd) {
    fwrite(STDERR, "Use stdout out instead.\n");
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
if ($output) {
  debug("Close output file ($output)");
  fclose($fd);
}

exit(0);
