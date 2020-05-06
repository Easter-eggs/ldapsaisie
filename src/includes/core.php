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

// PHP values
ini_set( 'magic_quotes_gpc', 'off' );
ini_set( 'magic_quotes_sybase', 'off' );
ini_set( 'magic_quotes_runtime', 'off' );
if ((isset($_REQUEST) && isset($_REQUEST['LSdebug'])) || (isset($_SERVER['HTTP_HOST']) && preg_match('/^127\.[0-9]+\.[0-9]+\.[0-9]+$/', $_SERVER['HTTP_HOST'])))
	error_reporting(E_ALL);
else
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

// Root directory path
if (__FILE__ != "") {
	$script = __FILE__;
}
else {
	foreach(get_included_files() as $script)
		if (basename($script) == 'core.php')
			break;
}
define('LS_ROOT_DIR', realpath(dirname($script).'/../'));

// Définitions des dossiers d'inclusions
define('LS_CONF_DIR', 'conf/');
define('LS_OBJECTS_DIR', LS_CONF_DIR . 'LSobjects/');
define('LS_INCLUDE_DIR', 'includes/');
define('LS_CLASS_DIR', LS_INCLUDE_DIR .'class/');
define('LS_LIB_DIR', LS_INCLUDE_DIR .'libs/');
define('LS_ADDONS_DIR', LS_INCLUDE_DIR .'addons/');
define('LS_JS_DIR', LS_INCLUDE_DIR .'js/');
define('LS_TMP_DIR', 'tmp/');
define('LS_TMP_DIR_PATH', LS_ROOT_DIR . '/' . LS_TMP_DIR);
define('LS_LOCAL_DIR', 'local/');

// Locale
define('LS_TEXT_DOMAIN', 'ldapsaisie');
define('LS_I18N_DIR', 'lang');
define('LS_I18N_DIR_PATH', LS_ROOT_DIR.'/lang');

require_once LS_ROOT_DIR.'/'.LS_INCLUDE_DIR.'functions.php';

require_once LS_ROOT_DIR.'/'.LS_CLASS_DIR.'class.LSsession.php';
