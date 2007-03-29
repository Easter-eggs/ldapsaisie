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
  'NetLDAP' => '/usr/share/php/Net/LDAP.php',
  'QuickForm' => '/usr/share/php/QuickForm.php',
  'check_data_place' => 'server',
  'ldap_config'=> array(
    'host'     => 'localhost',
    'port'     => 389,
    'version'  => 3,
    'starttls' => false,
    'binddn'   => 'uid=eeggs,ou=people,o=com',
    'bindpw'   => 'toto',
    'basedn'   => 'o=ost',
    'options'  => array(),
    'filter'   => '(objectClass=*)',
    'scope'    => 'sub'
  )
);

?>
