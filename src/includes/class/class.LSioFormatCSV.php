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

require 'File/CSV/DataSource.php';
LSsession :: loadLSclass('LSioFormatDriver');

/**
 * Driver to manage CSV ioFormat file of LSldapObject import/export
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSioFormatCSV extends LSioFormatDriver {

  private $delimiter = null;
  private $enclosure = null;
  private $escape = null;
  private $length = null;

  private $rows = null;
  private $headers = null;

  /**
   * Constructor
   *
   * @param[in] array $options Driver's options
   *
   * @retval void
   **/
  public function __construct($options) {
    parent :: __construct($options);
    // As recommend in PHP doc, we enable this ini parameter to allow detection of
    // Macintosh line-ending convention.
    ini_set("auto_detect_line_endings", true);

    // Set CSV input/output parameters
    $this -> delimiter = $this -> getOption('delimiter', ",", "string");
    $this -> enclosure = $this -> getOption('enclosure', '"', "string");
    $this -> escape = $this -> getOption('escape', "\\", "string");
    $this -> length = $this -> getOption('length', 0, "int");
    $this -> multiple_value_delimiter = $this -> getOption('multiple_value_delimiter', '|', "string");
    self :: log_debug(
      'New LSioFormatCSV objet started with delimiter="'.$this -> delimiter.'", '.
      'enclosure = <'.$this -> enclosure.'>, escape = "'.$this -> escape.'", '.
      'length = '.$this -> length.' and multiple value delimiter = "'.
      $this -> multiple_value_delimiter.'"'
    );
  }

  /**
   * Load file
   *
   * @param[in] string $file The file path to load
   *
   * @retval boolean True if file is loaded, false otherwise
   **/
  public function loadFile($path) {
    self :: log_debug("loadFile($path)");
    $fd = fopen($path, 'r');
    if ($fd === false) {
      self :: log_error("Fail to open file '$path'.");
      return false;
    }

    $this -> rows = array();
    while (
      (
        $row = fgetcsv(
          $fd, $this -> length, $this -> delimiter,
          $this -> enclosure, $this -> escape
        )
      ) !== FALSE) {
      $this -> rows[] = $row;
    }
    if (!$this -> rows)
      return false;
    $this -> headers = array_shift($this -> rows);
    self :: log_trace("loadFile($path): headers = ".varDump($this -> headers));
    self :: log_debug("loadFile($path): ".count($this -> rows)." row(s) loaded.");
    return true;
  }

  /**
   * Check if loaded file data are valid
   *
   * @retval boolean True if loaded file data are valid, false otherwise
   **/
  public function isValid() {
    if (!is_array($this -> rows) && empty($this -> rows)) {
      self :: log_error("No data loaded from input file");
      return false;
    }

    if (!$this -> headers) {
      self :: log_error("Header line seem empty");
      return false;
    }
    for($i = 0; $i < count($this -> rows); $i++) {
      if (count($this -> rows[$i]) != count($this -> headers)) {
        self :: log_error(
          "Input row #$i contain ".count($this -> rows[$i])." field(s) when ".
          "headers has ".count($this -> headers)
        );
        return false;
      }
    }
    self :: log_debug("isValid(): all ".count($this -> rows)." row(s) are symetric.");
    return True;
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
    $objects = array();
    foreach($this -> rows as $row) {
      $object = array();
      foreach ($this -> headers as $idx => $key) {
        $values = explode($this -> multiple_value_delimiter, $row[$idx]);
        $object[$key] = (count($values) == 1?$values[0]:$values);
      }
      $objects[] = $object;
    }
    self :: log_trace("getAll(): objects = ".varDump($objects));
    return $objects;
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
    return $this -> headers;
  }


  /**
   * Export objects data
   *
   * @param[in] $stream The stream where objects's data have to be exported
   * @param[in] $objects_data Array of objects data to export
   * @param[in] $stream resource|null The output stream (optional, default: STDOUT)
   *
   * @return boolean True on succes, False otherwise
   */
  public function exportObjectsData($objects_data, $stream=null) {
    if (!function_exists('fputcsv')) {
      LSerror :: addErrorCode('LSioFormatCSV_01');
      return false;
    }

    $stdout = false;
    if (is_null($stream)) {
      $stream = fopen('php://temp/maxmemory:'. (5*1024*1024), 'w+');
      $stdout = true;
    }

    $first = true;
    foreach($objects_data as $dn => $object_data) {
      if ($first) {
        $this -> writeRow($stream, array_keys($object_data));
        $first = false;
      }
      $row = array();
      foreach($object_data as $values)
        $row[] = (is_array($values)?implode($this -> multiple_value_delimiter, $values):$values);
      $this -> writeRow($stream, $row);
    }
    if (!$stdout)
      return true;
    header("Content-disposition: attachment; filename=export.csv");
    header("Content-type: text/csv");
    rewind($stream);
    print stream_get_contents($stream);
    @fclose($stream);
    exit();
  }

  /**
   * Write CSV row to stream
   *
   * @param[in] $stream The CSV file description reference
   * @param[in] $row An array of a CSV row fields to write
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if CSV row is successfully writed, false in other case
   */
  private function writeRow($stream, $row) {
    // Escape character could only be specified since php 5.5.4
    if (!defined('PHP_VERSION_ID') or PHP_VERSION_ID < 50504) {
      $result = fputcsv($stream, $row, $this -> delimiter, $this -> enclosure);
    }
    else {
      $result = fputcsv($stream, $row, $this -> delimiter, $this -> enclosure, $this -> escape);
    }
    return ($result !== false);
   }

}

LSerror :: defineError('LSioFormatCSV_01',
  ___("LSioFormatCSV: function fputcsv is not available.")
);
