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

$GLOBALS['LSobjects']['LSeecompany'] = array (
  'objectclass' => array(
    'top',
    'lscompany'
  ),
  'rdn' => 'ou',
  'orderby' => 'displayName',  // Valeurs possibles : 'displayName' ou 'subDn'
  'container_dn' => 'ou=companies',
  'display_name_format' => '%{ou}',
  'label' => _('Sociétés'),
  
  'attrs' => array (
  
    /* ----------- start -----------*/
    'ou' => array (
      'label' => _('Nom'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric' => NULL
      ),
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w'
      ),
      'form' => array (
        'modify' => 0,
        'create' => 1
      )
    ),
    /* ----------- end -----------*/
    
    /* ----------- start -----------*/
    'description' => array (
      'label' => _('Description'),
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'required' => 0,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'w'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      )
    ),
    /* ----------- end -----------*/
    
    /* ----------- start -----------*/
    'lsGodfatherDn' => array (
      'label' => _('Parrain(s)'),
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'selectable_object' => array(
          'object_type' => 'LSeepeople',
          'value_attribute' => '%{dn}'
      ),
      'validation' => array (
        array (
          'basedn' => '%{val}',
          'result' => 1,
          'msg' => _("Un ou plusieurs de ces utilisateurs n'existent pas.")
        )
      ),
      'multiple' => 1,
      'rights' => array(
        'admin' => 'w'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      )
    )
    /* ----------- end -----------*/
  )
);
?>
