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
 * Handle logging to email (using error_log PHP function with message_type = 1)
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog_email extends LSlog_handler {

	// The configured email recipient
	private $recipient = null;

	/**
	 * Constructor
	 *
	 * @param[in] $config array The handler configuration
	 *
	 * @retval void
	 **/
	public function __construct($config) {
		parent :: __construct($config);
		$this -> recipient = self :: getConfig('recipient');
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
		if ($this -> recipient)
			return error_log($message, 1, $this -> recipient);
		return false;
	}
}
