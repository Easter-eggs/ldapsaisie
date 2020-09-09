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
 * SAMBA support configuration                    *
 **************************************************
 */

// SID of the samba managed domain
define('LS_SAMBA_DOMAIN_SID', 'S-1-5-21-2421470416-3566881284-3047381809');

// Name of the samba managed domain (sambaDomainName)
define('LS_SAMBA_DOMAIN_NAME', 'LS');

// DN of the sambaDomain object of the domain
define('LS_SAMBA_DOMAIN_OBJECT_DN', 'sambaDomainName=LS,o=ls');

// DN of the sambaUnixIdPool object (optional, default: LS_SAMBA_DOMAIN_OBJECT_DN)
//define('LS_SAMBA_UNIX_ID_POOL_DN', null);

// Base number to calculate user sambaSID
define('LS_SAMBA_SID_BASE_USER', 1000);

// Base number to calculate group sambaSID
define('LS_SAMBA_SID_BASE_GROUP', 1001);

/**
 * NB : This two numbers must be for one even and for the other odd to maintain the
 * uniqueness of the SIDs.
 **/

// LDAP attribute uidNumber name
define('LS_SAMBA_UIDNUMBER_ATTR','uidNumber');

// LDAP attribute gidNumber name
define('LS_SAMBA_GIDNUMBER_ATTR','gidNumber');

// LDAP attribute userPassword name
define('LS_SAMBA_USERPASSWORD_ATTR','userPassword');

// Format of the users's home directory value
define('LS_SAMBA_HOME_PATH_FORMAT','\\SERVER\%{uid}');

// Format of the users's samba profile path value
define('LS_SAMBA_PROFILE_PATH_FORMAT','\\SERVER\profiles\%{uid}');
