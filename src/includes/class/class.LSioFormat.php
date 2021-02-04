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
 * Manage IOformat of LSldapObject import/export
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSioFormat {

  var $config=False;
  var $driver=False;

  /**
   * Constructor
   *
   * @param[in] string $LSobject The LSobject type name
   * @param[in] string $ioFormat The ioFormat name
   *
   * @retval void
   **/
  public function __construct($LSobject, $ioFormat) {
    $conf=LSconfig::get('LSobjects.'.$LSobject.".ioFormat.".$ioFormat);
    if(is_array($conf)) {
      $this -> config=$conf;
      if (isset($this -> config['driver']) && LSsession :: loadLSclass('LSioFormat'.$this -> config['driver'])) {
        $driverClass='LSioFormat'.$this -> config['driver'];
        $driverOptions=array();
        if (isset($this -> config['driver_options'])) $driverOptions = $this -> config['driver_options'];
        $this -> driver = new $driverClass($driverOptions);
      }
      else {
        LSerror :: addErrorCode('LSioFormat_01',$this -> config['driver']);
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
    return $this -> driver -> getAllFormated($this -> config['fields'],$this -> config['generated_fields']);
  }

}

LSerror :: defineError('LSioFormat_01',
___("LSioFormat : IOformat driver %{driver} invalid or unavailable.")
);
