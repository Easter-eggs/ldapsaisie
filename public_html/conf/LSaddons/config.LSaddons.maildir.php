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
 ****************************************************
 * Données de configuration pour le support Maildir *
 ****************************************************
 */
  
// Serveur FTP - Host
define('LS_MAILDIR_FTP_HOST','127.0.0.1');

// Serveur FTP - Port
define('LS_MAILDIR_FTP_PORT',21);

// Serveur FTP - User
define('LS_MAILDIR_FTP_USER','vmail');

// Serveur FTP - Passorwd
define('LS_MAILDIR_FTP_PWD','password'); 

// Serveur FTP - Maildir Path
define('LS_MAILDIR_FTP_MAILDIR_PATH','%{mailbox}');

// Serveur FTP - Maildir Path Regex
define('LS_MAILDIR_FTP_MAILDIR_PATH_REGEX','^\/home\/vmail\/([^\/]+)\/$');

// Serveur FTP - Maildir CHMOD
define('LS_MAILDIR_FTP_MAILDIR_CHMOD','700');

?>
