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
 * Object LSconfig
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSconfig { 
  
  // Configuration Data
  private static $data=array();
  
 /**
  * Lancement de LSconfig
  * 
  * Chargement initiale des données de configuration à partir des fichiers en
  * config.*.php du dossier LS_CONF_DIR
  * 
  * @retval boolean True si tout s'est bien passé, False sinon
  **/
  public static function start() {
    $files=array('config.inc.php','config.LSaddons.php');
    foreach($files as $file) {
      if (!LSsession::includeFile(LS_CONF_DIR.'/'.$file)) {
        return;
      }
    }
    if (is_array($GLOBALS['LSconfig'])) {
      self :: $data = $GLOBALS['LSconfig'];
      self :: $data['LSaddons'] = $GLOBALS['LSaddons'];
      return true;
    }
    return;
  }
  
 /**
  * Get a specific configuration variable value
  * 
  * @param[in] $var string The configuration variable name
  * @param[in] $default mixed The default value to return if configuration variable
  *                           is not set (Default : null)
  * @param[in] $cast string   The type of expected value. The configuration variable
  *                           value will be cast as this type. Could be : bool, int,
  *                           float or string. (Optional, default : raw value)
  * @param[in] $data array    The configuration data (optional)
  * 
  * @retval mixed The configuration variable value
  **/
  public static function get($var, $default=null, $cast=null, $data=null) {
    $vars = explode('.', $var);
    $value = $default;
    if (is_array($vars)) {
      $value = (is_array($data)?$data:self :: $data);
      foreach ($vars as $v) {
        if (!is_array($value) || !isset($value[$v])) {
          $value = $default;
          break;
        }
        $value = $value[$v];
      }
    }
    switch($cast) {
      case 'bool':
        return boolval($value);
      case 'int':
        return intval($value);
      case 'float':
        return floatval($value);
      case 'string':
        return strval($value);
      default:
        return $value;
    }
  }

 /**
  * Get list of keys of a specific configuration variable
  *
  * @param[in] $var string The configuration variable name
  * @param[in] $data array The configuration data (optional)
  *
  * @retval array An array of the keys of a specific configuration variable
  **/
  public static function keys($var, $data=null) {
    $value = self :: get($var, null, null, $data);
    return (is_array($value)?array_keys($value):array());
  }

 /**
  * Get list of configuration variables with their value that matching a specific pattern
  *
  * The character '*' could replace any part (expect the first one) of the configuration
  * variable name. In this case, the keys of the parent value will be iterated to compose
  * the result.
  *
  * @param[in] $pattern string The configuration variable pattern
  * @param[in] $default mixed  The default value to return if configuration variable
  *                            is not set (optional, see self :: get())
  * @param[in] $cast string    The type of expected value (optional, see self :: get())
  * @param[in] $data array     The configuration data (optional, see self :: get())
  *
  * @retval array The list of matching configuration variables with their value
  **/
  public static function getMatchingKeys($pattern, $default=null, $cast=null, $data=null, $prefix=null) {
    $return = array();
    if ($pos = strpos($pattern, '*')) {
      // Handle subkey
      $root_key = (is_string($prefix)?"$prefix.":"").substr($pattern, 0, ($pos-1));
      $suffix = substr($pattern, $pos+2, (strlen($pattern)-$pos));
      $subkeys = self :: keys($root_key);
      if ($suffix) {
        foreach ($subkeys as $subkey)
          $return = array_merge($return, self :: getMatchingKeys($suffix, $default, $cast, $data, "$root_key.$subkey"));
      }
      else {
        foreach ($subkeys as $subkey) {
          $key = "$root_key.$subkey";
          $return[$key] = self :: get($key, $default, $cast, $data);
        }
      }
    }
    else {
      $key = (is_string($prefix)?"$prefix.":"").$pattern;
      $return[$key] = self :: get($key, $default, $cast, $data);
    }
    return $return;
  }

 /**
  * Définition d'une valeur
  * 
  * @param[in] $var string Le nom de valeur à définir
  * @param[in] $val mixed La valeur de la variable
  * 
  * @retval boolean True si tout s'est bien passé, False sinon
  **/  
  public static function set($var,$val) {
    $vars=explode('.',$var);
    if(is_array($vars)) {
      $code='self :: $data';
      foreach ($vars as $v) {
        $code.='["'.$v.'"]';
      }
      $code.='=$val;';
      return (eval($code)===NULL);
    }
    return;
  }
  
}

