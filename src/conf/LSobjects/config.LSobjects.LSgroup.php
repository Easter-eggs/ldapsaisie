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

$GLOBALS['LSobjects']['LSgroup'] = array (
  'objectclass' => array(
    'lsgroup',
    'posixGroup'
  ),
  'rdn' => 'cn',
  'container_dn' => 'ou=groups',
  'container_auto_create' => array(
    'objectclass' => array(
      'top',
      'organizationalUnit'
    ),
    'attrs' => array(
      'ou' => 'groups'
    )
  ),
  'display_name_format' => '%{cn}',
  'label' => 'Groups',
  'attrs' => array (

    /* ----------- start -----------*/
    'cn' => array (
      'label' => 'Name',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric' => array(
          'msg' => 'Name must contain alphanumeric values only.'
        ),
      ),
      'validation' => array (
        array (
          'filter' => 'cn=%{val}',
          'result' => 0
        )
      ),
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'r'
      ),
      'form' => array (
        'modify' => 1,
        'create' => 1
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'gidNumber' => array (
      'label' => 'Identifier',
      'ldap_type' => 'numeric',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_gidNumber_withSambaDomainObject',
      'validation' => array (
        array (
          'filter' => 'gidNumber=%{val}',
          'result' => 0
        )
      ),
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w'
      ),
      'form' => array (
        'modify' => 1
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'uniqueMember' => array (
      'label' => 'Members',
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'html_options' => array(
        'selectable_object' => array(
          array(
            'object_type' => 'LSpeople',                      // Nom de l'objet à lister
            'display_name_format' => '%{cn} (%{uidNumber})',  // Spécifie le attributs à lister pour le choix,
                                                              // si non définie => utilisation du 'display_name_format'
                                                              // de la définition de l'objet

            'value_attribute' => 'dn',                        // Spécifie le attributs dont la valeur sera retournée par
          ),
          array(
            'object_type' => 'LSsysaccount',
            'value_attribute' => 'dn',
          )
        ),
        'ordered' => true,
      ),
      'required' => 0,
      'multiple' => 1,
      'validation' => array (
        array (
          'basedn' => '%{val}',
          'result' => 1
        )
      ),
      'view' => 1,
      'rights' => array(
        'admin' => 'w',
        'admingroup' => 'w',
        'godfather' => 'w'
      ),
      'form' => array (
        'modify' => 1,
        'create' => 1
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'description' => array (
      'label' => 'Description',
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'multiple' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'r'
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
      'label' => 'Accountable(s)',
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'html_options' => array (
        'selectable_object' => array(
            'object_type' => 'LSpeople',
            'value_attribute' => 'dn'
        ),
      ),
      'validation' => array (
        array (
          'basedn' => '%{val}',
          'result' => 1,
          'msg' => "One or several of these users don't exist."
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
    ),
    /* ----------- end -----------*/

  )
);
