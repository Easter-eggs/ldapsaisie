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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Driver to manage ioFormat file of LSldapObject import/export
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSioFormatDriver extends LSlog_staticLoggerClass {

  protected $options=array();

  /**
   * Constructor
   *
   * @param[in] array $options Driver's options
   *
   * @retval void
   **/
  public function __construct($options) {
    $this -> options = $options;
  }

  /**
   * Load file
   *
   * @param[in] string $file The file path to load
   *
   * @retval boolean True if file is loaded, false otherwise
   **/
  public function loadFile($path) {
    return False;
  }

  /**
   * Check if loaded file data are valid
   *
   * @retval boolean True if loaded file data are valid, false otherwise
   **/
  public function isValid() {
    return False;
  }

  /**
   * Retreive all object data contained by the loaded file
   *
   * The objects are returned in array :
   *
   *  array (
   *    array ( // Object 1
   *      '[field1]' => '[value1]',
   *      '[field2]' => '[value2]',
   *      [...]
   *    ),
   *    array ( // Object 2
   *      '[field1]' => '[value1]',
   *      '[field2]' => '[value2]',
   *      [...]
   *    ),
   *  )
   *
   * @retval array The objects contained by the loaded file
   **/
  public function getAll() {
    return array();
  }

  /**
   * Retreive fields names of the loaded file
   *
   * The fields names are returned in array :
   *
   *  array (
   *    '[field1]',
   *    '[field2]',
   *    [...]
   *  )
   *
   * @retval array The fields names of the loaded file
   **/
  public function getFieldNames() {
    return array();
  }

  /**
   * Retreive all objects data of the loaded file formated
   *
   * This method format objects data using ioFormat configuration
   * given as parameters.
   *
   * @param[in] $fields Array of file's fields name mapping with object attribute
   * @param[in] $generated_fields Array of object attribute to generate using other object data
   *
   * @retval array All objects data of the loaded file formated
   **/
  public function getAllFormated($fields, $generated_fields) {
    if (!is_array($fields)) return False;
    if (!is_array($generated_fields)) $generated_fields=array();
    $all = $this -> getAll();
    if (!is_array($all)) return False;
    $retall = array();
    foreach($all as $one) {
      $retone = array();
      foreach($fields as $key => $attr) {
        if (!isset($one[$key])) continue;
        if (!isset($retone[$attr])) $retone[$attr] = array();
        $retone[$attr][] = $one[$key];
      }
      foreach ($generated_fields as $attr => $format) {
        $value = getFData($format, $retone);
        if (!empty($value)) {
          $retone[$attr] = array($value);
        }
      }
      $retall[] = $retone;
    }

    return $retall;
  }

  /**
   * Export objects data
   *
   * @param[in] $objects_data Array of objects data to export
   * @param[in] $stream resource|null The output stream (optional, default: STDOUT)
   *
   * @return boolean True on succes, False otherwise
   */
  public function exportObjectsData($objects_data, $stream=null) {
    // Must be implement in real drivers
    return False;
  }

  /**
   * Return a option parameter (or default value)
   *
   * @param[] $param	The option parameter
   * @param[] $default	The default value (default : null)
   * @param[] $cast	Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The option parameter value or default value if not set
   **/
  public function getOption($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, $this -> options);
  }

}
