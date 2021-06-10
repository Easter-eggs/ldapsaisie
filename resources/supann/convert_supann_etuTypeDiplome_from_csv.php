<?php
/*******************************************************************************
 * Copyright (C) 2021 Easter-eggs
 * https://ldapsaisie.org
 *
 * Script de convertion d'un export CSV de la table n_type_diplome_sise de la BCN
 * vers un fichier PHP utilisé comme source de la nomenclatue etuTypeDiplome par
 * LdapSaisie.
 *
 * L'export CSV de la table est récupérable à l'adressse suivante :
 *
 * http://infocentre.pleiade.education.fr/bcn/workspace/viewTable/n/N_TYPE_DIPLOME_SISE
 *
 * Un exemple de fichier CSV a été stocké avec ce script pour afin d'avoir un
 * modèle.
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
$csv_file = realpath(dirname(__FILE__)).'/BCN_n_type_diplome_sise.csv';
$csv_file_encoding = 'ISO-8859-15';
$output = null;
$output_encoding = 'UTF-8';
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

echo "Handle CVS file rows... ";
$typesDiplome = array();
foreach($rows as $row) {
  if (!$row['TYPE_DIPLOME_SISE'] || !$row['LIBELLE_LONG']) continue;
  $typesDiplome[strval($row['TYPE_DIPLOME_SISE'])] = trim($row['LIBELLE_LONG']);
}
echo "done.\n".count($typesDiplome)." type(s) diplome found.\n";

if ($output) {
  $ofd = fopen($output, 'w') or die("Fail to open output file '$output'.\n");
}
else {
  $ofd = STDOUT;
}

$stats = stat($csv_file);

fwrite($ofd, "<?php

/*
 ***********************************************************************************************************************
 * Ce fichier a été généré le ".date('Y-m-d à H:i:s')." en utilisant le script ".basename($argv[0]).".
 * Son contenu est basé sur un export CSV de la table n_type_diplome_sise de la BCN datant du ".date('Y-m-d à H:i:s', $stats['mtime']).".
 *
 * Note : Le script ".basename($argv[0])." est fourni avec les sources du projet LdapSaisie dans
 * le dossier resources/supann.
 ***********************************************************************************************************************
 */


\$GLOBALS['BCN_TYPE_DIPLOME_SISE'] = ");
export_nomenclature($typesDiplome, $ofd, "", array($csv_file_encoding, $output_encoding));

if ($output) {
  fclose($ofd);
  echo "Types diplomes exported in '$output' file.\n";
}
