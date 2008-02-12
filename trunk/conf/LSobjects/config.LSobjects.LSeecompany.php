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
    'lscompany'
  ),
  'rdn' => 'o',
  'container_dn' => 'ou=companies',
  'select_display_attrs' => '%{dc}',
  'label' => _('Soci�t�s'),
  'attrs' => array (
    'o' => array (
      'label' => _('Nom'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric'
      ),
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w'
      ),
      'form' => array (
        'modify' => 1,
        'create' => 1
      )
    ),
    'dc' => array (
      'label' => _('Domaine'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      )
    )
  )
);
?>
