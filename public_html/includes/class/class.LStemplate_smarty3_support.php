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
 * Smarty ressource for LdapSaisie template
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class Smarty_Resource_LdapSaisie extends Smarty_Resource_Custom {

 // prepared fetch() statement
 protected $fetch;
 // prepared fetchTimestamp() statement
 protected $mtime;

 /**
  * Fetch a template and its modification time from database
  *
  * @param string $name template name
  * @param string $source template source
  * @param integer $mtime template modification timestamp (epoch)
  * @return void
  */
 protected function fetch($name, &$source, &$mtime) {
   $source = LStemplate :: getTemplateSource($name);
   $mtime = LStemplate :: getTemplateTimestamp($name);
 }
 
 /**
  * Fetch a template's modification time from database
  *
  * @note implementing this method is optional. Only implement it if modification times can be accessed faster than loading the comple template source.
  * @param string $name template name
  * @return integer timestamp (epoch) the template was modified
  */
 protected function fetchTimestamp($name) {
   return LStemplate :: getTemplateTimestamp($name);
 }
}

// Register 'ls' template ressource
LStemplate :: $_smarty -> registerResource('ls', new Smarty_Resource_LdapSaisie());

/**
 * Register a template function
 *
 * @param[in] string $name The function name in template
 * @param[in] string $function_name The function name in PHP
 *
 * @retval void
 */
function LStemplate_register_function($name,$function_name) {
  LStemplate :: $_smarty -> registerPlugin("function",$name,$function_name);
}

