<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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

// Messages d'erreur

// Support
LSerror :: defineError('LS_EXPORTSEARCHRESULTASCSV_SUPPORT_01',
  ___("ExportSearchResultAsCSV Support : function fputcsv is not available.")
);
LSerror :: defineError('LS_EXPORTSEARCHRESULTASCSV_SUPPORT_02',
  ___("ExportSearchResultAsCSV Support : The constant %{const} is not defined..")
);

// Autres erreurs
LSerror :: defineError('LS_EXPORTSEARCHRESULTASCSV_00',
  ___("ExportSearchResultAsCSV Error : An error occured generating CSV outfile memory space.")
);
LSerror :: defineError('LS_EXPORTSEARCHRESULTASCSV_01',
  ___("ExportSearchResultAsCSV Error : An error occured executing the search.")
);
LSerror :: defineError('LS_EXPORTSEARCHRESULTASCSV_02',
  ___("ExportSearchResultAsCSV Error : An error occured writing CSV header.")
);
LSerror :: defineError('LS_EXPORTSEARCHRESULTASCSV_03',
  ___("ExportSearchResultAsCSV Error : An error occured writing a CSV row.")
);


 /**
  * Check support of exportSearchResultAsCSV
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true if exportSearchResultAsCSV is fully supported, false in other case
  */
  function LSaddon_exportSearchResultAsCSV_support() {
    $retval=true;

    // Check fputcsv function
    if (!function_exists('fputcsv')) {
      LSerror :: addErrorCode('LS_EXPORTSEARCHRESULTASCSV_SUPPORT_01');
    }

    $MUST_DEFINE_CONST= array(
      'LS_EXPORTSEARCHRESULTASCSV_DELIMITER',
      'LS_EXPORTSEARCHRESULTASCSV_ENCLOSURE',
      'LS_EXPORTSEARCHRESULTASCSV_ESCAPE_CHAR',
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( (!defined($const)) || (constant($const) == "")) {
        LSerror :: addErrorCode('LS_EXPORTSEARCHRESULTASCSV_SUPPORT_02',$const);
        $retval=false;
      }
    }

    return $retval;
  }

 /**
  * Write LSsearch result as CSV and force download of it.
  *
  * @param[in] $LSsearch The LSsearch object
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean Void if CSV file is successfully generated and upload, false in other case
  */
  function exportSearchResultAsCSV($LSsearch) {
    $csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');

    if ($csv === false) {
      LSerror :: addErrorCode('LS_EXPORTSEARCHRESULTASCSV_00');
      return false;
    }

    if (!$LSsearch -> run()) {
      LSerror :: addErrorCode('LS_EXPORTSEARCHRESULTASCSV_01');
      return false;
    }

    $headers=array($LSsearch->label_objectName, 'DN');
    if ($LSsearch->displaySubDn) $headers[]='Sub DN';
    if ($LSsearch->visibleExtraDisplayedColumns) {
      foreach ($LSsearch->visibleExtraDisplayedColumns as $cid => $conf) {
        $headers[] = __($conf['label']);
      }
    }

    if (!writeRowInCSV($csv, $headers)) {
      LSerror :: addErrorCode('LS_EXPORTSEARCHRESULTASCSV_02');
      return false;
    }


    foreach ($LSsearch -> getSearchEntries() as $e) {
      $row = array(
        $e -> displayName,
        $e -> dn
      );
      if ($LSsearch->displaySubDn) $row[] = $e -> subDn;
      if ($LSsearch->visibleExtraDisplayedColumns)
        foreach ($LSsearch->visibleExtraDisplayedColumns as $cid => $conf)
          $row[] = $e -> $cid;

      if (!writeRowInCSV($csv, $row)) {
        LSerror :: addErrorCode('LS_EXPORTSEARCHRESULTASCSV_03');
        return false;
      }
    }

    header("Content-disposition: attachment; filename=export.csv");
    header("Content-type: text/csv");
    rewind($csv);
    print stream_get_contents($csv);
    @fclose($csv);
    exit();
  }

 /**
  * Write CSV row in file
  *
  * @param[in] $csv The CSV file description reference
  * @param[in] $row An array of a CSV row fields to write
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean True if CSV row is successfully writed, false in other case
  */
  function writeRowInCSV(&$csv, &$row) {
    if (!defined('PHP_VERSION_ID') or PHP_VERSION_ID < 50504) {
      return (fputcsv($csv, $row, LS_EXPORTSEARCHRESULTASCSV_DELIMITER, LS_EXPORTSEARCHRESULTASCSV_ENCLOSURE) !== false);
    }
    return (fputcsv($csv, $row, LS_EXPORTSEARCHRESULTASCSV_DELIMITER, LS_EXPORTSEARCHRESULTASCSV_ENCLOSURE, LS_EXPORTSEARCHRESULTASCSV_ESCAPE_CHAR) !== false);
  }
