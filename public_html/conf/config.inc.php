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
  'Smarty' => '/usr/share/php/smarty3/Smarty.class.php',
  'ConsoleTable' => '/usr/share/php/Console/Table.php',
  'lang' => 'fr_FR',
  'encoding' => 'UTF8',
  'cacheLSprofiles' => true,
  'cacheSubDn' => true,
  'cacheSearch' => true,
  'globalSearch' => true,
  'keepLSsessionActive' => true,
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
/*
      'LSauth' => array (
        'method' => 'HTTP'
      ),
*/
      'LSprofiles' => array (
        'admin' => array (
          'label' => 'Administrator',
          'o=ls' => array (
            'uid=eeggs,ou=people,o=ls' => NULL
          ),
          'ou=people,o=ls' => array (
            'cn=adminldap,ou=groups,o=ls' => array (
              'attr' => 'uniqueMember',
              'LSobject' => 'LSgroup'
            )
          )
        ),
        'godfather' => array (
          'label' => 'Godfather',
          'LSobjects' => array (
            'LSpeople' => array (
              'attr' => 'lsGodfatherDn',
              'attr_value' => '%{dn}',
              'basedn' => 'ou=people,o=ls'
            ),
            'LSgroup' => array (
              'filter' => '(lsGodfatherDn=%{dn})'
            )
          )
        )
      ),
      'cacheLSprofiles' => true,
      'cacheSearch' => true,
      'authObjectType' => 'LSpeople',
      'authObjectFilter' => '(|(uid=%{user})(mail=%{user}))',
      'authObjectTypeAttrPwd' => 'userPassword',
      'LSaccess' => array(
        'LSpeople',
        'LSgroup'
      ),
      'recoverPassword' => array(
        'mailAttr' => 'mail',
        'recoveryHashAttr' => 'lsRecoveryHash',
        'recoveryEmailSender' => 'noreply-recover@ls.com',
        'recoveryHashMail' => array(
          'subject' => 'LSexample : Recovering your password.',
          'msg' => "To proceed password recovery procedure, please follow that link:\n%{url}"
        ),
        'newPasswordMail' => array(
          'subject' => 'LSexample : Your new credentials.',
          'msg' => "Your new password : %{mdp}"
        )
      ),
      'emailSender' => 'noreply@ls.com'
    ),
    array (
      'name' => 'LSexample - multi-company',
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
      'LSprofiles' => array(
        'admin' => array (
          'o=ls' => array (
            'uid=eeggs,ou=people,o=ls' => NULL,
            'cn=adminldap,ou=groups,o=ls' => array (
              'attr' => 'uniqueMember',
              'LSobject' => 'LSgroup'
            )
          )
        ),
        'admingroup' => array (
          'ou=company1,ou=companies,o=ls' => array (
            'uid=user1,ou=people,ou=company1,ou=companies,o=ls' => NULL
          )
        )
      ),
      'authObjectType' => 'LSpeople',
      'subDnLabel' => 'Company',
      'subDn' => array(
        '== All ==' => array(
          'dn' => 'o=ls',
          'LSobjects' => array(
            'LSpeople',
            'LSgroup',
            'LScompany'
          )
        ),
        'LSobject' => array(
          'LScompany' => array(
            'LSobjects' => array(
              'LSpeople',
              'LSgroup'
            )
          )
        )
      ),
      'cacheLSprofiles' => true,
      'cacheSearch' => true,
      'globalSearch' => true,
      'authObjectTypeAttrPwd' => 'userPassword',
      'recoverPassword' => array(
        'mailAttr' => 'mail',
        'recoveryHashAttr' => 'lsRecoveryHash',
        'recoveryEmailSender' => 'noreply-recover@lsexample.net',
        'recoveryHashMail' => array(
          'subject' => 'LSexample : Recovering your password.',
          'msg' => "To proceed password recovery procedure, please follow that link:\n%{url}"
        ),
        'newPasswordMail' => array(
          'subject' => 'LSexample : Your new credentials.',
          'msg' => "Your new password : %{mdp}"
        )
      ),
      'emailSender' => 'noreply@lsexample.net'
    )
  )
);

// Interface
// Theme Black
//define('LS_THEME','black');
//define('LS_TEMPLATES_DIR', 'templates/default');

// Theme Default
define('LS_THEME','default');
define('LS_TEMPLATES_DIR', 'templates');
define('LS_IMAGES_DIR', 'images');
define('LS_CSS_DIR', 'css');

//Debug
define('LSdebug',false);

// Logging
$GLOBALS['LSlog']['handlers'] = array (
	array (
		'handler' => 'file',
		'path' => 'tmp/LS.log',
	),
	array (
		'handler' => 'email', // Email handler (each logged message generated an email)
		'level' => 'FATAL',
		'recipient' => 'root@localhost', // Email recipient
	),
	/*
	array (
		'handler' => 'syslog', // Syslog handler
		//'priority' => 'WARNING', // Force priority : EMERG, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG
	),
	*/
	/*
	array (
		'handler' => 'system', // System logging (using PHP error_log)
		'level' => 'ERROR',
	),
	*/
);
$GLOBALS['LSlog']['level'] = 'INFO';	// DEBUG, INFO, WARNING, ERROR, FATAL
$GLOBALS['LSlog']['enable'] = true;

define('NB_LSOBJECT_LIST',30);
define('NB_LSOBJECT_LIST_SELECT',11);
$GLOBALS['NB_LSOBJECT_LIST_CHOICES'] = array(30, 60, 100);

define('MAX_SEND_FILE_SIZE',2000000);


// Javascript
$GLOBALS['defaultJSscipts']=array(
  'mootools-core.js',
  'mootools-more.js',
  'functions.js',
  'LSdefault.js',
  'LSinfosBox.js'
);

// CSS
$GLOBALS['defaultCSSfiles']=array('../light-blue.css');
