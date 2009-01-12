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

/*
 **************************************************
 * Données de configuration pour le support POSIX *
 **************************************************
 */

// Nom de l'attribut LDAP uid
define('LS_POSIX_UID_ATTR','uid');

// Nom de l'attribut LDAP uidNumber
define('LS_POSIX_UIDNUMBER_ATTR','uidNumber');

// Valeur minimum d'un uidNumber
define('LS_POSIX_UIDNUMBER_MIN_VAL','100000');

// Nom de l'attribut LDAP gidNumber
define('LS_POSIX_GIDNUMBER_ATTR','gidNumber');

// Valeur minimum d'un gidNumber
define('LS_POSIX_GIDNUMBER_MIN_VAL','100000');

// Dossier contenant les homes des utilisateurs (defaut: /home/)
define('LS_POSIX_HOMEDIRECTORY','/home/');

// Create homeDirectory by FTP - Host
define('LS_POSIX_HOMEDIRECTORY_FTP_HOST','127.0.0.1');

// Create homeDirectory by FTP - Port
define('LS_POSIX_HOMEDIRECTORY_FTP_PORT',21);

// Create homeDirectory by FTP - User
define('LS_POSIX_HOMEDIRECTORY_FTP_USER','admin');

// Create homeDirectory by FTP - Password
define('LS_POSIX_HOMEDIRECTORY_FTP_PWD','password');

// Create homeDirectory by FTP - Path
define('LS_POSIX_HOMEDIRECTORY_FTP_PATH','%{homeDirectory}');

?>
