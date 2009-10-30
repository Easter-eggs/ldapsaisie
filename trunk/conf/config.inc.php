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
  'Smarty' => '/usr/share/php/smarty/libs/Smarty.class.php',
  'lang' => 'fr_FR',
  'encoding' => 'UTF8',
  'cacheLSprofiles' => true,
  'cacheSubDn' => true,
  'cacheSearch' => true,
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
      'LSprofiles' => array (
        'admin' => array (
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
define('LS_TEMPLATES_DIR', 'templates/'.LS_THEME);
define('LS_IMAGES_DIR', 'images/'.LS_THEME);
define('LS_CSS_DIR', 'css/'.LS_THEME);

//Debug
define('LSdebug',true);

// Logs
$GLOBALS['LSlog']['filename'] = 'tmp/LS.log';
$GLOBALS['LSlog']['enable'] = true;

define('NB_LSOBJECT_LIST',20);
define('NB_LSOBJECT_LIST_SELECT',11);

define('MAX_SEND_FILE_SIZE',2000000);


// Javascript
$GLOBALS['defaultJSscipts']=array(
  'mootools-core.js',
  'mootools-more.js',
  'functions.js',
  'LSdefault.js',
  'LSinfosBox.js'
);

?>
