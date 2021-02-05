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
LSsession::loadLSclass('LSioFormat');

/**
 * Manage export LSldapObject
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSexport extends LSlog_staticLoggerClass {

  /**
   * Export objects
   *
   * @param[in] $LSobject LSldapObject An instance of the object type
   * @param[in] $ioFormat string The LSioFormat name
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True on success, False otherwise
   */
  public static function export($object, $ioFormat) {
    // Load LSobject
    if (is_string($object)) {
      if (!LSsession::loadLSobject($object, true)) {  // Load with warning
        return false;
      }
      $object = new $object();
    }

    // Validate ioFormat
    if(!$object -> isValidIOformat($ioFormat)) {
      LSerror :: addErrorCode('LSexport_01', $ioFormat);
      return false;
    }

    // Create LSioFormat object
    $ioFormat = new LSioFormat($object -> type, $ioFormat);
    if (!$ioFormat -> ready()) {
      LSerror :: addErrorCode('LSexport_02');
      return false;
    }

    // Load LSsearch class (with warning)
    if (!LSsession :: loadLSclass('LSsearch', null, true)) {
      return false;
    }

    // Search objects
    $search = new LSsearch($object -> type, 'LSexport');
    $search -> run();

    // Retreive objets
    $objects = $search -> listObjects();
    if (!is_array($objects)) {
      LSerror :: addErrorCode('LSexport_03');
      return false;
    }
    self :: log_debug(count($objects)." object(s) found to export");

    // Export objects using LSioFormat object
    if (!$ioFormat -> exportObjects($objects)) {
      LSerror :: addErrorCode('LSexport_04');
      return false;
    }
    self :: log_debug("export(): objects exported");
    return true;
  }

}
LSerror :: defineError('LSexport_01',
___("LSexport: input/output format %{format} invalid.")
);
LSerror :: defineError('LSexport_02',
___("LSexport: Fail to initialize input/output driver.")
);
LSerror :: defineError('LSexport_03',
___("LSexport: Fail to load objects's data to export from LDAP directory.")
);
LSerror :: defineError('LSexport_04',
___("LSexport: Fail to export objects's data.")
);
