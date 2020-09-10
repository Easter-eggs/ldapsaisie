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
 * Handle logging to file (using error_log PHP function with message_type = 3)
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog_file extends LSlog_handler {

	// The configured logfile path
	private $path;

	/**
	 * Constructor
	 *
	 * @param[in] $config array The handler configuration
	 *
	 * @retval void
	 **/
	public function __construct($config) {
		parent :: __construct($config);
		// For reto-compatibilty, use LSlog.filename as default log path value
		$this -> path = self :: getConfig('path', LSlog :: getConfig('filename', 'tmp/LS.log'));
		if (substr($this -> path, 0, 1) != '/')
			$this -> path = LS_ROOT_DIR."/".$this -> path;
		if ($this -> enabled)
			self :: log_trace("$this Enabled", get_class($this));
	}

  /**
   * Return list of details for the string representation of the LSlog_file
   *
   * @retval array List of details for the string representation of the LSlog_file
   */
	public function __toStringDetails() {
		return array_merge(
			array("path=".$this -> path),
			parent :: __toStringDetails()
		);
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
		return error_log(
			$this -> format($level, $message, $logger)."\n",
			3,
			$this -> path
		);
	}
}
