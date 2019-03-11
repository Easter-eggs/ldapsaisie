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
 *******************************************************************************/

/** 
 * Support Smarty2 for LStemplate
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 **/

/**
 * Retrieve a resource
 *
 * @param[in] $tpl_name string The template name
 * @param[in] $tpl_source string Variable passed by reference
 *                               where the result should be stored.
 * @param[in] $smarty_obj object The Smarty object
 *
 * @return bool TRUE if it was able to successfully retrieve 
 *              the resource and FALSE otherwise.
 **/
function LStemplate_smarty_get_template ($tpl_name, &$tpl_source, &$smarty_obj) {
  $tpl_source=LStemplate :: getTemplateSource($tpl_name);
  return True;
}

/**
 * Retrieve the last modification timestamp of a template 
 *
 * @param[in] $tpl_name string The template name
 * @param[in] $tpl_timestamp int Variable passed by reference
 *                               where the result should be stored.
 * @param[in] $smarty_obj object The Smarty object
 *
 * @return bool TRUE if the timestamp could be succesfully determined,
 *               or FALSE otherwise 
 **/
function LStemplate_smarty_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
  $time=LStemplate :: getTemplateTimestamp($tpl_name);
  if ($time) {
    $tpl_timestamp=$time;
    return True;
  }
  return False;
}

/**
 * Determine if template is secured or not
 *
 * This function is used only for template resources but should 
 * still be defined.
 *
 * @param[in] $tpl_name string The template name
 * @param[in] $smarty_obj object The Smarty object
 *
 * @return bool TRUE if the template is secured, or FALSE otherwise 
 **/
function LStemplate_smarty_get_secure($tpl_name, &$smarty_obj) {
  return True;
}

/**
 * Determine if template is trusted or not
 *
 * This function is used for only for PHP script components
 * requested by {include_php} tag or {insert} tag with the
 * src attribute. However, it should still be defined even
 * for template resources.
 *
 * @param[in] $tpl_name string The template name
 * @param[in] $smarty_obj object The Smarty object
 *
 * @return bool TRUE if the template is trusted, or FALSE otherwise 
 **/
function LStemplate_smarty_get_trusted($tpl_name, &$smarty_obj) {
  return True;
}

// Register 'ls' template ressource
LStemplate :: $_smarty -> register_resource('ls', array(
                                              'LStemplate_smarty_get_template',
                                              'LStemplate_smarty_get_timestamp',
                                              'LStemplate_smarty_get_secure',
                                              'LStemplate_smarty_get_trusted'
                                            )
                                     );
/**
 * Register a template function
 *
 * @param[in] string $name The function name in template
 * @param[in] string $function_name The function name in PHP
 *
 * @retval void
 */
function LStemplate_register_function($name,$function_name) {
  LStemplate :: $_smarty -> register_function($name,$function_name);
}

