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

  // File_CSV_DataSource object
  private $csv=false;

  /**
   * Load file
   *
   * @param[in] string $file The file path to load
   *
   * @retval boolean True if file is loaded, false otherwise
   **/
  public function loadFile($path) {
    $this->csv=new File_CSV_DataSource;
    if (is_array($this -> options)) {
      foreach ($this -> options as $opt_key => $opt_val) {
        if (isset($this->csv -> settings[$opt_key]))
          $this->csv -> settings[$opt_key] = $opt_val;
      }
    }
    if ($this->csv->load($path)) {
      return True;
    }
    return false;
  }

  /**
   * Check if loaded file data are valid
   *
   * @retval boolean True if loaded file data are valid, false otherwise
   **/
  public function isValid() {
    if ($this -> csv && $this -> csv -> isSymmetric()) {
      return True;
    }
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
    return $this -> csv -> connect();
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
    return $this -> csv -> getHeaders();
  }

}
