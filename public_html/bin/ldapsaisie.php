#!/usr/bin/php
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

require_once realpath(dirname(__FILE__)."/../")."/core.php";

if(LSsession :: startCliLSsession()) {

  // Load some default classes
  foreach(array('LSsearch', 'LSldapObject') as $class)
    if (!LSsession :: loadLSClass($class))
      LSlog :: fatal("Fail to load class $class.");

  // Handle CLI arguments and run command (if provided)
  LScli :: handle_args();

}
else {
  die('An error occured initializing CLI LSsession');
}
