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

$GLOBALS['LSobjects']['LSsysaccount'] = array (
  'objectclass' => array(
    'top',
    'lssysaccount',
  ),
  'rdn' => 'uid',
  'container_dn' => 'ou=sysaccounts',

  'display_name_format' => '%{uid}',
  'label' => 'System accounts',

  'LSsearch' => array (
    'attrs' => array (
      'uid',
      'description',
    ),
  ),

  // LSrelation
  'LSrelation' => array(
    'groups' => array(
      'label' => 'Belongs to groups ...',
      'emptyText' => "Doesn't belong to any group.",
      'LSobject' => 'LSgroup',
      'linkAttribute' => "uniqueMember",
      'linkAttributeValue' => "dn",
      'rights' => array(
        'admin' => 'w',
        'admingroup' => 'w',
        'LSsysaccount' => 'r',
      ),
    ),
  ),

  // Attributes
  'attrs' => array (

    /* ----------- start -----------*/
    'uid' => array (
      'label' => 'Identifier',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'regex' => array(
          'msg' => "Identifier must contain alphanumeric values, dots (.) and dashes (-) only.",
          'params' => array('regex' => '/^[a-zA-Z0-9-_\.]*$/')
        ),
      ),
      'validation' => array (
        array (
          'filter' => 'uid=%{val}',
          'result' => 0,
          'msg' => 'This identifier is already used.',
          'except_current_object' => true,
        )
      ),
      'rights' => array(
        'self' => 'r',
        'LSsysaccount' => 'r',
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'userPassword' => array (
      'label' => 'Password',
      'ldap_type' => 'password',
      'ldap_options' => array (
        'encode' => 'ssha'
      ),
      'html_type' => 'password',
      'html_options' => array(
        'generationTool' => true,
        'viewHash' => true,
        'autoGenerate' => false,
        'lenght' => 12,
        'chars' => array (
          array(
            'nb' => 3,
            'chars' => 'abcdefijklmnopqrstuvwxyz'
          ),
          '0123456789',
          '*$.:/_-[]{}=~'
        ),
      ),
      'check_data' => array(
        'password' => array(
          'msg' => 'This password must contain at least 12 characters.',
          'params' => array(
            'minLength' => 12,
          )
        )
      ),
      'required' => 1,
      'rights' => array(
        'self' => 'w',
        'admin' => 'w'
      ),
      'form' => array (
        'modify' => 1,
        'create' => 1,
        'lostPassword' => 1
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'description' => array (
      'label' => 'Description',
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'multiple' => 1,
      'rights' => array(
        'self' => 'r',
        'LSsysaccount' => 'r',
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      )
    ),
    /* ----------- end -----------*/

  ) // Fin args
);
