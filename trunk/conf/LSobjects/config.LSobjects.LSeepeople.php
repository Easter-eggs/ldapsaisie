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
    'top',
    'lspeople',
    'posixAccount',
    'sambaSamAccount',
  ),
  'rdn' => 'uid',
  'container_dn' => 'ou=people',
  'before_save' => 'valid',
  'after_save' => 'valid',
  'select_display_attrs' => '%{cn}',
	// Attributes
  'attrs' => array (
    'uid' => array (
      'label' => _('Identifiant'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric' => array(
          'msg' => _("L'identifiant ne doit comporter que des lettres et des chiffres.")
        ),
      ),
      'validation' => array (
        array (
          'filter' => 'uid=%{val}',
          'result' => 0,
          'msg' => _('Cet identifiant est d�j� utilis�.')
        )
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 0,
        'add' => 1
      )
    ),
    'uidNumber' => array (
      'label' => _('Identifiant (num�rique)'),
      'ldap_type' => 'numeric',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_uidNumber',
      'check_data' => array (
        'numeric' => array(
          'msg' => _("L'identifiant unique doit �tre un entier.")
        ),
      ),
      'validation' => array (
        array (
          'filter' => 'uidNumber=%{val}',
          'result' => 0,
          'msg' => _('Cet uid est d�j� utilis�.')
        )
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 0,
      )
    ),
    'cn' => array (
      'label' => _('Nom complet'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'default_value' => 'titi',
      'validation' => 'valid',
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      )
    ),
    'givenName' => array (
      'label' => _('Prenom'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'default_value' => 'toto',
      'check_data' => array (
        'alphanumeric' => array(
          'msg' => _('Le prenom ne doit comporter que des lettres et des chiffres.')
        ),
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
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
      'label' => _('Nom'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      )
    ),
    'gidNumber' => array (
      'label' => _('Groupe principal'),
      'ldap_type' => 'numeric',
      'html_type' => 'select_list',
			'multiple' => true,
      'required' => 1,
      'validation' => array (
        array (
          'object_type' => 'LSeegroup',           // 'object_type' : Permet definir le type d'objet recherch�s
          //'basedn' => 'o=company',                    // et d'utiliser les objectClass d�finis dans le fichier de configuration
          'filter' => '(gidNumber=%{val})',       // pour la recherche
          'result' => 1
        )
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      ),
      'possible_values' => array(
        'OTHER_OBJECT' => array(
          'object_type' => 'LSeegroup',                      // Nom de l'objet � lister
          'display_attribute' => '%{cn} (%{gidNumber})',     // Sp�cifie le attributs � lister pour le choix,
                                                             // si non d�finie => utilisation du 'select_display_attrs'
                                                             // de la d�finition de l'objet
                                              
          'value_attribute' => 'gidNumber',   // Sp�cifie le attributs dont la valeur sera retourn�e par
          'filter' =>                         // le formulaire sp�cifie les filtres de recherche pour
            array (                           // l'�tablissement de la liste d'objets :
              array(                          // Premier filtre
                'filter' => 'cn=*a*',
                //'basedn' => 'o=company',
                'scope' => 'sub',
              )
            )
        )
      )
    ),
    'loginShell' => array (
      'label' => _('Interpreteur de commande'),
      'ldap_type' => 'ascii',
      'html_type' => 'select_list',
      'required' => 1,
      'default_value' => '/bin/false',
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      ),
      'possible_values' => array(
        '/bin/false' => _('Aucun'),
        '/bin/bash' => 'Bash',
      )
    ),
    'sambaSID' => array (
      'label' => _('Identifiant Samba'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaSID',
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'r',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        //'test' => 0,
      )
    ),
    'homeDirectory' => array (
      'label' => _('R�pertoire personnel'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'default_value' => '/home/%{uid}',
			'generate_function' => 'generate_homeDirectory',
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'r',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
      )
    ),
    'mail' => array (
      'label' => _('Adresse e-mail'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'email' => array(
          'msg' => _("L'adresse e-mail entr�e n'est pas valide.")
        ),
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'r',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      )
    ),
    'personalTitle' => array (
      'label' => _('Titre'),
      'ldap_type' => 'ascii',
      'html_type' => 'select_list',
      'required' => 1,
      'default_value' => 'M.',
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
        'add' => 1
      ),
      'possible_values' => array(
        'M.' => 'M.',
        'Mme' => 'Mme',
        'Mlle' => 'Mlle'
      )
    ),
    'maildrop' => array (
      'label' => _('Mail ind�sirable'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
			'multiple' => true,
      'check_data' => array (
        'email' => array(
          'msg' => _("L'adresse e-mail entr�e n'est pas valide.")
        ),
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
      )
    ),
    'vacationActive' => array (
      'label' => _('R�ponse automatique'),
      'ldap_type' => 'ascii',
      'html_type' => 'select_list',
      'default_value' => '',
      'check_data' => array (
        'email' => array(
          'msg' => _("L'adresse e-mail entr�e n'est pas valide.")
        ),
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
      ),
      'possible_values' => array(
        '%{uid}@autoreponse.example.fr' => 'Oui',
        '' => 'Non'
      )
    ),
    'vacationInfo' => array (
      'label' => _('Message en reponse'),
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
			'multiple' => true,
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
      )
    ),
    'vacationForward' => array (
      'label' => _('Transfert de mail'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'check_data' => array (
        'email' => array(
          'msg' => _("L'adresse e-mail entr�e n'est pas valide.")
        ),
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
      )
    ),
    'mailQuota' => array (
      'label' => _('Quota boite mail'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'check_data' => array (
        'numeric' => array(
          'msg' => _("Le quota de l'adresse mail entr�e n'est pas valide.")
        ),
      ),
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'r',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
      )
    ),
    'description' => array (
      'label' => _('Description'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
      'form' => array (
        'test' => 1,
      )
    ),
    'userPassword' => array (
      'label' => _('Mot de passe'),
      'ldap_type' => 'password',
      'html_type' => 'password',
			'required' => 1,
      'rights' => array(                  // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                    // d�finition des droits de l'utilisateur sur lui m�me
        'users' => 'r'                    // d�finition des droits de tout les utilisateurs
      ),
			'dependAttrs' => array(
				'sambaLMPassword',
				'sambaNTPassword'
			),
      'form' => array (
        'test' => 1,
        'add' => 1
      )
    ),
    'sambaLMPassword' => array (
      'label' => _('Mot de passe Samba (LM)'),
      'ldap_type' => 'ascii',
      'html_type' => 'password',
			'required' => 1,
      'generate_function' => 'generate_sambaLMPassword',
      'rights' => array(                 // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                   // d�finition des droits de l'utilisateur sur lui m�me
        'users' => ''                    // d�finition des droits de tout les utilisateurs
      )
		),
    'sambaNTPassword' => array (
      'label' => _('Mot de passe Samba (NT)'),
      'ldap_type' => 'ascii',
      'html_type' => 'password',
			'required' => 1,
      'generate_function' => 'generate_sambaNTPassword',
      'rights' => array(                 // D�finition de droits : 'r' => lecture / 'w' => modification / '' => aucun (par defaut)
        'self' => 'w',                   // d�finition des droits de l'utilisateur sur lui m�me
        'users' => ''                    // d�finition des droits de tout les utilisateurs
      )
    )
	)
);
?>
