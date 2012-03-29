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
 *****************************************************
 * Configuration of the CAS authentification support *
 *****************************************************
 */

// phpCAS Path (http://www.ja-sig.org/wiki/display/CASC/phpCAS)
define('PHP_CAS_PATH','/usr/share/php/CAS.php');

// phpCAS Debug File
// define('PHP_CAS_DEBUG_FILE','/tmp/phpCAS.log');

// Disable logout
define('LSAUTH_CAS_DISABLE_LOGOUT',false);

// CAS Server version (used constant name know by phpCAS : CAS_VERSION_1_0 or CAS_VERSION_2_0)
define('LSAUTH_CAS_VERSION','CAS_VERSION_2_0');

// CAS Server hostname
define('LSAUTH_CAS_SERVER_HOSTNAME','cas.univ.fr');

// CAS Server port
define('LSAUTH_CAS_SERVER_PORT',443);

// CAS Server URI (empty by default)
// define('LSAUTH_CAS_SERVER_URI','cas/');

// No SSL validation for the CAS server
define('LSAUTH_CAS_SERVER_NO_SSL_VALIDATION',false);

// CAS server SSL Certificate path
//define('LSAUTH_CAS_SERVER_SSL_CERT','');

// CAS server SSL CA Certificate path
//define('LSAUTH_CAS_SERVER_SSL_CACERT','');

// phpCAS use cURL to validate ticket from the CAS server.
// You could have to set SSLVERSION manualy if you have cURL
// error on ticket validation. Possibles values : 2 or 3
//define('LSAUTH_CAS_CURL_SSLVERION',3);

?>
