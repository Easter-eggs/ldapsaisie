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

$GLOBALS['LSobjects']['LSeegroup'] = array (
  'objectclass' => array(
    'lsgroup',
    'posixGroup'
  ),
  'rdn' => 'cn',
  'container_dn' => 'ou=groups',
  'select_display_attrs' => '%{cn}',
  'attrs' => array (
    'cn' => array (
      'label' => _('Nom'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric'
      ),
      'validation' => array (
        array (
          'filter' => 'cn=%{val}',
          'result' => 0
        )
      ),
      'rights' => array(                  // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1
      )
    ),
    'gidNumber' => array (
      'label' => _('Identifiant'),
      'ldap_type' => 'numeric',
      'html_type' => 'text',
      'required' => 1,
      'validation' => array (
        array (
          'filter' => 'gidNumber=%{val}',
          'result' => 0
        )
      ),
      'rights' => array(                  // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1
      )
    ),
    'uniqueMember' => array (
      'label' => _('Membres'),
      'ldap_type' => 'ascii',
      'html_type' => 'select_list',
      'required' => 0,
      'validation' => array (
        array (
          'basedn' => '%{val}',
          'result' => 1
        )
      ),
      'rights' => array(                  // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1
      ),
      'possible_values' => array(
        'aucun' => _('-- Selectionner --'),
        'OTHER_OBJECT' => array(
          'object_type' => 'LSeepeople',   						  			// Nom de l'objet à lister
          'display_attribute' => '%{cn} (%{uidNumber})',			// Spécifie le attributs à lister pour le choix,
                                            								  // si non définie => utilisation du 'select_display_attrs'
                                             								  // de la définition de l'objet
                                              
          'value_attribute' => '%{dn}',                       // Spécifie le attributs dont la valeur sera retournée par
        )
      )
    )
  )
);
?>
