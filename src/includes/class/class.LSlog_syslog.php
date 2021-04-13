<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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
 * Handle logging to syslog
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog_syslog extends LSlog_handler {

	// Force syslog priority
	private $priority = null;

	// Levels to syslog priority mapping
	private static $levels2priority = array (
		'EMERG'	=>	LOG_EMERG, 	// system is unusable
		'ALERT'	=>	LOG_ALERT, 	// action must be taken immediately
		'CRITICAL' => 	LOG_CRIT, 	// critical conditions
		'ERROR' => 	LOG_ERR, 	// error conditions
		'WARNING' =>	LOG_WARNING, 	// warning conditions
		'NOTICE' =>	LOG_NOTICE, 	// normal, but significant, condition
		'INFO' => 	LOG_INFO, 	// informational message
		'DEBUG' =>	LOG_DEBUG, 	// debug-level message
		'TRACE' =>	LOG_DEBUG, 	// also debug-level message
	);

	// Default syslog priority (used if level is not provided or invalid)
	private static $default_priority = LOG_WARNING;

	// Default datetime prefix (enabled/disabled)
	protected $default_datetime_prefix = false;

	/**
	 * Constructor
	 *
	 * @param[in] $config array The handler configuration
	 *
	 * @retval void
	 **/
	public function __construct($config) {
		parent :: __construct($config);
		$this -> priority = static :: getConfig('priority');
		if ($this -> enabled)
			self :: log_trace("$this Enabled", get_class($this));
	}

	/**
	 * Return list of details for the string representation of the LSlog_email
	 *
	 * @retval array List of details for the string representation of the LSlog_email
	 */
	public function __toStringDetails() {
		return array_merge(
			array("priority=".$this -> priority),
			parent :: __toStringDetails()
		);
	}

	/**
	 * Check system compatibility with this handler
	 *
	 * @retval bool True if system is compatible, False otherwise
	 **/
	public function checkCompatibility() {
		return function_exists('syslog');
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
		return syslog(
			$this -> level2priority($level),
			$this -> format($level, $message, $logger)
		);
	}

	/**
	 * Get syslog corresponding priority to a specific log level
	 *
	 * @param[in] $level string The log level
	 *
	 * @retval int Syslog corresponding priority
	 **/
	private function level2priority($level) {
		if ($this -> priority && $level != $this -> priority)
			return $this -> level2priority($this -> priority);
		if (array_key_exists($level, static :: $levels2priority))
			return static :: $levels2priority[$level];
		return static :: $default_priority;
	}
}
