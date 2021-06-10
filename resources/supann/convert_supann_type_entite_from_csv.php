<?php
/*******************************************************************************
 * Copyright (C) 2021 Easter-eggs
 * https://ldapsaisie.org
 *
 * Script de convertion d'un export CSV des types d'entités fournies par SUPANN
 * au format XLS vers un fichier PHP utilisé comme nomenclature typeEntite
 * par LdapSaisie.
 *
 * Le fichier des types d'entités fourni par SUPANN est récupérable à
 * l'adressse suivante :
 *
 * https://services.renater.fr/documentation/supann/nomenclatures-proposees
 *
 * Un exemple de fichier CSV a été stocké avec ce script pour afin d'avoir un
 * modèle, en plus du fichier XLS d'origine.
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
$csv_file = realpath(dirname(__FILE__)).'/supann_type_entite.csv';
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

echo "Handle CVS file rows... ";
$type_entite = array();
foreach($rows as $row) {
  if (!$row['Code'] || !$row["Libellé du type d'entité"]) continue;
  $type_entite[$row['Code']] = mb_ucfirst(trim($row["Libellé du type d'entité"]));
}
echo "done.\n".count($type_entite)." type(s) entite(s) found.\n";


if ($output) {
  $ofd = fopen($output, 'w') or die("Fail to open output file '$output'.\n");
}
else {
  $ofd = STDOUT;
}

$stats = stat($csv_file);

fwrite($ofd, "<?php

/*
 ********************************************************************************************************
 * Ce fichier a été généré le ".date('Y-m-d à H:i:s')." en utilisant le script ".basename($argv[0]).".
 * Son contenu est basé sur un export CSV des types d'entités fournies par SUPANN au format XLS
 * et datant du ".date('Y-m-d à H:i:s', $stats['mtime']).".
 *
 * Note : Le script ".basename($argv[0])." est fourni avec les sources du projet LdapSaisie dans
 * le dossier resources/supann.
 ********************************************************************************************************
 */


\$GLOBALS['supannTypeEntite'] = ");
export_nomenclature($type_entite, $ofd, "");

if ($output) {
  fclose($ofd);
  echo "Types entites exported in '$output' file.\n";
}
