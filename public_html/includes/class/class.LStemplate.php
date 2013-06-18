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
 * Manage template
 *
 * This class is use to manage template in LdapSaisie.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LStemplate {

  /**
   * LStemplate configuration
   *
   * array(
   *   'smarty_path' => '/path/to/Smarty.php',
   *   'template_dir' => '/path/to/template/directory',
   *   'compile_dir' => '/path/to/compile/directory',
   *   'debug' => True,
   *   'debug_smarty' => True
   * ) 
   *
   **/
  private static $config = NULL;

  // Smarty object
  public static $_smarty = NULL;
  
  // Smarty version
  private static $_smarty_version = NULL;

  // Array of directories
  private static $directories = array(
                                  'local',
                                  LS_THEME
                                );

 /**
  * Start LStemplate
  *
  * Set configuration from parameter $config and initialize
  * Smarty object.
  *
  * @param[in] $config array LStemplate configuration
  *
  * @retval boolean True on success, False instead
  **/
  public static function start($config) {
    self :: $config = $config;

    if (LSsession :: includeFile($config['smarty_path'])) {
      self :: $_smarty = new Smarty();
      self :: $_smarty -> template_dir = $config['template_dir'];

      if ( ! is_writable($config['compile_dir']) ) {
        die(_('LStemplate : compile directory is not writable (dir : '.$config['compile_dir'].')'));
      }
      self :: $_smarty -> compile_dir = $config['compile_dir'];

      if ($config['debug']) {
        self :: $_smarty -> caching = 0;
        // cache files are always regenerated
        self :: $_smarty -> force_compile = TRUE;
        // recompile template if it is changed
        self :: $_smarty -> compile_check = TRUE;
        if ($config['debug_smarty']) {
          // debug smarty
          self :: $_smarty -> debugging = true;
        }
      }

      if (method_exists(self :: $_smarty,'register_function')) {
        self :: $_smarty_version=2;
        if (!LSsession :: loadLSclass('LStemplate_smarty2_support')) {
          die(_("LStemplate : Can't load Smarty 2 support file"));
        }

      }
      elseif (method_exists(self :: $_smarty,'registerPlugin')) {
        self :: $_smarty_version=3;
        if (!LSsession :: loadLSclass('LStemplate_smarty3_support')) {
          die(_("LStemplate : Can't load Smarty 3 support file"));
        }
      }
      else {
        die(_("LStemplate : Smarty version not recognized."));
      }

      self :: $_smarty -> assign('LS_CSS_DIR',LS_CSS_DIR);
      self :: $_smarty -> assign('LS_IMAGES_DIR',LS_IMAGES_DIR);

      return True;
    }
    else {
      die(_("LStemplate : Can't load Smarty."));
      return False;
    }
  }

 /**
  * Return the path of the file to use
  *
  * @param[in] string $name The file name (eg: mail.png)
  * @param[in] string $root_dir The root directory (eg: images)
  * @param[in] string $default_dir The default directory (eg: default)
  *
  * @retval string The path of the file
  **/
  public static function getFilePath($file,$root_dir,$default_dir='default') {
    foreach(self :: $directories as $dir) {
      if (file_exists($root_dir.'/'.$dir.'/'.$file)) {
        return $root_dir.'/'.$dir.'/'.$file;
      }
    }
    if (!$default_dir) {
      return;
    }
    return $root_dir.'/'.$default_dir.'/'.$file;
  }

 /**
  * Return the path of the Smarty template file to use
  *
  * @param[in] string $template The template name (eg: top.tpl)
  *
  * @retval string The path of the Smarty template file
  **/
  public static function getTemplatePath($template) {
    return self :: getFilePath($template,self :: $config['template_dir']);
  }

 /**
  * Return the content of a Smarty template file.
  *
  * @param[in] string $template The template name (eg: top.tpl)
  *
  * @retval string The content of the Smarty template file
  **/
  public static function getTemplateSource($template) {
    $tpl_path=self :: getTemplatePath($template);
    if (!is_readable($tpl_path)) {
      $tpl_path=self :: getTemplatePath('empty.tpl');
      LSerror::addErrorCode('LStemplate_01',$template);
    }
    return implode('',file($tpl_path));
  }

 /**
  * Return the timestamp of the last change of a Smarty
  * template file.
  *
  * @param[in] string $template The template name (eg: top.tpl)
  *
  * @retval string The timestamp of the last change of the Smarty template file
  **/
  public static function getTemplateTimestamp($template) {
    $tpl_path=self :: getTemplatePath($template);
    if (is_file($tpl_path)) {
      $time=filemtime($tpl_path);
      if ($time)
        return $time;
    }
    return NULL;
  }

 /**
  * Assign template variable
  *
  * @param[in] string $name The variable name
  * @param[in] mixed $value The variable value
  *
  * @retval void
  **/
  public static function assign($name,$value) {
    return self :: $_smarty -> assign($name,$value);
  }

 /**
  * Display a template
  *
  * @param[in] string $template The template name (eg: empty.tpl)
  *
  * @retval void
  **/
  public static function display($template) {
    return self :: $_smarty -> display("ls:$template");
  }

 /**
  * Fetch a template
  *
  * @param[in] string $template The template name (eg: empty.tpl)
  *
  * @retval string The template compiled
  **/
  public static function fetch($template) {
    return self :: $_smarty -> fetch("ls:$template");
  }

}

function LStemplate_smarty_getFData($params) {
    extract($params);
    echo getFData($format,$data,$meth=NULL);
}

function LStemplate_smarty_tr($params) {
  extract($params);
  echo __($msg);
}

// Errors
LSerror :: defineError('LStemplate_01',
_("LStemplate : Template %{file} not found.")
);
