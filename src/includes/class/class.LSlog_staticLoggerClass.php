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
 * Class definition helper to have a static class logger
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSlog_staticLoggerClass {


  /*
   * Log a message via class logger
   *
   * @param[in] $level string The log level (see LSlog)
   * @param[in] $message string The message to log
   *
   * @retval void
   **/
  protected static function log($level, $message) {
    LSlog :: get_logger(get_called_class()) -> logging($level, $message);
  }

  /**
	 * Log an exception via class logger
	 *
	 * @param[in] $exception Exception The exception to log
	 * @param[in] $prefix string|null Custom message prefix (optional, see self :: log_exception())
	 * @param[in] $fatal boolean Log exception as a fatal error (optional, default: true)
	 *
	 * @retval void
	 **/
	protected static function log_exception($exception, $prefix=null, $fatal=true) {
    if (is_null(self :: $logger))
      self :: $logger = LSlog :: get_logger(get_called_class());
    self :: $logger -> exception($exception, $prefix, $fatal);
  }

}
