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
 * Default logging handler
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog_handler {

	// The handler configuration
	private $config;

	/**
	 * Constructor
	 *
	 * @param[in] $config array The handler configuration
	 *
	 * @retval void
	 **/
	public function __construct($config) {
		$this -> config = $config;
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
	 * Check level against configured level
	 *
	 * @param[in] $level string The level
	 *
	 * @retval bool True if a message with this level have to be logged, False otherwise
	 **/
	public function checkLevel($level) {
		return LSlog :: checkLevel($level, $this -> getConfig('level'));
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
		return false;
	}
}
