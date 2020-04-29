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

/**
 * Handle logging
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog {

	// Enable state
	private static $enabled = false;

	// Configured handlers
	private static $handlers = array();

	// Default handlers (if not configured)
	private static $default_handlers = array(
		array (
			'handler' => 'file',
		),
	);

	// Current level
	private static $level;
	private static $default_level = 'WARNING';

	// Levels
	private static $levels=array(
		'DEBUG' => 0,
		'INFO' => 1,
		'WARNING' => 2,
		'ERROR' => 3,
		'FATAL' => 4,
	);

	/**
	 * Start/initialize logging
	 *
	 * @retval bool True on success, False otherwise
	 **/
	public static function start() {
		// Load configuration
		self :: $enabled = self :: getConfig('enable', false, 'bool');
		self :: setLevel();

		// Load default handlers class
		if (!LSsession :: loadLSclass('LSlog_handler', null, true)) {
			LSdebug('LSlog disabled');
			return False;
		}

		// Load handlers
		$handlers = self :: getConfig('handlers');
		if (!is_array($handlers)) $handlers = self :: $default_handlers;
		LSdebug($handlers, true);
		$debug_handlers = array();
		foreach($handlers as $handler => $handler_config) {
			if (!is_array($handler_config))
				$handler_config = array('handler' => $handler);
			else
				$handler = (isset($handler_config['handler'])?$handler_config['handler']:'system');

			if (!self :: add_handler($handler, $handler_config))
				continue;

			$debug_handlers[] = $handler;
		}
		LSdebug('LSlog enabled with level='.self :: $level.' and following handlers : '.implode(', ', $debug_handlers));

		set_exception_handler(array('LSlog', 'exception'));
		return True;
	}

	/**
	 * Add handler
	 *
	 * @param[in] $handler string					The handler name
	 * @param[in] $handler_config array 	The handler configuration (optional)
	 *
	 * @retval boolean True if handler added, false otherwise
	 **/
	public static function add_handler($handler, $handler_config = array()) {
		$handler_class = "LSlog_$handler";

		// Load handler class
		if (!LSsession :: loadLSclass($handler_class) || !class_exists($handler_class)) {
			LSerror :: addErrorCode('LSlog_01', $handler);
			return false;
		}

		$handler_obj = new $handler_class($handler_config);
		if ($handler_obj -> checkCompatibility()) {
			self :: $handlers[] = $handler_obj;
			return True;
		}
		LSdebug("LSlog handler $handler not supported.");
		return false;
	}

	/**
	 * Enable console handler (if not already enabled)
	 *
	 * @retval boolean True if log on console enabled, false otherwise
	 **/
	public static function logOnConsole() {
		for ($i=0; $i < count(self :: $handlers); $i++)
			if (is_a(self :: $handlers[$i], 'LSlog_console'))
				return true;
		return self :: add_handler('console');
	}


	/**
	 * Set log level
	 *
	 * @param[in] $level string|null The log level (optinal, default: from configuration or 'WARNING')
	 *
	 * @retval boolean True if log level set, false otherwise
	 **/
	public static function setLevel($level=null) {
		if (!$level) {
			$level = self :: getConfig('level', self :: $default_level, 'string');
			if (!array_key_exists(self :: $level, self :: $levels)) {
				self :: $level = 'WARNING';
				if ($level)
					self :: warning("Invalid log level '$level' configured. Set log level to WARNING.");
				$level = 'WARNING';
			}
		}
		else if (!array_key_exists($level, self :: $levels))
			return false;
		self :: $level = $level;

		// Set PHP error/exception handlers
		if (self :: $level == 'DEBUG')
			set_error_handler(array('LSlog', 'php_error'), E_ALL & ~E_STRICT);
		else
			set_error_handler(array('LSlog', 'php_error'), E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		return True;
	}

	/**
	 * Get a configuration variable value
	 *
	 * @param[in] $var string The configuration variable name
	 * @param[in] $default mixed The default value to return if configuration variable
	 *			   is not set (Default : null)
	 * @param[in] $cast string   The type of expected value. The configuration variable
	 *			   value will be cast as this type. Could be : bool, int,
	 *			   float or string. (Optional, default : raw value)
	 *
	 * @retval mixed The configuration variable value
	 **/
	public static function getConfig($var, $default=null, $cast=null) {
		return LSconfig :: get($var, $default, $cast, ((isset($GLOBALS['LSlog']) && is_array($GLOBALS['LSlog']))?$GLOBALS['LSlog']:array()));
	}

	/**
	 * Log a message
	 *
	 * @param[in] $level string The message level
	 * @param[in] $message string The message
	 *
	 * @retval void
	 **/
	private static function logging($level, $message) {
		// Check LSlog is enabled
		if (!self :: $enabled)
			return;

		// Check/fix level
		if (!array_key_exists($level, self :: $levels))
			$level = self :: $default_level;

		// Handle non-string message
		if (!is_string($message)) {
			if (is_object($message) && method_exists($message, '__toString'))
				$message = strval($message);
			else
				$message = varDump($message);
		}

		// Add prefix
		if (php_sapi_name() == "cli") {
			global $argv;
			$message = basename($argv[0])." - $level - $message";
		}
		else {
			$message = $_SERVER['REQUEST_URI'].' - '.$_SERVER['REMOTE_ADDR'].' - '.self :: getLdapServerName().' - '.self :: getAuthenticatedUserDN()." - $level - $message";
		}

		foreach (self :: $handlers as $handler) {
			// Check handler level
			if (!$handler -> checkLevel($level))
				continue;

			// Logging on this handler
			call_user_func(array($handler, 'logging'), $level, $message);
		}

		if ($level == 'FATAL') {
			if (php_sapi_name() == "cli")
				die($message);
			elseif (class_exists('LStemplate'))
				LStemplate :: fatal_error($message);
			else
				die($message);
		}
	}

	/**
	 * Check level against configured level
	 *
	 * @param[in] $level string The level
	 * @param[in] $configured_level string|null The configured level (optional, default : self :: $level)
	 *
	 * @retval bool True if a message with this level have to be logged, False otherwise
	 **/
	public static function checkLevel($level, $configured_level=null) {
		if (is_null($configured_level) || !array_key_exists($configured_level, self :: $levels))
			$configured_level = self :: $level;

		// On unknown level, use default level
		if (!array_key_exists($level, self :: $levels))
			$level = self :: $default_level;

		return (self :: $levels[$level] >= self :: $levels[$configured_level]);
	}

	/**
	 * Helper to retreive current LDAP server name
	 *
	 * @retval string Current LDAP server name
	 **/
	private static function getLdapServerName() {
		if (LSsession :: $ldapServer) {
			if (isset(LSsession :: $ldapServer['name']))
				return LSsession :: $ldapServer['name'];
			else
				return "#".LSsession :: $ldapServerId;
		}
		return "Not connected";
	}

	/**
	 * Helper to retreive current authenticated user DN
	 *
	 * @retval string Current authenticated user DN
	 **/
	private static function getAuthenticatedUserDN() {
		$auth_dn = LSsession :: getLSuserObjectDn();
		if ($auth_dn)
			return LSsession :: getLSuserObjectDn();
		return "Anonymous";
	}

	/*
	 * PHP error logging helpers
	 */

	/**
	 * Generate current context backtrace
	 *
	 * @retval string Current context backtrace
	 **/
	public static function get_debug_backtrace_context() {
		$traces = debug_backtrace();
		if (!is_array($traces) || count($traces) <= 2)
			return "";

		$msg = array();
		for ($i=2; $i < count($traces); $i++) {
			$trace = array("#$i");
			if (isset($traces[$i]['file']))
				$trace[] = $traces[$i]['file'].(isset($traces[$i]['line'])?":".$traces[$i]['line']:"");
			if (isset($traces[$i]['class']) && isset($traces[$i]['function']))
				$trace[] = $traces[$i]['class'] . " " . $traces[$i]['type'] . " " . $traces[$i]['function']. "()";
			elseif (isset($traces[$i]['function']))
				$trace[] = $traces[$i]['function']. "()";
			$msg[] = implode(" - ", $trace);
		}

		return implode("\n", $msg);
	}

	/**
	 * PHP set_exception_handler helper
	 *
	 * @see https://www.php.net/set_exception_handler
	 **/
	public static function exception($exception, $prefix=null, $fatal=true) {
		$message = ($prefix?"$prefix :\n":"An exception occured :\n"). self :: get_debug_backtrace_context(). "\n" .
			   "## ".$exception->getFile().":".$exception->getLine(). " : ". $exception->getMessage();
		if ($fatal)
			self :: fatal($message);
		else
			self :: error($message);
	}

	/**
	 * PHP set_error_handler helper
	 *
	 * @see https://www.php.net/set_error_handler
	 **/
	public static function php_error($errno, $errstr, $errfile, $errline) {
		$errnos2error = array (
			1       => "ERROR",
			2       => "WARNING",
			4       => "PARSE",
			8       => "NOTICE",
			16      => "CORE_ERROR",
			32      => "CORE_WARNING",
			64      => "COMPILE_ERROR",
			128     => "COMPILE_WARNING",
			256     => "USER_ERROR",
			512     => "USER_WARNING",
			1024    => "USER_NOTICE",
			2048    => "STRICT",
			4096    => "RECOVERABLE_ERROR",
			8192    => "DEPRECATED",
			16384   => "USER_DEPRECATED",
			32767   => "ALL",
		);

		$errors2level = array (
			"ERROR"			=> "ERROR",
			"WARNING"		=> "WARNING",
			"PARSE"			=> "FATAL",
			"NOTICE"		=> "INFO",
			"CORE_ERROR"		=> "ERROR",
			"CORE_WARNING"		=> "WARNING",
			"COMPILE_ERROR"		=> "ERROR",
			"COMPILE_WARNING"	=> "WARNING",
			"USER_ERROR"		=> "ERROR",
			"USER_WARNING"		=> "WARNING",
			"USER_NOTICE"		=> "INFO",
			"STRICT"		=> "WARNING",
			"RECOVERABLE_ERROR"	=> "WARNING",
			"DEPRECATED"		=> "DEBUG",
			"USER_DEPRECATED"	=> "DEBUG",
			"ALL"			=> "ERROR",
			"UNKNOWN"		=> "ERROR",
		);
		$error = (isset($errnos2error[$errno])?$errnos2error[$errno]:'UNKNOWN');
		$level = (isset($errors2level[$error])?$errors2level[$error]:'ERROR');
		self :: logging($level, "A PHP $error occured (#$errno) : $errstr [$errfile:$errline]");
		return False;
	}

	/*
	 * Public logging methods
	 */

	/**
	 * Log a message with level DEBUG
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public static function debug($message) {
		self :: logging('DEBUG', $message);
	}

	/**
	 * Log a message with level INFO
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public static function info($message) {
		self :: logging('INFO', $message);
	}

	/**
	 * Log a message with level WARNING
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public static function warning($message) {
		self :: logging('WARNING', $message);
	}

	/**
	 * Log a message with level ERROR
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public static function error($message) {
		self :: logging('ERROR', $message);
	}

	/**
	 * Log a message with level FATAL
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public static function fatal($message) {
		self :: logging('FATAL', $message);
	}
}

/**
 * Error Codes
 */
LSerror :: defineError('LSlog_01',
_("LSlog : Fail to load logging handler %{handler}.")
);
