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
  'orderby' => 'displayValue',  // Valeurs possibles : 'displayValue' ou 'subDn'
  'rdn' => 'uid',
  'container_dn' => 'ou=people',
  
  'container_auto_create' => array(
    'objectclass' => array(
      'top',
      'organizationalUnit'
    ),
    'attrs' => array(
      'ou' => 'people'
    )
  ),
  
  'before_modify' => 'valid',
  'after_modify' => 'valid',
  //'after_create' => 'createMaildirByFTP',
  //'after_delete' => 'removeMaildirByFTP',
  'display_name_format' => '%{cn}',
  'label' => _('Utilisateurs'),
  
  // LSrelation
  'relations' => array(
    'groups' => array(
      'label' => _('Appartient aux groupes...'),
      'emptyText' => _("N'appartient à aucun groupe."),
      'LSobject' => 'LSeegroup',
      'list_function' => 'listUserGroups',
      'getkeyvalue_function' => 'getMemberKeyValue',
      'update_function' => 'updateUserGroups',
      'remove_function' => 'deleteOneMember',
      'rename_function' => 'renameOneMember',
      'rights' => array(
        'self' => 'r',
        'admin' => 'w'
      )
    )
  ),
  
  // LSform
  'LSform' => array (
    // Layout
    'layout' => array (
      'Civilite' => array(
        'label' => 'Civilité',
        'img' => 1, 
        'args' => array (
          'uid',
          'personalTitle',
          'givenName',
          'sn',
          'cn',
          'mail',
          'userPassword',
          'description',
          'jpegPhoto',
          'lsGodfatherDn'
        )
      ),
      'Posix' => array (
        'label' => 'Posix',
        'args' => array (
          'uidNumber',
          'gidNumber',
          'loginShell',
          'homeDirectory'
        )
      ),
      'Samba' => array (
        'label' => 'Samba',
        'args' => array (
          'sambaSID',
          'sambaLMPassword',
          'sambaNTPassword'
        )
      )
    ) // fin Layout
  ), // fin LSform
  
  // Attributes
  'attrs' => array (
  
    /* ----------- start -----------*/
    'uid' => array (
      'label' => _('Identifiant'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'html_options' => array(
        'generate_value_format' => '%{givenName:1}.%{sn}',
        'autoGenerateOnModify' => true,   // default : false
        'withoutAccent' => 1,
        'replaceSpaces' => '.',
        'lowerCase' => 1
      ),
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
          'msg' => _('Cet identifiant est déjà utilisé.')
        )
      ),
      'rights' => array(
        'self' => 'r',
        'admin' => 'w',
        'godfather' => 'r'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      ),
      'dependAttrs' => array(
        'homeDirectory'
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'uidNumber' => array (
      'label' => _('Identifiant (numérique)'),
      'ldap_type' => 'numeric',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_uidNumber',
      'check_data' => array (
        'numeric' => array(
          'msg' => _("L'identifiant unique doit être un entier.")
        ),
      ),
      'validation' => array (
        array (
          'filter' => 'uidNumber=%{val}',
          'result' => 0,
          'msg' => _('Cet uid est déjà utilisé.')
        )
      ),
      'rights' => array(
        'admin' => 'w'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 0,
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
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
      'rights' => array(
        'self' => 'r',
        'users' => 'r',
        'admin' => 'w',
        'godfather' => 'w'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      ),
      'onDisplay' => 'return_data'
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sn' => array (
      'label' => _('Nom'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'rights' => array(
        'self' => 'r',
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
    'cn' => array (
      'label' => _('Nom complet'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'html_options' => array(
        'generate_value_format' => '%{givenName} %{sn}',
        'autoGenerateOnModify' => true,   // default : false
        'autoGenerateOnCreate' => true    // default : false
      ),
      'required' => 1,
      'validation' => 'valid',
      'rights' => array(
        'self' => 'r',
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
    'gidNumber' => array (
      'label' => _('Groupe principal'),
      'ldap_type' => 'numeric',
      'html_type' => 'select_list',
      'multiple' => false,
      'required' => 1,
      'validation' => array (
        array (
          'msg' => _("Ce groupe n'existe pas."),
          'object_type' => 'LSeegroup',           // 'object_type' : Permet definir le type d'objet recherchés
          //'basedn' => 'o=company',                    // et d'utiliser les objectClass définis dans le fichier de configuration
          'filter' => '(gidNumber=%{val})',       // pour la recherche
          'result' => 1
        )
      ),
      'rights' => array(
        'admin' => 'w',
        'godfather' => 'r'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      ),
      'possible_values' => array(
        'OTHER_OBJECT' => array(
          'object_type' => 'LSeegroup',                      // Nom de l'objet à lister
          'display_attribute' => '%{cn} (%{gidNumber})',     // Spécifie le attributs à lister pour le choix,
                                                             // si non définie => utilisation du 'select_display_attrs'
                                                             // de la définition de l'objet
                                              
          'value_attribute' => 'gidNumber',   // Spécifie le attributs dont la valeur sera retournée par
          /*'filter' =>                         // le formulaire spécifie les filtres de recherche pour
            array (                           // l'établissement de la liste d'objets :
              array(                          // Premier filtre
                'filter' => 'cn=*a*',
                //'basedn' => 'o=company',
                'scope' => 'sub',
              )
            )*/
        )
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'loginShell' => array (
      'label' => _('Interpreteur de commande'),
      'help_info' => _("Permet ou non a l'utilisateur de se connecter à un système POSIX."),
      'ldap_type' => 'boolean',
      'html_type' => 'boolean',
      'required' => 1,
      'default_value' => 'no',
      'rights' => array(
        'admin' => 'w'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      ),
      'true_value' => '/bin/bash',
      'false_value' => '/bin/false'
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaSID' => array (
      'label' => _('Identifiant Samba'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaSID',
      'rights' => array(
        'admin' => 'r'
      ),
      'form' => array (
        'modify' => 0
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'homeDirectory' => array (
      'label' => _('Répertoire personnel'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_homeDirectory',
      'rights' => array(
        'admin' => 'r'
      ),
      'view' => 1
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'mail' => array (
      'label' => _('Adresse e-mail'),
      'ldap_type' => 'ascii',
      'html_type' => 'mail',
      'html_options' => array(
        'generate_value_format' => '%{givenName}.%{sn}@ls.com',
        'withoutAccent' => 1,
        'replaceSpaces' => '.',
        'lowerCase' => 1
      ),
      'required' => 1,
      'check_data' => array (
        'email' => array(
          'msg' => _("L'adresse e-mail entrée n'est pas valide."),
          'params' => array('checkDomain' => false)
        ),
      ),
      'rights' => array(
        'self' => 'r',
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
    'personalTitle' => array (
      'label' => _('Titre'),
      'ldap_type' => 'ascii',
      'html_type' => 'select_list',
      'required' => 1,
      'default_value' => 'M.',
      'rights' => array(
        'self' => 'r',
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'w'
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1
      ),
      'possible_values' => array(
        'M.' => 'M.',
        'Mme' => 'Mme',
        'Mlle' => 'Mlle'
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'description' => array (
      'label' => _('Description'),
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'multiple' => 1,
      'rights' => array(
        'self' => 'r',
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
    'userPassword' => array (
      'label' => _('Mot de passe'),
      'ldap_type' => 'password',
      'ldap_options' => array (
        'encode' => 'md5'
      ),
      'html_type' => 'password',
      'html_options' => array(
        'generationTool' => true,
        'autoGenerate' => false,
        'lenght' => 8,
        'chars' => array (
          array(
            'nb' => 3,
            'chars' => 'abcdefijklmnopqrstuvwxyz'
          ),
          '0123456789',
          '*$.:/_-[]{}=~'
        ),
        'mail' => array(
          'send' => 1,
          'ask' => 1,
          'subject' => "LSexample : votre nouveau mot de passe",
          'msg' => "Votre mot de passe vient d'etre modifie.\nNouveau mot de passe : %{mdp}",
          'mail_attr' => 'mail'
        )
      ),
      'check_data' => array(
        'password' => array(
          'msg' => 'Le mot de passe doit contenir entre 8 et 10 caractères.',
          'params' => array(
            'minLength' => 8,
            'maxLength' => 10
          )
        )
      ),
      'required' => 1,
      'rights' => array(
        'self' => 'w',
        'admin' => 'w'
      ),
      'dependAttrs' => array(
        'sambaLMPassword',
        'sambaNTPassword'
      ),
      'form' => array (
        'modify' => 1,
        'create' => 1,
        'lostPassword' => 1
      ),
      'after_modify' => 'valid'
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'lsRecoveryHash' => array (
      'label' => _('Hash de recouvrement du mot de passe'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 0,
      'form' => array (
        'lostPassword' => 1
      ),
      'rights' => array(
        'self' => 'w',
        'admin' => 'w'
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaLMPassword' => array (
      'label' => _('Mot de passe Samba (LM)'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaLMPassword',
      'form' => array (
        'modify' => 0
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaNTPassword' => array (
      'label' => _('Mot de passe Samba (NT)'),
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaNTPassword',
      'form' => array (
        'modify' => 0
      )
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'jpegPhoto' => array (
      'label' => _('Photo'),
      'ldap_type' => 'image',
      'html_type' => 'image',
      'required' => 0,
      'view' => 0,
      'check_data' => array (
        'imagesize' => array(
          'msg' => _("La taille de l'image n'est pas valide."),
          'params' => array(
            'maxWidth' => 2000
          )
        ),
        'imagefilesize' => array(
          'msg' => _("La taille du fichier image n'est pas valide."),
          'params' => array(
            'maxSize' => 3000000   // taille du fichier en octets
          )
        ),
        'imagefile' => array(
          'msg' => _("Le type du fichier n'est pas valide.")
        )
      ),
      'form' => array (
        'modify' => 1
      ),
      'rights' => array(
        'self' => 'w',
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'w'
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
    ),
    /* ----------- end -----------*/

  ) // Fin args
);
?>
