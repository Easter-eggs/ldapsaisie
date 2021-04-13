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

$GLOBALS['LSobjects']['LScompany'] = array (
  'objectclass' => array(
    'top',
    'lscompany',
  ),
  'rdn' => 'ou',
  'container_dn' => 'ou=companies',
  'display_name_format' => '%{ou}',
  'label' => 'Companies',

  'customActions' => array (
    'showTechInfo' => array (
      'function' => 'showTechInfo',
      'label' => 'Show technical information',
      'hideLabel' => True,
      'noConfirmation' => true,
      'disableOnSuccessMsg' => true,
      'icon' => 'tech_info',
      'rights' => array (
        'admin',
      ),
    ),
  ),

  'attrs' => array (

    /* ----------- start -----------*/
    'ou' => array (
      'label' => 'Name',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric' => NULL,
      ),
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
      ),
      'form' => array (
        'modify' => 0,
        'create' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'description' => array (
      'label' => 'Description',
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'required' => 0,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'lsGodfatherDn' => array (
      'label' => 'Accountable(s)',
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'html_options' => array(
        'selectable_object' => array(
          'object_type' => 'LSpeople',
          'value_attribute' => 'dn',
        ),
      ),
      'validation' => array (
        array (
          'basedn' => '%{val}',
          'result' => 1,
          'msg' => "One or several of these users don't exist.",
        ),
      ),
      'multiple' => 1,
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
    ),
    /* ----------- end -----------*/
  ),
);
