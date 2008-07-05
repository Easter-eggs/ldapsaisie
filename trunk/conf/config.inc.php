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

// Configuration LDAP Saisie :
$GLOBALS['LSconfig'] = array(
  'NetLDAP2' => '/usr/share/php/Net/LDAP2.php',
  'Smarty' => '/var/www/tmp/Smarty-2.6.18/libs/Smarty.class.php',
  'lang' => 'fr_FR.UTF8',
  'cacheLSrights' => true,
  'cacheSubDn' => true,
  'cacheSearch' => true,
  'ldap_servers' => array (
    array (
      'name' => 'LSexample',
      'ldap_config'=> array(
            'host'     => '127.0.0.1',
            'port'     => 389,
            'version'  => 3,
            'starttls' => false,
        'binddn'   => 'uid=ldapsaisie,ou=sysaccounts,o=ls',
        'bindpw'   => 'toto',
        'basedn'   => 'o=ls',
        'options'  => array(),
        'filter'   => '(objectClass=*)',
        'scope'    => 'sub'
        ),
        'LSadmins' => array (
          'o=ls' => array (
            'uid=eeggs,ou=people,o=ls' => NULL
          ),
          'ou=people,o=ls' => array (
            'cn=adminldap,ou=groups,o=ls' => array (
              'attr' => 'uniqueMember',
              'LSobject' => 'LSeegroup'
            )
          )
        ),
        'cacheLSrights' => true,
        'cacheSearch' => true,
      'authobject' => 'LSeepeople',
      'authobject_pwdattr' => 'userPassword',
      'LSaccess' => array(
        'LSeepeople',
        'LSeegroup',
        'LSeecompany'
      ),
      'recoverPassword' => array(
        'mailAttr' => 'mail',
        'recoveryHashAttr' => 'lsRecoveryHash',
        'recoveryEmailSender' => 'noreply-recover@lsexample.net',
        'recoveryHashMail' => array(
          'subject' => 'LSexample : Récupération de votre mot de passe.',
          'msg' => "Pour poursuivre le processus de récupération de votre mot de passe,\nmerci de cliquer de vous rendre à l'adresse suivante :\n%{url}"
        ),
        'newPasswordMail' => array(
          'subject' => 'LSexample : Votre nouveau mot de passe.',
          'msg' => "Votre nouveau mot de passe : %{mdp}"
        )
      ),
      'emailSender' => 'noreply@lsexample.net'
    )
  )
);

//Debug
$GLOBALS['LSdebug']['active'] = true;

define('NB_LSOBJECT_LIST',20);
define('NB_LSOBJECT_LIST_SELECT',11);

define('MAX_SEND_FILE_SIZE',2000000);

// Définitions des locales
$textdomain = 'ldapsaisie';
bindtextdomain($textdomain, '/var/www/ldapsaisie/trunk/l10n');
textdomain($textdomain);
setlocale(LC_ALL, $GLOBALS['LSconfig']['lang']);

// Définitions des dossiers d'inclusions
define('LS_CONF_DIR','conf/');
define('LS_OBJECTS_DIR', LS_CONF_DIR . 'LSobjects/');
define('LS_INCLUDE_DIR','includes/');
define('LS_CLASS_DIR', LS_INCLUDE_DIR .'class/');
define('LS_LIB_DIR', LS_INCLUDE_DIR .'libs/');
define('LS_ADDONS_DIR', LS_INCLUDE_DIR .'addons/');
define('LS_JS_DIR', LS_INCLUDE_DIR .'js/');
define('LS_TMP_DIR', 'tmp/');

// Javascript
$GLOBALS['defaultJSscipts']=array(
  'mootools-core.js',
  'mootools-more.js',
  'LSdefault.js'
);

// PHP values
ini_set( 'magic_quotes_gpc', 'off' );
ini_set( 'magic_quotes_sybase', 'off' );
ini_set( 'magic_quotes_runtime', 'off' );

?>
