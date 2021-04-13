<?php
/*******************************************************************************
 * Copyright (C) 2019 Easter-eggs
 * http://ldapsaisie.org
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
 ***************************************
 * Configuration for mailquota support *
 ***************************************
 */

// IMAP Mailbox connection string LSformat (composed with LSldapObject attributes)
// See : https://php.net/imap_open (parameter $mailbox)
define('MAILQUOTA_IMAP_MAILBOX','{localhost}');

// IMAP Master user
define('MAILQUOTA_IMAP_MASTER_USER', 'ldapsaisie');

// IMAP Master user's password
define('MAILQUOTA_IMAP_MASTER_USER_PWD', 'secret');

// IMAP Master user LSformat composed with :
//  * masteruser = master username (MAILQUOTA_IMAP_MASTER_USER)
//  * LSldapObject attributes
define('MAILQUOTA_IMAP_MASTER_USER_FORMAT', '%{mail}*%{masteruser}');

// IMAP quota root mailbox
define('MAILQUOTA_IMAP_QUOTA_ROOT_MAILBOX', 'INBOX');
