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

$GLOBALS['LSobjects']['LSeepeople'] = array (
  'objectclass' => array(
    'ostpeople',
    'posixAccount'
  ),
  'before_save' => 'valid',
  'after_save' => 'valid',
  'select_display_attrs' => '%{cn]',
  'attrs' => array (
    'uid' => array (
      'label' => 'Identifiant',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric' => array(
          'msg' => "L'identifiant ne doit comporter que des lettres et des chiffres."
        ),
      ),
      'validation' => array (
        array (
          'basedn' => 'o=ost',
          'filter' => 'uid=%{val}',
          'result' => 0,
          //~ 'msg' => 'Cet identifiant est d�j� utilis�.'
        )
      ),
      'rights' => array(                      // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 0,
        'add' => 1
      )
    ),
    'uidNumber' => array (
      'label' => 'Identifiant (num�rique)',
      'ldap_type' => 'numeric',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'numeric' => array(
          'msg' => "L'identifiant unique doit �tre un entier."
        ),
      ),
      'validation' => array (
        array (
          'basedn' => 'o=ost',
          'filter' => 'uidNumber=%{val}',
          'result' => 0,
          //~ 'msg' => 'Cet identifiant est d�j� utilis�.'
        )
      ),
      'rights' => array(                      // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 0,
        'add' => 1
      )
    ),
    'cn' => array (
      'label' => 'Nom complet',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'validation' => 'valid',
      'rights' => array(                      // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      )
    ),
    'givenName' => array (
      'label' => 'Prenom',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'default_value' => 'toto',
      'check_data' => array (
        'alphanumeric' => array(
          'msg' => 'Le prenom ne doit comporter que des lettres et des chiffres.'
        ),
      ),
      //~ 'validation' => array (
        //~ array (
          //~ 'basedn' => 'o=ost',
          //~ 'filter' => 'uid=%{uid}',
          //~ 'result' => 0
        //~ )
      //~ ),
      'rights' => array(                      // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      ),
      'onDisplay' => 'return_data'
    ),
    'sn' => array (
      'label' => 'Nom',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric' => array(
          'msg' => 'Le nom ne doit comporter que des lettres et des chiffres.'
        ),
      ),
      //~ 'validation' => array (
        //~ array (
          //~ 'basedn' => 'o=ost',
          //~ 'filter' => 'uid=%{uid}',
          //~ 'result' => 0
        //~ )
      //~ ),
      'rights' => array(                      // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      )
    ),
    'gidNumber' => array (
      'label' => 'Groupe principal',
      'ldap_type' => 'numeric',
      'html_type' => 'select_list',
      'required' => 1,
      'validation' => array (
        array (
          'object_type' => 'LSeegroup',           // 'object_type' : Permet definir le type d'objet recherch�s
          'basedn' => 'o=ost',                    //  et d'utiliser les objectClass d�finis dans le fichier de configuration
          'filter' => '(gidNumber=%{val})', // pour la recherche
          'result' => 1
        )
      ),
      'rights' => array(                      // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      ),
      'possible_values' => array(
        'aucun' => '-- Selectionner --',
        'OTHER_OBJECT' => array(
          'object_type' => 'LSeegroup',         // Nom de l'objet � lister
          'display_attribute' => '%{cn} (%{gidNumber})',     // Sp�cifie le attributs � lister pour le choix,
                                              // si non d�finie => utilisation du 'select_display_attrs'
                                              // de la d�finition de l'objet
                                              
          'value_attribute' => 'gidNumber',    // Sp�cifie le attributs dont la valeur sera retourn�e par
          'filter' =>                         // le formulaire sp�cifie les filtres de recherche pour
            array (                           // l'�tablissement de la liste d'objets :
              array(                          // Premier filtre
                'filter' => 'cn=*a*',
                'basedn' => 'o=ost',
                'scope' => 'sub',
                //~ 'attr' => '[attribut]',      // Si 'attr' est d�finis, on effectura pour chacune des 
                                             // valeurs de l'attribut correspants une recherche avec 
                                             // le filtre suivant compos� avec la valeur de cette attribut
              )
              //~ array(
                //~ 'filter' => '[format sprintf]',
                //~ 'basedn' => '[basedn]',
              //~ ),
              //~ ...
            )
          //~ 'basedn' =>
            //~ '[basedn]'
        )
      )
    )
  )
);
?>