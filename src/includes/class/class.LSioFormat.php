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
 * Manage IOformat of LSldapObject import/export
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSioFormat extends LSlog_staticLoggerClass {

  var $config = false;
  var $driver = False;

  /**
   * Constructor
   *
   * @param[in] string $LSobject The LSobject type name
   * @param[in] string $ioFormat The ioFormat name
   *
   * @retval void
   **/
  public function __construct($LSobject, $ioFormat) {
    $conf = LSconfig::get('LSobjects.'.$LSobject.".ioFormat.".$ioFormat);
    if(is_array($conf)) {
      $this -> config = $conf;
      $driver = $this -> getConfig('driver');
      if ($driver && LSsession :: loadLSclass('LSioFormat'.$driver)) {
        $driverClass = "LSioFormat".$driver;
        $driverOptions = $this -> getConfig('driver_options', array(), 'array');
        $this -> driver = new $driverClass($driverOptions);
      }
      else {
        LSerror :: addErrorCode('LSioFormat_01', $driver);
      }
    }
  }

  /**
   * Check if ioFormat driver is ready
   *
   * @retval boolean True if ioFormat driver is ready, false otherwise
   **/
  public function ready() {
    return (is_array($this -> config) && $this -> driver !== False);
  }

  /**
   * Return a configuration parameter (or default value)
   *
   * @param[] $param	The configuration parameter
   * @param[] $default	The default value (default : null)
   * @param[] $cast	Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  public function getConfig($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, (is_array($this -> config)?$this -> config:array()));
  }

  /**
   * Load and valid file
   *
   * @param[in] string $file The file path to load
   *
   * @retval boolean True if file is loaded and valid, false otherwise
   **/
  public function loadFile($file) {
    if ($this -> driver -> loadFile($file)) {
      return $this -> driver -> isValid();
    }
    return False;
  }

  /**
   * Retreive all objects contained by the loaded file
   *
   * @retval array The objects contained by the loaded file
   **/
  public function getAll() {
    return $this -> driver -> getAllFormated(
      $this -> getConfig('fields', array(), 'array'),
      $this -> getConfig('generated_fields', array(), 'array')
    );
  }

  /**
   * Export objects
   *
   * @param  $objects array of LSldapObject The objects to export
   *
   * @return boolean True on succes, False otherwise
   */
  public function exportObjects(&$objects) {
    self :: log_trace('exportObjects(): start');
    $fields = $this -> getConfig('fields');
    if (!$fields) {
      self :: log_error('exportObjects(): No field configured !');
      return false;
    }
    if (!LSsession :: loadLSclass('LSform', null, true))
      return false;

    $objects_data = array();
    foreach($objects as $object) {
      $objects_data[$object -> getDn()] = array();

      // Build a LSform object
      $export = new LSform($object, 'export');

      // Add attributes to export and put their values to data to export
      foreach($fields as $key => $attr_name) {
        $object -> attrs[$attr_name] -> addToExport($export);
        $objects_data[$object -> getDn()][$key] = $export -> elements[$attr_name] -> getApiValue();
      }
    }
    self :: log_trace('exportObjects(): objects data = '.varDump($objects_data));
    return $this -> driver -> exportObjectsData($objects_data);
  }

}

LSerror :: defineError('LSioFormat_01',
___("LSioFormat : IOformat driver %{driver} invalid or unavailable.")
);
