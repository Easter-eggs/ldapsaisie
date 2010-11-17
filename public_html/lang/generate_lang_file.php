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

require_once('../core.php');
require_once('../conf/config.inc.php');

if (isset($argv[2]) && is_file($argv[2])) {
  @include($argv[2]);
}

$data=array();

function add($msg) {
  if ($msg!='') {
    global $data;
    $data[$msg]=$GLOBALS['LSlang'][$msg];
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
if (loadDir('../'.LS_OBJECTS_DIR)) {
  foreach($GLOBALS['LSobjects'] as $name => $conf) {
    add($conf['label']);
    
    // LSrelation
    if (is_array($conf['LSrelation'])) {
      foreach($conf['LSrelation'] as $rel) {
        add($rel['label']);
        add($rel['emptyText']);
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

    
    if(is_array($conf['attrs'])) {
      foreach($conf['attrs'] as $attr) {
        add($attr['label']);
        add($attr['help_info']);
        add($attr['html_options']['mail']['subject']);
        add($attr['html_options']['mail']['msg']);
        
        // LSattr_html_select_list
        if ($attr['html_type']=='select_list' && is_array($attr['html_options']['possible_values'])) {
          foreach($attr['html_options']['possible_values'] as $pkey => $pname) {
            if ($pkey != 'OTHER_OBJECT') {
              add($pname);
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

ksort($data);

echo "<?php\n\n\$GLOBALS['LSlang'] = array (\n";

foreach($data as $key => $val) {
  print "\n\"$key\" =>\n  \"$val\",\n";
}

echo "\n);\n\n?>\n";

?>
