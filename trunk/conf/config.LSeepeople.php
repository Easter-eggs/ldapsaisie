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
          //~ 'msg' => 'Cet identifiant est déjà utilisé.'
        )
      ),
      'rights' => array(                      // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 0,
        'add' => 1
      )
    ),
    'uidNumber' => array (
      'label' => 'Identifiant (numérique)',
      'ldap_type' => 'numeric',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'numeric' => array(
          'msg' => "L'identifiant unique doit être un entier."
        ),
      ),
      'validation' => array (
        array (
          'basedn' => 'o=ost',
          'filter' => 'uidNumber=%{val}',
          'result' => 0,
          //~ 'msg' => 'Cet identifiant est déjà utilisé.'
        )
      ),
      'rights' => array(                      // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
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
      'rights' => array(                      // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
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
      'rights' => array(                      // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
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
      'rights' => array(                      // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
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
          'object_type' => 'LSeegroup',           // 'object_type' : Permet definir le type d'objet recherchés
          'basedn' => 'o=ost',                    //  et d'utiliser les objectClass définis dans le fichier de configuration
          'filter' => '(gidNumber=%{val})', // pour la recherche
          'result' => 1
        )
      ),
      'rights' => array(                      // Définition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // définition des droits de l'utilisateur sur lui même
        'users' => 'r'                    // définition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      ),
      'possible_values' => array(
        'aucun' => '-- Selectionner --',
        'OTHER_OBJECT' => array(
          'object_type' => 'LSeegroup',         // Nom de l'objet à lister
          'display_attribute' => '%{cn} (%{gidNumber})',     // Spécifie le attributs à lister pour le choix,
                                              // si non définie => utilisation du 'select_display_attrs'
                                              // de la définition de l'objet
                                              
          'value_attribute' => 'gidNumber',    // Spécifie le attributs dont la valeur sera retournée par
          'filter' =>                         // le formulaire spécifie les filtres de recherche pour
            array (                           // l'établissement de la liste d'objets :
              array(                          // Premier filtre
                'filter' => 'cn=*a*',
                'basedn' => 'o=ost',
                'scope' => 'sub',
                //~ 'attr' => '[attribut]',      // Si 'attr' est définis, on effectura pour chacune des 
                                             // valeurs de l'attribut correspants une recherche avec 
                                             // le filtre suivant composé avec la valeur de cette attribut
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