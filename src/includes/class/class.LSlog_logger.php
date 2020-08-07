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
 * Logger class for LSlog
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog_logger {

	// Name
	private $name;

	// The handler configuration
	private $config;

	// Enabled/disabled
	private $enabled;

	// Level
	private $level;

	/**
	 * Constructor
	 *
	 * @param[in] $name string The logger name
	 * @param[in] $config array The handler configuration (optional, default: array())
	 *
	 * @retval void
	 **/
	public function __construct($name, $config=array()) {
		$this -> name = $name;
		$this -> config = $config;
		$this -> enabled = $this -> getConfig('enabled', true, 'boolean');
		$this -> level = $this -> getConfig('level');
		if ($this -> enabled)
			$this -> debug("Enabled $name logger with level=".$this -> level);
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
	 * Get logger info
	 *
	 * @param[in] $key string The info name
	 *
	 * @retval mixed The info value
	 **/
	public function __get($key) {
		switch ($key) {
			case 'name':
				return $this -> name;
			case 'enabled':
				return $this -> enabled;
			case 'level':
				return $this -> level;
		}
		return;
	}

	/**
	 * Check level against configured level
	 *
	 * @param[in] $level string The level
	 *
	 * @retval bool True if a message with this level have to be logged, False otherwise
	 **/
	public function checkLevel($level) {
		// If no level configured, always log
		if (!$this -> enabled || !$this -> level)
			return True;
		return LSlog :: checkLevel($level, $this -> level);
	}

	/**
	 * Log a message
	 *
	 * @param[in] $level string The message level
	 * @param[in] $message string The message
	 *
	 * @retval void
	 **/
	public function logging($level, $message) {
		if (!$this -> enabled || !$this -> checkLevel($level))
			return;
		LSlog :: logging($level, $message, $this -> name);
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
	public function trace($message) {
		$this -> logging('TRACE', $message);
	}

	/**
	 * Log a message with level DEBUG
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public function debug($message) {
		$this -> logging('DEBUG', $message);
	}

	/**
	 * Log a message with level INFO
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public function info($message) {
		$this -> logging('INFO', $message);
	}

	/**
	 * Log a message with level WARNING
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public function warning($message) {
		$this -> logging('WARNING', $message);
	}

	/**
	 * Log a message with level ERROR
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public function error($message) {
		$this -> logging('ERROR', $message);
	}

	/**
	 * Log a message with level FATAL
	 *
	 * @param[in] $message The message to log
	 *
	 * @retval void
	 **/
	public function fatal($message) {
		$this -> logging('FATAL', $message);
	}

	/**
	 * Log an exception
	 *
	 * @param[in] $exception Exception The exception to log
	 * @param[in] $prefix string|null Custom message prefix (optional, see LSlog :: exception())
	 * @param[in] $fatal boolean Log exception as a fatal error (optional, default: true)
	 *
	 * @retval void
	 **/
	public function exception($exception, $prefix=null, $fatal=true) {
		if (!$this -> enabled)
			return;
		LSlog :: exception($exception, $prefix, $fatal, $this -> name);
	}
}
