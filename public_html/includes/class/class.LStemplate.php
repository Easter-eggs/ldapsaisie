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
   *   'image_dir' => '/path/to/image/directory',
   *   'css_dir' => '/path/to/css/directory',
   *   'compile_dir' => '/path/to/compile/directory',
   *   'debug' => True,
   *   'debug_smarty' => True
   * )
   *
   **/
  private static $config = array (
    'smarty_path' => 'smarty/libs/Smarty.class.php',
    'template_dir' => 'templates',
    'image_dir' => 'images',
    'css_dir' => 'css',
    'js_dir' => 'includes/js',
    'libs_dir' => 'includes/libs',
    'compile_dir' => 'tmp',
    'debug' => False,
    'debug_smarty' => False
  );

  // Smarty object
  public static $_smarty = NULL;

  // Smarty version
  private static $_smarty_version = NULL;

  // Array of directories
  private static $directories = array('local', LS_THEME, './');

  // Registered events
  private static $_events = array();

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
    // Trigger starting event
    self :: fireEvent('starting');

    foreach ($config as $key => $value) {
      self :: $config[$key] = $value;
    }

    if (LSsession :: includeFile(self :: $config['smarty_path'], true)) {
      self :: $_smarty = new Smarty();
      self :: $_smarty -> template_dir = self :: $config['template_dir'];

      if ( ! is_writable(self :: $config['compile_dir']) ) {
        LSlog :: fatal(getFData(_("LStemplate : compile directory is not writable (dir : %{dir})"), self :: $config['compile_dir']));
      }
      self :: $_smarty -> compile_dir = self :: $config['compile_dir'];

      if (self :: $config['debug']) {
        self :: $_smarty -> caching = 0;
        // cache files are always regenerated
        self :: $_smarty -> force_compile = TRUE;
        // recompile template if it is changed
        self :: $_smarty -> compile_check = TRUE;
        if (self :: $config['debug_smarty']) {
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

      self :: registerFunction("getFData", "LStemplate_smarty_getFData");
      self :: registerFunction("tr", "LStemplate_smarty_tr");
      self :: registerFunction("img", "LStemplate_smarty_img");
      self :: registerFunction("css", "LStemplate_smarty_css");
      self :: registerFunction("uniqid", "LStemplate_smarty_uniqid");
      self :: registerFunction("var_dump", "LStemplate_smarty_var_dump");

      // Define public root URL
      $public_root_url = LSconfig :: get('public_root_url', '/', 'string');
      // Remove trailing slash
      if (substr($public_root_url, -1) == '/')
        $public_root_url =  substr($public_root_url, 0, -1);
      self :: assign('public_root_url', $public_root_url);

      // Trigger started event
      self :: fireEvent('started');

      return True;
    }
    else {
      die(_("LStemplate : Can't load Smarty."));
      return False;
    }
  }

 /**
  * Return the default directory path of files
  *
  * Return LS_THEME contanst value or 'default' if not defined
  *
  * @retval string The default directory path of files
  **/
  public static function getDefaultDir() {
    if (defined('LS_THEME'))
      return LS_THEME;
    else
      return 'default';
  }

 /**
  * Return the path of the file to use
  *
  * @param[in] string $name The file name (eg: mail.png)
  * @param[in] string $root_dir The root directory (eg: images)
  * @param[in] string $default_dir The default directory (eg: default)
  * @param[in] bool $with_nocache If true, include nocache URL param (default: false)
  *
  * @retval string The path of the file
  **/
  public static function getFilePath($file, $root_dir, $default_dir=null, $with_nocache=false) {
    if ($default_dir === null)
      $default_dir = self :: getDefaultDir();
    $path = false;
    foreach(self :: $directories as $dir) {
      $dir_path = realpath($root_dir.'/'.$dir);
      if ($dir_path === false)
        // Directory not found or not accessible
        continue;
      $file_path = realpath($dir_path.'/'.$file);
      if ($file_path === false)
        // File not found or not accessible
        continue;
      // Checks that the file is in the actual folder location
      $pos = strpos($file_path, $dir_path);
      if (!is_int($pos) || $pos != 0) {
        LSlog :: error("LStemplate :: getFilePath($file, $root_dir, $default_dir, $with_nocache) : File '$file_path' is not in root directory '$dir_path' (".varDump($pos).").");
      }
      elseif (file_exists($file_path)) {
        $path = $file_path;
        break;
      }
    }
    if (!$path) {
      if (!$default_dir)
        return;
      $path = $root_dir.'/'.$default_dir.'/'.$file;
    }
    if ($with_nocache)
      $path .= "?nocache=".self::getNoCacheFileValue($path);
    return $path;
  }

 /**
  * Return the path of the image file to use
  *
  * @param[in] string $image The image name (eg: mail)
  * @param[in] bool $with_nocache If true, include nocache URL param (default: false)
  *
  * @retval string The path of the image file
  **/
  public static function getImagePath($image, $with_nocache=false) {
    $exts=array('png','gif','jpg');
    foreach($exts as $ext) {
      $path = self :: getFilePath("$image.$ext", self :: $config['image_dir'], False, $with_nocache);
      if ($path) return $path;
    }
    return self :: $config['image_dir']."/".self :: getDefaultDir()."/$image.png";
  }

 /**
  * Return the path of the CSS file to use
  *
  * @param[in] string $css The CSS name (eg: main.css)
  * @param[in] bool $with_nocache If true, include nocache URL param (default: false)
  *
  * @retval string The path of the CSS file
  **/
  public static function getCSSPath($css, $with_nocache=false) {
    return self :: getFilePath($css, self :: $config['css_dir'], Null, $with_nocache);
  }

 /**
  * Return the path of the JS file to use
  *
  * @param[in] string $js The JS name (eg: LSdefaults.js)
  * @param[in] bool $with_nocache If true, include nocache URL param (default: false)
  *
  * @retval string The path of the CSS file
  **/
  public static function getJSPath($js, $with_nocache=false) {
    return self :: getFilePath($js, self :: $config['js_dir'], Null, $with_nocache);
  }

 /**
  * Return the path of the libary file to use
  *
  * @param[in] string $file_path The lib file path (eg: arian-mootools-datepicker/Picker.js)
  * @param[in] bool $with_nocache If true, include nocache URL param (default: false)
  *
  * @retval string The path of the Lib file
  **/
  public static function getLibFilePath($file_path, $with_nocache=false) {
    return self :: getFilePath($file_path, self :: $config['libs_dir'], Null, $with_nocache);
  }

 /**
  * Return the path of the Smarty template file to use
  *
  * @param[in] string $template The template name (eg: top.tpl)
  * @param[in] bool $with_nocache If true, include nocache URL param (default: false)
  *
  * @retval string The path of the Smarty template file
  **/
  public static function getTemplatePath($template, $with_nocache=false) {
    return self :: getFilePath($template, self :: $config['template_dir'], null, $with_nocache);
  }

 /**
  * Return the nocache value of the specify file
  *
  * @param[in] string $file The file path
  *
  * @retval string The specified file's nocache value
  **/
  public static function getNoCacheFileValue($file) {
    $stat = @stat($file);
    if (is_array($stat) && isset($stat['mtime']))
      return md5($stat['mtime']);
    return md5(time());
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
      if (self :: $_smarty_version > 2) {
        // No error return with Smarty3 and highter because it's call
        // template name in lower first systematically
        return '';
      }
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
    // Trigger displaying event
    self :: fireEvent('displaying');

    try {
      self :: $_smarty -> display("ls:$template");
    }
    catch (Exception $e) {
      LSlog :: exception($e, getFData(_("Smarty - An exception occured displaying template '%{template}'"), $template));
      exit();
    }

    // Trigger displayed event
    self :: fireEvent('displayed');
  }

 /**
  * Fetch a template
  *
  * @param[in] string $template The template name (eg: empty.tpl)
  *
  * @retval string The template compiled
  **/
  public static function fetch($template) {
    try {
      return self :: $_smarty -> fetch("ls:$template");
    }
    catch (Exception $e) {
      LSlog :: exception($e, getFData(_("Smarty - An exception occured fetching template '%{template}'"), $template), false);
    }
  }

  /**
   * Handle fatal error
   *
   * @param[in] $error string|null Error message (optional)
   *
   * @retval void
   **/
  public static function fatal_error($error=null) {
    self :: $_smarty -> assign('fatal_error', $error);
    self :: $_smarty -> display("ls:fatal_error.tpl");
    exit();
  }

 /**
  * Register a template function
  *
  * @param[in] string $name The function name in template
  * @param[in] string $function_name The function name in PHP
  *
  * @retval void
  */
  public static function registerFunction($name,$function_name) {
    LStemplate_register_function($name,$function_name);
  }

  /**
   * Registered an action on a specific event
   *
   * @param[in] $event string The event name
   * @param[in] $callable callable The callable to run on event
   * @param[in] $param mixed Paremeters that will be pass to the callable
   *
   * @retval void
   */
  public static function addEvent($event,$callable,$param=NULL) {
    self :: $_events[$event][] = array(
      'callable' => $callable,
      'param'    => $param,
    );
  }

  /**
   * Run triggered actions on specific event
   *
   * @param[in] $event string Event name
   *
   * @retval boolean True if all triggered actions succefully runned, false otherwise
   */
  public static function fireEvent($event) {
    $return = true;

    // Binding via addEvent
    if (isset(self :: $_events[$event]) && is_array(self :: $_events[$event])) {
      foreach (self :: $_events[$event] as $e) {
        if (is_callable($e['callable'])) {
          try {
            call_user_func_array($e['callable'],array(&$e['param']));
          }
          catch(Exception $er) {
            LSerror :: addErrorCode('LStemplate_03',array('callable' => getCallableName($e['callable']),'event' => $event));
            $return = false;
          }
        }
        else {
          LSerror :: addErrorCode('LStemplate_02',array('callable' => getCallableName($e['callable']),'event' => $event));
          $return = false;
        }
      }
    }

    return $return;
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

function LStemplate_smarty_img($params) {
  extract($params);
  echo "image/$name";
}

function LStemplate_smarty_css($params) {
  echo "css/".$params['name'];
}

function LStemplate_smarty_uniqid($params, &$smarty) {
  if (!isset($params['var']))
    $params['var'] = 'uniqid';
  $smarty -> assign($params['var'], uniqid());
}

function LStemplate_smarty_var_dump($params, &$smarty) {
  var_dump($params['data']);
}

// Errors
LSerror :: defineError('LStemplate_01',
_("LStemplate : Template %{file} not found.")
);
LSerror :: defineError('LStemplate_02',
_("LStemplate : Fail to execute trigger %{callable} on event %{event} : is not callable.")
);
LSerror :: defineError('LStemplate_03',
_("LStemplate : Error during the execution of the trigger %{callable} on event %{event}.")
);
