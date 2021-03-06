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

LSsession :: includeFile(LS_OBJECTS_DIR.'config.LSobjects.common-pwdPolicyAccount.php');

$GLOBALS['LSobjects']['LSpeople'] = array (
  'objectclass' => array(
    'top',
    'lspeople',
    'posixAccount',
    'shadowAccount',
    'sambaSamAccount',
  ),
  'rdn' => 'uid',
  'container_dn' => 'ou=people',

  'container_auto_create' => array(
    'objectclass' => array(
      'top',
      'organizationalUnit',
    ),
    'attrs' => array(
      'ou' => 'people',
    ),
  ),

  'LSaddons' => array (
    'exportSearchResultAsCSV',
  ),

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

  'ioFormat' => array (
    'mycsv' => array (
      'label' => 'Simple CSV',
      'driver' => 'CSV',
      'driver_options' => array (
        'delimiter' => ';',
        'enclosure' => '"',
      ),
      'fields' => array (
        'login' => 'uid',
        'civility' => 'personalTitle',
        'firstname' => 'givenName',
        'name' => 'sn',
        'mail' => 'mail',
        'password' => 'userPassword',
        'gid' => 'gidNumber',
        'shell' => 'loginShell',
      ),
      'generated_fields' => array (
        'cn' => '%{personalTitle} %{givenName} %{sn}',
      ),
    ),
    'myfullcsv' => array (
      'label' => 'Complete CSV',
      'driver' => 'CSV',
      'fields' => array (
        'login' => 'uid',
        'civility' => 'personalTitle',
        'firstname' => 'givenName',
        'lastname' => 'sn',
        'fullname' => 'cn',
        'mail' => 'mail',
        'password' => 'userPassword',
        'description' => 'description',
        'accountables' => 'lsGodfatherDn',
        'posix_uidnumber' => 'uidNumber',
        'posix_gidnumber' => 'gidNumber',
        'posix_shell' => 'loginShell',
        'posix_home' => 'homeDirectory',
        'posix_gecos' => 'gecos',
        'password_expiration' => 'shadowExpire',
        'password_max_age' => 'shadowMax',
        'password_max_inactive' => 'shadowInactive',
        'password_last_change' => 'shadowLastChange',
        'samba_sid' => 'sambaSID',
        'samba_primary_group_sid' => 'sambaPrimaryGroupSID',
        'samba_account_flags' => 'sambaAcctFlags',
        'samba_home_drive' => 'sambaHomeDrive',
        'samba_home_path' => 'sambaHomePath',
        'samba_profile_paht' => 'sambaProfilePath',
        'samba_logon_script' => 'sambaLogonScript',
        'samba_last_login' => 'sambaLogonTime',
        'samba_last_logoff' => 'sambaLogoffTime',
        'samba_expiration' => 'sambaKickoffTime',
        'samba_password_last_change' => 'sambaPwdLastSet',
        'samba_password_must_change' => 'sambaPwdMustChange',
        'samba_password_cas_change' => 'sambaPwdCanChange',
      ),
    ),
  ),

  'before_modify' => 'valid',
  'after_modify' => 'valid',
  //'after_create' => 'createMaildirByFTP',
  //'after_delete' => 'removeMaildirByFTP',
  'display_name_format' => '%{cn}',
  'label' => 'Users',

  // LSrelation
  'LSrelation' => array(
    'groups' => array(
      'label' => 'Belongs to groups ...',
      'emptyText' => "Doesn't belong to any group.",
      'LSobject' => 'LSgroup',
      'list_function' => 'listUserGroups',
      'getkeyvalue_function' => 'getMemberKeyValue',
      'update_function' => 'updateUserGroups',
      'remove_function' => 'deleteOneMember',
      'rename_function' => 'renameOneMember',
      'canEdit_function' => 'canEditGroupRelation',
      'canEdit_attribute' => 'uniqueMember',
      'rights' => array(
        'self' => 'r',
        'admin' => 'w',
        'admingroup' => 'w',
      ),
    ),
    'dyngroups' => array(
      'label' => 'Belongs to dynamic groups ...',
      'emptyText' => "Doesn't belong to any dynamic group.",
      'LSobject' => "LSdyngroup",
      'linkAttribute' => "uniqueMember",
      'linkAttributeValue' => "dn",
      'rights' => array(
        'self' => 'r',
        'admin' => 'r',
      ),
    ),
    'godfather' => array(
      'label' => 'Godfather of ...',
      'emptyText' => "Doesn't sponsor any user.",
      'LSobject' => "LSpeople",
      'linkAttribute' => "lsGodfatherDn",
      'linkAttributeValue' => "dn",
      'rights' => array(
        'self' => 'r',
        'admin' => 'w',
        'admingroup' => 'w',
      ),
    ),
    'group_godfather' => array(
      'label' => 'Godfather of groups ...',
      'emptyText' => "Doesn't sponsor any group.",
      'LSobject' => "LSgroup",
      'linkAttribute' => "lsGodfatherDn",
      'linkAttributeValue' => "dn",
      'rights' => array(
        'self' => 'r',
        'admin' => 'w',
        'admingroup' => 'w',
      ),
    ),
    'dyngroup_godfather' => array(
      'label' => 'Godfather of dynamic groups ...',
      'emptyText' => "Doesn't sponsor any dynamic group.",
      'LSobject' => "LSdyngroup",
      'linkAttribute' => "lsGodfatherDn",
      'linkAttributeValue' => "dn",
      'rights' => array(
        'self' => 'r',
        'admin' => 'w',
        'admingroup' => 'w',
      ),
    ),
  ),

  // LSform
  'LSform' => array (
    'ajaxSubmit' => 1,
    // Layout
    'layout' => array (
      'Civilite' => array(
        'label' => 'Civility',
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
          'lsGodfatherDn',
        ),
      ),
      'Posix' => array (
        'label' => 'Posix',
        'args' => array (
          'uidNumber',
          'gidNumber',
          'loginShell',
          'homeDirectory',
          'gecos',
          'shadowExpire',
          'shadowMax',
          'shadowInactive',
          'shadowLastChange',
        ),
      ),
      'Samba' => array (
        'label' => 'Samba',
        'args' => array (
          'sambaSID',
          'sambaPrimaryGroupSID',
          'sambaAcctFlags',
          'sambaHomeDrive',
          'sambaHomePath',
          'sambaProfilePath',
          'sambaLogonScript',
          'sambaLogonTime',
          'sambaLogoffTime',
          'sambaKickoffTime',
          'sambaLMPassword',
          'sambaNTPassword',
          'sambaPwdLastSet',
          'sambaPwdMustChange',
          'sambaPwdCanChange',
        ),
      ),
      'ppolicy' => $GLOBALS['pwdPolicyAccountAttrs_LSform_layout'],
    ), // fin Layout
    'dataEntryForm' => array (
      'simple' => array (
        'label' => 'Simple',
        'disabledLayout' => true,
        'displayedElements' => array (
          'uid',
          'personalTitle',
          'givenName',
          'sn',
          'cn',
          'mail',
          'userPassword',
        ),
        'defaultValues' => array (
          'description' => 'Create with the simple data entry form',
          'loginShell' => 'no',
          'gidNumber' => '102001',
        ),
      ),
    ), // fin dataEntryForm
  ), // fin LSform

  'LSsearch' => array (
    'attrs' => array (
      'givenName',
      'sn',
      'cn',
      'uid',
      'mail',
    ),
    'params' => array (
      'recursive' => true,
      'nbObjectsByPage' => 25,
      'nbObjectsByPageChoices' => array(15, 25, 35),
    ),
    'predefinedFilters' => array (
      '(jpegPhoto=*)' => 'With photo',
      '(!(jpegPhoto=*))' => 'Without photo',
    ),
    'extraDisplayedColumns' => array (
      'mail' => array (
        'label' => 'Mail',
        'LSformat' => '%{mail}',
      ),
    ),
    'customActions' => array (
      'exportSearchResultAsCSV' => array (
        'label' => 'Export result as CSV',
        'icon' => 'export_csv',
        'function' => 'exportSearchResultAsCSV',
        'noConfirmation' => true,
        'disableOnSuccessMsg' => true,
        'rights' => array (
          'admin',
        ),
      ),
    ),
  ),

  'after_create' => 'triggerUpdateDynGroupsMembersCacheOnUserCreateOrDelete',
  'after_modify' => 'triggerUpdateDynGroupsMembersCacheOnUserModify',
  'after_delete' => 'triggerUpdateDynGroupsMembersCacheOnUserCreateOrDelete',

  // Attributes
  'attrs' => array_merge($GLOBALS['pwdPolicyAccountAttrs'], array (

    /* ----------- start -----------*/
    'uid' => array (
      'label' => 'Identifier',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'html_options' => array(
        'generate_value_format' => '%{givenName:1}.%{sn}',
        'autoGenerateOnModify' => true,   // default : false
        'withoutAccent' => 1,
        'replaceSpaces' => '.',
        'lowerCase' => 1,
      ),
      'required' => 1,
      'check_data' => array (
        'regex' => array(
          'msg' => "Identifier must contain alphanumeric values, dots (.) and dashes (-) only.",
          'params' => array('regex' => '/^[a-zA-Z0-9-_\.]*$/'),
        ),
      ),
      'validation' => array (
        array (
          'filter' => 'uid=%{val}',
          'result' => 0,
          'msg' => 'This identifier is already used.',
          'except_current_object' => true,
        ),
      ),
      'rights' => array(
        'self' => 'r',
        'admin' => 'w',
        'godfather' => 'r',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
      'dependAttrs' => array(
        'homeDirectory',
        'sambaHomePath',
        'sambaProfilePath',
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'givenName' => array (
      'label' => 'First Name',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'default_value' => 'toto',
      'check_data' => array (
          'alphanumeric' => array(
              'params' => array('withAccents' => true),
              'msg' => 'The first name must contain alphanumeric values only.',
          ),
      ),
      'rights' => array(
        'self' => 'r',
        'users' => 'r',
        'admin' => 'w',
        'godfather' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
      'onDisplay' => 'return_data',
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sn' => array (
      'label' => 'Last Name',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'rights' => array(
        'self' => 'r',
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
    'cn' => array (
      'label' => 'Full Name',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'html_options' => array(
        'generate_value_format' => '%{givenName} %{sn}',
        'autoGenerateOnModify' => true,   // default : false
        'autoGenerateOnCreate' => true    // default : false
      ),
      'required' => 1,
      'rights' => array(
        'self' => 'r',
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
      'dependAttrs' => array('gecos'),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'mail' => array (
      'label' => 'E-mail address',
      'ldap_type' => 'ascii',
      'html_type' => 'mail',
      'html_options' => array(
        'generate_value_format' => '%{givenName}.%{sn}@ls.com',
        'withoutAccent' => 1,
        'replaceSpaces' => '.',
        'lowerCase' => 1,
      ),
      'required' => 1,
      'check_data' => array (
        'email' => array(
          'msg' => "Given email address is invalid.",
          'params' => array('checkDomain' => false),
        ),
      ),
      'rights' => array(
        'self' => 'r',
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
    'personalTitle' => array (
      'label' => 'Civility',
      'ldap_type' => 'ascii',
      'html_type' => 'select_box',
      'html_options' => array (
        'possible_values' => array(
          'Mme' => 'Mrs',
          'M.' => 'Mr',
        ),
        'inline' => true,
        'sort' => false,
      ),
      'required' => 1,
      'default_value' => 'M.',
      'rights' => array(
        'self' => 'r',
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
    'description' => array (
      'label' => 'Description',
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'multiple' => 1,
      'rights' => array(
        'self' => 'r',
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
    'userPassword' => array (
      'label' => 'Password',
      'ldap_type' => 'password',
      'ldap_options' => array (
        'encode' => 'md5',
      ),
      'html_type' => 'password',
      'html_options' => array(
        'generationTool' => true,
        'viewHash' => true,
        'autoGenerate' => false,
        'confirmInput' => true,
        'lenght' => 8,
        'chars' => array (
          array(
            'nb' => 3,
            'chars' => 'abcdefijklmnopqrstuvwxyz',
          ),
          '0123456789',
          '*$.:/_-[]{}=~',
        ),
        'mail' => array(
          'send' => 1,
          'ask' => 1,
          'subject' => "LSexample : Your new credentials.",
          'msg' => "Your password has been changed.\nLogin : %{uid}\nNew password : %{password}",
          'mail_attr' => 'mail',
        ),
        'confirmChange' => True,
        'confirmChangeQuestion' => "Do you confirm change of this user's password?",
      ),
      'check_data' => array(
        'password' => array(
          'msg' => 'Your password must contain from 8 to 10 characters and contains at least one caracter that match with 3 of this types :<ul><li>Uppercase unaccent character</li><li>Lowercase unaccent character</li><li>Digit</li><li>Anything that is not a letter or a digit</li></ul>',
          'params' => array(
            'minLength' => 8,
            'maxLength' => 10,
            'regex' => array (
              '/[A-Z]/',
              '/[a-z]/',
              '/[0-9]/',
              '/[^A-Za-z0-9]/',
            ),
            'minValidRegex' => 3,
          ),
        ),
      ),
      'required' => 1,
      'rights' => array(
        'self' => 'w',
        'admin' => 'w',
      ),
      'dependAttrs' => array(
        'sambaLMPassword',
        'sambaNTPassword',
        'sambaPwdLastSet',
        'shadowLastChange',
      ),
      'form' => array (
        'modify' => 1,
        'create' => 1,
        'lostPassword' => 1,
      ),
      'after_modify' => 'valid',
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'lsRecoveryHash' => array (
      'label' => 'Password recovery hash',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 0,
      'form' => array (
        'lostPassword' => 1,
      ),
      'rights' => array(
        'self' => 'w',
        'admin' => 'w',
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'jpegPhoto' => array (
      'label' => 'Picture',
      'ldap_type' => 'image',
      'html_type' => 'image',
      'required' => 0,
      'view' => 1,
      'check_data' => array (
        'imagesize' => array(
          'msg' => "Picture size is not valid.",
          'params' => array(
            'maxWidth' => 2000,
          ),
        ),
        'filesize' => array(
          'msg' => "File size is not valid.",
          'params' => array(
            'maxSize' => 3000000,   // taille du fichier en octets
          ),
        ),
        'imagefile' => array(
          'msg' => "File type is not valid.",
        ),
      ),
      'form' => array (
        'modify' => 1,
      ),
      'rights' => array(
        'self' => 'w',
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'w',
      ),
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
            'value_attribute' => 'dn',
        ),
      ),
      'validation' => array (
        array (
          'basedn' => '%{val}',
          'result' => 1,
          'msg' => "One or several users don't exist.",
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

    /************************************************
     *              POSIX Attributes
     ************************************************/

     /* ----------- start -----------*/
     'uidNumber' => array (
       'label' => 'Numeric identifier',
       'ldap_type' => 'numeric',
       'html_type' => 'text',
       'required' => 1,
       'generate_function' => 'generate_samba_uidNumber',
       'check_data' => array (
         'numeric' => array(
           'msg' => "The numeric identifier must be an integer.",
         ),
       ),
       'validation' => array (
         array (
           'filter' => 'uidNumber=%{val}',
           'result' => 0,
           'msg' => 'This uid is already used.',
         ),
       ),
       'rights' => array(
         'admin' => 'w',
       ),
       'view' => 1,
       'form' => array (
         'modify' => 0,
       ),
     ),
     /* ----------- end -----------*/

     /* ----------- start -----------*/
     'gidNumber' => array (
       'label' => 'Main group',
       'ldap_type' => 'numeric',
       'html_type' => 'select_list',
       'html_options' => array (
         'possible_values' => array(
           '0' => 'No group',
           array (
             'label' => 'LDAP Groups',
             'possible_values' => array (
               'OTHER_OBJECT' => array (
               'object_type' => 'LSgroup',                      // Nom de l'objet ?? lister
               'display_name_format' => '%{cn} (%{gidNumber})',   // Sp??cifie le attributs ?? lister pour le choix,
                                                                  // si non d??finie => utilisation du 'display_name_format'
                                                                  // de la d??finition de l'objet

               'value_attribute' => 'gidNumber',   // Sp??cifie le attributs dont la valeur sera retourn??e par
               /*'filter' =>                         // le formulaire sp??cifie les filtres de recherche pour
                 array (                           // l'??tablissement de la liste d'objets :
                   array(                          // Premier filtre
                     'filter' => 'cn=*a*',
                     //'basedn' => 'o=company',
                     'scope' => 'sub',
                   ),
                 )*/
               ),
             ),
           ),
         ),
       ),
       'multiple' => false,
       'required' => 1,
       'validation' => array (
         array (
           'msg' => "This group doesn't exist.",
           'object_type' => 'LSgroup',           // 'object_type' : Permet definir le type d'objet recherch??s
           //'basedn' => 'o=company',                    // et d'utiliser les objectClass d??finis dans le fichier de configuration
           'filter' => '(gidNumber=%{val})',       // pour la recherche
           'result' => 1,
         ),
       ),
       'rights' => array(
         'admin' => 'w',
         'godfather' => 'r',
       ),
       'view' => 1,
       'form' => array (
         'modify' => 1,
         'create' => 1,
       ),
       'dependAttrs' => array(
         'sambaPrimaryGroupSID',
       ),
     ),
     /* ----------- end -----------*/

     /* ----------- start -----------*/
     'loginShell' => array (
       'label' => 'Command shell',
       'help_info' => "Allow user to connect a POSIX system.",
       'ldap_type' => 'boolean',
       'ldap_options' => array (
         'true_value' => '/bin/bash',
         'false_value' => '/bin/false',
       ),
       'html_type' => 'boolean',
       'required' => 1,
       'default_value' => 'no',
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

     /* ----------- start -----------*/
     'homeDirectory' => array (
       'label' => 'Home Directory',
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
     'gecos' => array (
       'label' => 'Gecos',
       'ldap_type' => 'ascii',
       'html_type' => 'text',
       'html_options' => array(
         'generate_value_format' => '%{cn~}',
         'autoGenerateOnModify' => true,   // default : false
         'autoGenerateOnCreate' => true,   // default : false
       ),
       'required' => 1,
       'generate_value_format' => '%{cn~}',
       'rights' => array(
         'admin' => 'w',
       ),
       'view' => 1,
       'form' => array(
         'modify' => 1,
       ),
     ),
     /* ----------- end -----------*/

     /* ----------- start -----------*/
     'shadowExpire' => array (
       'label' => 'Password expiration',
       'ldap_type' => 'shadowExpire',
       'html_type' => 'date',
       'html_options' => array(
         'time' => false,
         'showNowButton' => False,
         'special_values' => array(
           '0' => 'Always (disable account)',
         ),
       ),
       'required' => 0,
       'generate_function' => 'generate_shadowExpire_from_sambaPwdMustChange',
       'rights' => array(
         'admin' => 'w',
       ),
       'view' => 1,
       'form' => array(
         'modify' => 1,
       ),
     ),
     /* ----------- end -----------*/

     /* ----------- start -----------*/
     'shadowMax' => array (
       'label' => 'Password validity (in days)',
       'help_info' => 'The maximum number of days the password is valid.',
       'ldap_type' => 'numeric',
       'html_type' => 'text',
       'check_data' => array (
         'integer' => array(
           'params' => array(
             'positive' => true,
           ),
           'msg' => "The password validity must be an positive integer.",
         ),
       ),
       'required' => 0,
       'rights' => array(
         'admin' => 'w',
       ),
       'view' => 1,
       'form' => array(
         'create' => 1,
         'modify' => 1,
       ),
     ),
     /* ----------- end -----------*/

     /* ----------- start -----------*/
     'shadowInactive' => array (
       'label' => 'Inactivity allowed (in days)',
       'help_info' => 'The number of days of inactivity allowed for the specified user.',
       'ldap_type' => 'numeric',
       'html_type' => 'text',
       'check_data' => array (
         'integer' => array(
           'params' => array(
             'positive' => true,
           ),
           'msg' => "The inactivity allowed must be an positive integer.",
         ),
       ),
       'required' => 0,
       'rights' => array(
         'admin' => 'w',
       ),
       'view' => 1,
       'form' => array(
         'create' => 1,
         'modify' => 1,
       ),
     ),
     /* ----------- end -----------*/

     /* ----------- start -----------*/
     'shadowLastChange' => array (
       'label' => 'Password last change time',
       'ldap_type' => 'shadowExpire',
       'html_type' => 'date',
       'html_options' => array(
         'time' => False,
         'showNowButton' => False,
       ),
       'generate_function' => 'generate_shadowLastChange',
       'no_value_label' => 'Never',
       'rights' => array(
         'admin' => 'w',
       ),
       'view' => 1,
     ),
     /* ----------- end -----------*/

    /************************************************
     *              Samba Attributes
     ************************************************/

    /* ----------- start -----------*/
    'sambaSID' => array (
      'label' => 'Samba Identifier',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_user_sambaSID',
      'rights' => array(
        'admin' => 'r',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 0,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaPrimaryGroupSID' => array (
      'label' => 'Samba primary group identifier',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaPrimaryGroupSID',
      'rights' => array(
        'admin' => 'r',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 0,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaAcctFlags' => array (
      'label' => 'Samba account flags',
      'ldap_type' => 'sambaAcctFlags',
      'html_type' => 'sambaAcctFlags',
      'required' => 1,
      'default_value' => array('U'),
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'create' => 1,
        'modify' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaHomeDrive' => array (
      'label' => 'Samba network drive of the home directory',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'default_value' => 'Z:',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaHomePath' => array (
      'label' => 'Samba network path of the home directory',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaHomePath',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 0,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaProfilePath' => array (
      'label' => 'Samba network path of the profile',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaProfilePath',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 0,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaLogonScript' => array (
      'label' => 'Samba logon script',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'default_value' => 'logon.bat',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 0,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaLogonTime' => array (
      'label' => 'Samba last logon time',
      'ldap_type' => 'date',
      'ldap_options' => array(
        'timestamp' => True,
      ),
      'html_type' => 'date',
      'html_options' => array(
        'time' => True,
        'showTodayButton' => False,
      ),
      'no_value_label' => 'Never',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaLogoffTime' => array (
      'label' => 'Samba last logoff time',
      'ldap_type' => 'date',
      'ldap_options' => array(
        'timestamp' => True,
      ),
      'html_type' => 'date',
      'html_options' => array(
        'time' => True,
        'showTodayButton' => False,
      ),
      'no_value_label' => 'Never',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaKickoffTime' => array (
      'label' => 'Samba expiration time',
      'help_info' => 'Specifies the time when the user will be locked down and cannot login any longer.',
      'ldap_type' => 'date',
      'ldap_options' => array(
        'timestamp' => True,
      ),
      'html_type' => 'date',
      'html_options' => array(
        'time' => True,
        'showTodayButton' => False,
        'special_values' => array(
          LS_SAMBA_INFINITY_TIME => 'Never',
        ),
      ),
      'no_value_label' => 'Default (never)',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaLMPassword' => array (
      'label' => 'Samba Password (LM)',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaLMPassword',
      'form' => array (
        'modify' => 0,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaNTPassword' => array (
      'label' => 'Samba Password (NT)',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_sambaNTPassword',
      'form' => array (
        'modify' => 0,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaPwdLastSet' => array (
      'label' => 'Samba password last change time',
      'ldap_type' => 'date',
      'ldap_options' => array(
        'timestamp' => True,
      ),
      'html_type' => 'date',
      'html_options' => array(
        'time' => True,
        'showTodayButton' => False,
      ),
      'generate_function' => 'generate_sambaPwdLastSet',
      'no_value_label' => 'Never',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 0,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaPwdMustChange' => array (
      'label' => 'Samba password must change',
      'ldap_type' => 'date',
      'ldap_options' => array(
        'timestamp' => True,
      ),
      'html_type' => 'date',
      'html_options' => array(
        'time' => True,
        'showTodayButton' => False,
        'special_values' => array(
          '0' => 'At first login',
          LS_SAMBA_INFINITY_TIME => 'Never',
        ),
      ),
      'no_value_label' => 'Default (never)',
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'sambaPwdCanChange' => array (
      'label' => 'Samba password can change',
      'help_info' => 'If not set, the user will be free to change his password whenever he wants.',
      'ldap_type' => 'date',
      'ldap_options' => array(
        'timestamp' => True,
      ),
      'html_type' => 'date',
      'html_options' => array(
        'time' => True,
        'showTodayButton' => False,
        'special_values' => array(
          LS_SAMBA_INFINITY_TIME => 'Never',
          0 => 'Whenever',
        ),
      ),
      'no_value_label' => 'Default (whenever)',
      'multiple' => false,
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
      ),
    ),
    /* ----------- end -----------*/

  )), // Fin args & array_merge()
);
