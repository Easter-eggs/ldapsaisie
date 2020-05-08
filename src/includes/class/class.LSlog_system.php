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
 * Handle system logging (using error_log PHP function)
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog_system extends LSlog_handler {

	// Default datetime prefix (enabled/disabled)
	protected $default_datetime_prefix = false;

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
		error_log(
			$this -> format($level, $message, $logger)
		);
	}
}
