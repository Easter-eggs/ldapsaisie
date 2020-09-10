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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Default logging handler
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog_handler extends LSlog_staticLoggerClass {

	// The handler configuration
	protected $config;

	// Log level
	protected $level;

	// Default log formats
	protected $default_format = '%{requesturi} - %{remoteaddr} - %{ldapservername} - %{authuser} - %{logger} - %{level} - %{message}';
	protected $default_cli_format = '%{clibinpath} - %{logger} - %{level} - %{message}';

	// Default datetime prefix (enabled/disabled)
	protected $default_datetime_prefix = true;

	// Default datetime format
	protected $default_datetime_format = 'Y/m/d H:i:s';

	// Loggers filters
	protected $loggers = array();
	protected $excluded_loggers = array();

	/**
	 * Constructor
	 *
	 * @param[in] $config array The handler configuration
	 *
	 * @retval void
	 **/
	public function __construct($config) {
		$this -> config = $config;
		$this -> level = $this -> getConfig('level', null, 'string');
		$this -> loggers = $this -> getConfig('loggers', array());
		if (!is_array($this -> loggers))
			$this -> loggers = array($this -> loggers);
		$this -> excluded_loggers = $this -> getConfig('excluded_loggers', array());
		if (!is_array($this -> excluded_loggers))
			$this -> excluded_loggers = array($this -> excluded_loggers);
	}

  /**
   * Allow conversion of LSlog_handler to string
   *
   * @retval string The string representation of the LSlog_handler
   */
  public function __toString() {
    return "<".get_class($this)." ".implode(', ', $this -> __toStringDetails()).">";
  }

  /**
   * Return list of details for the string representation of the LSlog_handler
   *
   * @retval array List of details for the string representation of the LSlog_handler
   */
	public function __toStringDetails() {
		return array(
			"level=".($this -> level?$this -> level:'default'),
			"loggers=".($this -> loggers?implode(',', $this -> loggers):'all'),
			"excluded loggers=".($this -> excluded_loggers?implode(',', $this -> excluded_loggers):'no'),
		);
	}

	/**
	 * Get handler info
	 *
	 * @param[in] $key string The info name
	 *
	 * @retval mixed The info value
	 **/
	public function __get($key) {
		switch ($key) {
			case 'enabled':
				return $this -> getConfig('enabled', true, 'bool');
			case 'format':
				if (php_sapi_name() == "cli")
					$format = $this -> getConfig('cli_format', $this -> default_cli_format, 'string');
				else
					$format = $this -> getConfig('format', $this -> default_format, 'string');
				// Add datetime prefix (if enabled)
				if ($this -> getConfig('datetime_prefix', $this -> default_datetime_prefix, 'boolean')) {
					$format = date($this -> getConfig('datetime_format', $this -> default_datetime_format, 'string'))." - $format";
				}
				return $format;
		}
		// Unknown key, log warning
		self :: log_warning("__get($key): invalid property requested\n".LSlog :: get_debug_backtrace_context());
	}

	/**
	 * Check system compatibility with this handler
	 *
	 * Note : LSlog do not generate no error about imcompatibly, it's
	 * just omit this handler if system is incompatible. You have to
	 * trigger it with this method if you want.
	 *
	 * @retval bool True if system is compatible, False otherwise
	 **/
	public function checkCompatibility() {
		return True;
	}

	/**
	 * Get a configuration variable value
	 *
	 * @param[in] $var string The configuration variable name
	 * @param[in] $default mixed The default value to return if configuration variable
	 *                           is not set (Default : null)
	 * @param[in] $cast string   The type of expected value. The configuration variable
	 *                           value will be cast as this type. Could be : bool, int,
	 *                           float or string. (Optional, default : raw value)
	 *
	 * @retval mixed The configuration variable value
	 **/
	public function getConfig($var, $default=null, $cast=null) {
		return LSconfig :: get($var, $default, $cast, $this -> config);
	}

	/**
	 * Set log level
	 *
	 * @param[in] $level string The level
	 *
	 * @retval bool True if log level set, False otherwise
	 **/
	public function setLevel($level) {
		if (!is_null($level) && !LSlog :: checkLevelExists($level)) {
			self :: log_error("Invalid log level '$level'");
			return false;
		}
		self :: log_debug("Log handler ".get_called_class()." level set to ".(is_null($level)?'default':$level));
		$this -> level = $level;
	}

	/**
	 * Check level against configured level
	 *
	 * @param[in] $level string The level
	 *
	 * @retval bool True if a message with this level have to be logged, False otherwise
	 **/
	public function checkLevel($level) {
		return LSlog :: checkLevel($level, $this -> level);
	}

	/**
	 * Check logger against configured loggers filters
	 *
	 * @param[in] $logger string The logger
	 *
	 * @retval bool True if message of this logger have to be logged, False otherwise
	 **/
	public function checkLogger($logger) {
		if (!$this -> loggers && !$this -> excluded_loggers)
			return true;
		if ($this -> loggers && in_array($logger, $this -> loggers))
			return true;
		if ($this -> excluded_loggers && !in_array($logger, $this -> excluded_loggers))
			return true;
		return false;
	}

	/**
	 * Log a message
	 *
	 * @param[in] $level string The message level
	 * @param[in] $message string The message
	 * @param[in] $logger string|null The logger name (optional, default: null)
	 *
	 * @retval void
	 **/
	public function logging($level, $message, $logger=null) {
		return;
	}

	/**
	 * Format a message
	 *
	 * @param[in] $level string The message level
	 * @param[in] $message string The message
	 * @param[in] $logger string|null The logger name (optional, default: null)
	 *
	 * @retval string The formated message to log
	 **/
	protected function format($level, $message, $logger=null) {
		global $argv;
		return getFData(
			$this -> format,
			array(
					'level' => $level,
					'message' => $message,
					'logger' => ($logger?$logger:'default'),
					'clibinpath' => (isset($argv)?basename($argv[0]):'unknown bin path'),
					'requesturi' => (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'unknown request URI'),
					'remoteaddr' => (isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'unknown remote address'),
					'ldapservername' => self :: getLdapServerName(),
					'authuser' => self :: getAuthenticatedUserDN(),
			)
		);
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
}
