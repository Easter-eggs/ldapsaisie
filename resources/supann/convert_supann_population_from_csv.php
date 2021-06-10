<?php
/*******************************************************************************
 * Copyright (C) 2021 Easter-eggs
 * https://ldapsaisie.org
 *
 * Script de convertion d'un export CSV des catégories de populations fournies
 * par SUPANN au format XLSX vers un fichier PHP utilisé comme source d'infor-
 * mations par LdapSaisie.
 *
 * Le fichier des catégories de populations fourni par SUPANN est récupérable à
 * l'adressse suivante :
 *
 * https://services.renater.fr/documentation/supann/supann2020/recommandations2020/tables_references/population
 *
 * Un exemple de fichier CSV a été stocké avec ce script pour afin d'avoir un
 * modèle, en plus du fichier XLSX d'origine.
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
/*

*/

require(realpath(dirname($argv['0']))."/convert_common.php");
$csv_file = realpath(dirname(__FILE__)).'/supann_population.csv';
$output = null;
if (count($argv) >= 2)
  $csv_file = $argv[1];
if (count($argv) >= 3)
  $output = $argv[2];
if (!is_file($csv_file))
  die("CSV file '$csv_file' not found.\n");

if ($output) {
  if(is_file($output)) die("Output file '$output' already exists.\n");
}
else
  echo "No output file specified: export on STDOUT\n";

$rows = load_csv_file($csv_file);

$affectations_mapping = array(
  'Stu' => 'student',
  'Fac' => 'faculty',
  'Emp' => 'employee',
  'Aff' => 'affiliate',
  'Alum' => 'alum',
  'Res' => 'researcher',
  'Ret' => 'retired',
  'Eme' => 'emeritus',
  'Mem' => 'member',
  'Staff' => 'staff',
  'Tea' => 'teacher',
  'RR' => 'registered-reader',
  'LWI' => 'librery-walk-in',
);

echo "Handle CVS file rows... ";
$populations = array();
$population_found = 0;
foreach($rows as $row) {
  if (!$row['ID1'] || !$row['Libellé']) continue;
  $ID1 = trim($row['ID1']);
  $ID2 = trim($row['ID2']);
  $ID3 = trim($row['ID3']);
  $ID4 = trim($row['ID4']);
  $ID5 = trim($row['ID5']);
  $infos = array(
    'label' => $row['Libellé'],
    'affiliations' => array(),
    'definition' => $row['Définition'],
    'poids' => ($row['Poids']?intval($row['Poids']):null),
  );
  if ($infos['label'])
    $population_found++;

  foreach ($affectations_mapping as $key => $aff)
    if ($row[$key])
      $infos['affiliations'][] = $aff;

  if (!array_key_exists($ID1, $populations))
    $populations[$ID1] = array('subpopulations' => array());
  if ($ID2) {
    if (!array_key_exists($ID2, $populations[$ID1]['subpopulations']))
      $populations[$ID1]['subpopulations'][$ID2] = array('subpopulations' => array());
    if ($ID3) {
      if (!array_key_exists($ID3, $populations[$ID1]['subpopulations'][$ID2]['subpopulations']))
        $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3] = array('subpopulations' => array());
      if ($ID4) {
        if (!array_key_exists($ID4, $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3]['subpopulations']))
          $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3]['subpopulations'][$ID4] = array('subpopulations' => array());
        if ($ID5) {
          // Pas de sous-niveaux: on ajoute la clé subpopulations pour l'uniformité
          $infos['subpopulations'] = array();
          if (array_key_exists($ID5, $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3]['subpopulations'][$ID4]['subpopulations']))
            echo "WARNING: Duplicate key $ID1$ID2$ID3$ID4$ID5: ".print_r($populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3]['subpopulations'][$ID4]['subpopulations'][$ID5], 1). " / ".print_r($infos, 1)."\n\n";
          $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3]['subpopulations'][$ID4]['subpopulations'][$ID5] = $infos;
        }
        else {
          $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3]['subpopulations'][$ID4] = array_merge(
            $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3]['subpopulations'][$ID4],
            $infos
          );
        }
      }
      else {
        $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3] = array_merge(
          $populations[$ID1]['subpopulations'][$ID2]['subpopulations'][$ID3],
          $infos
        );
      }
    }
    else {
      $populations[$ID1]['subpopulations'][$ID2] = array_merge(
        $populations[$ID1]['subpopulations'][$row['ID2']],
        $infos
      );
    }
  }
  else {
    $populations[$ID1] = array_merge($populations[$ID1], $infos);
  }
}
echo "done.\n$population_found population(s) found.\n";

if ($output) {
  $ofd = fopen($output, 'w') or die("Fail to open output file '$output'.\n");
}
else {
  $ofd = STDOUT;
}

$stats = stat($csv_file);

fwrite($ofd, "<?php

/*
 *******************************************************************************************************************
 * Ce fichier a été généré le ".date('Y-m-d à H:i:s')." en utilisant le script ".basename($argv[0]).".
 * Son contenu est basé sur un export CSV des catégories de populations fournies par SUPANN au format XLSX
 * et datant du ".date('Y-m-d à H:i:s', $stats['mtime']).".
 *
 * Note : Le script ".basename($argv[0])." est fourni avec les sources du projet LdapSaisie dans
 * le dossier resources/supann.
 *******************************************************************************************************************
 */


\$GLOBALS['supannPopulations'] = ");
fwrite($ofd, var_export($populations, true).';');

if ($output) {
  fclose($ofd);
  echo "Populations exported in '$output' file.\n";
}
