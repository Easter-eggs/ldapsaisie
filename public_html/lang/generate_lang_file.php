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

$withoutselectlist=False;
$copyoriginalvalue=False;
$additionalfileformat=False;
$lang=False;
$encoding=False;
$translations=array();
if ($argc > 1) {
  // Change dir again to manage file input
  chdir($curdir);
  for ($i=1;$i<$argc;$i++) {
    if (is_file($argv[$i])) {
      @include($argv[$i]);
      foreach($GLOBALS['LSlang'] as $msg => $trans) {
        $translations[$msg]=$trans;
      }
    }
    elseif($argv[$i]=='--without-select-list') {
      $withoutselectlist=True;
    }
    elseif($argv[$i]=='--copy-original-value') {
      $copyoriginalvalue=True;
    }
    elseif($argv[$i]=='--additional-file-format') {
      $additionalfileformat=True;
    }
    elseif($argv[$i]=='--lang') {
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
    elseif($argv[$i]=='-h') {
      echo "Usage : ".$argv[0]." [file1] [file2] [-h] [options]\n";
      echo "  --without-select-list    Don't add possibles values of select list\n";
      echo "  --copy-original-value    Copy original value as translated value when no translated value exists\n";
      echo "  --additional-file-format Additional file format output\n";
      echo "  --lang                   Load this specify lang (format : [lang].[encoding])\n";
      exit(0);
    }
  }
  chdir(dirname(__FILE__).'/../');
}

$data=array();

function add($msg) {
  if ($msg!='' && _($msg) == "$msg") {
    global $data, $translations;
    $data[$msg]=$translations[$msg];
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

// LDAP Servers
foreach($GLOBALS['LSconfig']['ldap_servers'] as $conf) {
  add($conf['name']);
  add($conf['subDnLabel']);
  add($conf['recoverPassword']['recoveryHashMail']['subject']);
  add($conf['recoverPassword']['recoveryHashMail']['msg']);
  add($conf['recoverPassword']['newPasswordMail']['subject']);
  add($conf['recoverPassword']['newPasswordMail']['msg']);
  if (is_array($conf['subDn'])) {
    foreach($conf['subDn'] as $name => $cf) {
      if ($name!='LSobject') {
        add($name);
      }
    }
  }
}


// LSobject
if (loadDir(LS_OBJECTS_DIR) && loadDir(LS_LOCAL_DIR.LS_OBJECTS_DIR)) {
  foreach($GLOBALS['LSobjects'] as $name => $conf) {
    add($conf['label']);
    
    // LSrelation
    if (is_array($conf['LSrelation'])) {
      foreach($conf['LSrelation'] as $rel) {
        add($rel['label']);
        add($rel['emptyText']);
      }
    }
    // Custom Actions
    if (is_array($conf['customActions'])) {
      foreach($conf['customActions'] as $act) {
        add($act['label']);
        add($act['helpInfo']);
        add($act['question_format']);
        add($act['onSuccessMsgFormat']);
      }
    }

    // LSform
    if (is_array($conf['LSform']['layout'])) {
      foreach($conf['LSform']['layout'] as $lay) {
        add($lay['label']);
      }
    }
    if (is_array($conf['LSform']['dataEntryForm'])) {
      foreach($conf['LSform']['dataEntryForm'] as $def) {
        add($def['label']);
      }
    }
    // LSsearch
    if (is_array($conf['LSsearch']['predefinedFilters'])) {
      foreach($conf['LSsearch']['predefinedFilters'] as $lay) {
        add($lay);
      }
    }
    if (is_array($conf['LSsearch']['extraDisplayedColumns'])) {
      foreach($conf['LSsearch']['extraDisplayedColumns'] as $cid => $cconf) {
        add($cconf['label']);
      }
    }
    if (is_array($conf['LSsearch']['customActions'])) {
      foreach($conf['LSsearch']['customActions'] as $act) {
        add($act['label']);
        add($act['question_format']);
        add($act['onSuccessMsgFormat']);
      }
    }


    
    if(is_array($conf['attrs'])) {
      foreach($conf['attrs'] as $attr) {
        add($attr['label']);
        add($attr['help_info']);
        add($attr['no_value_label']);
        add($attr['html_options']['mail']['subject']);
        add($attr['html_options']['mail']['msg']);
        
        // LSattr_html_select_list
        if (($attr['html_type']=='select_list' || $attr['html_type']=='select_box') && is_array($attr['html_options']['possible_values']) && !$withoutselectlist) {
          foreach($attr['html_options']['possible_values'] as $pkey => $pname) {
            if (is_array($pname)) {
              add($pname['label']);
              if (is_array($pname['possible_values'])) {
                foreach($pname['possible_values'] as $pk => $pn) {
                  if ($pk == 'OTHER_OBJECT') continue;
                  add($pn);
                }
              }
            }
            elseif ($pkey != 'OTHER_OBJECT') {
              add($pname);
            }
          }
        }

        // LSattr_html_valueWithUnit
        if (is_array($attr['html_options']['units'])) {
          foreach($attr['html_options']['units'] as $pname) {
            add($pname);
          }
        }

	// LSattr_html_jsonCompositeAttribute
        if (is_array($attr['html_options']['components'])) {
          foreach($attr['html_options']['components'] as $c => $cconfig) {
            add($cconfig['label']);

            // Component type select_list
            if (is_array($cconfig['options']['possible_values'])) {
              foreach($cconfig['options']['possible_values'] as $pkey => $pname) {
                if (is_array($pname)) {
                  add($pname['label']);
                  if (is_array($pname['possible_values'])) {
                    foreach($pname['possible_values'] as $pk => $pn) {
                      if ($pk == 'OTHER_OBJECT') continue;
                      add($pn);
                    }
                  }
                }
                elseif ($pkey != 'OTHER_OBJECT') {
                  add($pname);
                }
              }
            }

            // Check data
            if (is_array($cconfig['check_data'])) {
              foreach($cconfig['check_data'] as $check) {
                add($check['msg']);
              }
            }
          }
        }
        
        // Check data
        if (is_array($attr['check_data'])) {
          foreach($attr['check_data'] as $check) {
            add($check['msg']);
          }
        }
        
        // validation
        if (is_array($attr['validation'])) {
          foreach($attr['validation'] as $valid) {
            add($valid['msg']);
          }
        }
      }
    }
  }
}

/*
 * Manage template file
 */

function parse_template_file($file) {
  foreach(file($file) as $line) {
    if (preg_match_all('/\{ *tr +msg=["\']([^\}]+)["\'] *\}/',$line,$matches)) {
      foreach($matches[1] as $t)
        add($t);
    }
  }
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

/*
 * Manage addons files
 */

function parse_addon_file($file) {
  foreach(file($file) as $line) {
    $offset=0;
    while ($pos = strpos($line,'__(',$offset)) {
      $quote='';
      $res='';
      for ($i=$pos+3;$i<strlen($line);$i++) {
        if (empty($quote)) {
          if ($line[$i]=='\\') {
            $i++;
          }
          elseif ($line[$i]=='"' || $line[$i]=="'") {
            $quote=$line[$i];
          }
        }
        elseif (!empty($quote)) {
          if ($line[$i]=='\\') {
            $res.=$line[$i];
            $i++;
            $res.=$line[$i];
          }
          elseif ($line[$i]==$quote) {
            $offset=$i;
            break;
          }
          else {
            $res.=$line[$i];
          }
        }
      }
      if (!empty($res)) add($res);
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


ksort($data);

echo "<?php\n\n";

if (!$additionalfileformat) print "\$GLOBALS['LSlang'] = array (\n";

foreach($data as $key => $val) {
  if ($copyoriginalvalue && $val=="") {
    $val=$key;
  }
  $key=str_replace('"','\\"',$key);
  $val=str_replace('"','\\"',$val);
  if ($additionalfileformat) {
    print "\$GLOBALS['LSlang'][\"$key\"] = \"$val\";\n";
  }
  else {
    print "\n\"$key\" =>\n  \"$val\",\n";
  }
}

if (!$additionalfileformat) echo "\n);\n";

?>
