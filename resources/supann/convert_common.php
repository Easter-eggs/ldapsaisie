<?php

function load_csv_file($csv_file, $delimiter=';', $enclosure='"', $length=0, $escape="\\") {
  echo "Load CSV file '$csv_file'... ";
  $fd = fopen($csv_file, 'r');
  $headers = array();
  $rows = array();
  while (($raw_row = fgetcsv($fd, $length, $delimiter, $enclosure, $escape)) !== FALSE) {
    if (!$headers) {
      $headers = array();
      foreach($raw_row as $idx => $key) {
        $headers[$idx] = trim($key);
      }
      continue;
    }
    $row = array();
    foreach($headers as $idx => $key) {
      if (!$key) continue;
      $row[$key] = (isset($raw_row[$idx])?$raw_row[$idx]:null);
    }
    $rows[] = $row;
  }
  fclose($fd);
  echo "done.\n";
  if (!$rows)
    die("CSV file is empty ?\n");
  echo count($rows)." loaded from CSV file.\n";
  return $rows;
}

function mb_ucfirst($str) {
  $fc = mb_strtoupper(mb_substr($str, 0, 1));
  return $fc.mb_substr($str, 1);
}

function export_nomenclature($array, $fd, $prefix="", $fix_encoding=false) {
  fwrite($fd, $prefix."array (\n");
  ksort($array);
  foreach ($array as $key => $value) {
    if ($fix_encoding)
      $key = iconv($fix_encoding[0], $fix_encoding[1], $key);
    fwrite($fd, $prefix."  '".str_replace("'", "\\'", strval($key))."' => ");
    if (is_array($value)) {
      export_nomenclature($value, $fd, $prefix."  ", $fix_encoding);
      fwrite($fd, ",\n");
    }
    else {
      $value = var_export($value, true);
      if ($fix_encoding)
        $value = iconv($fix_encoding[0], $fix_encoding[1], $value);
      fwrite($fd, "$value,\n");
    }
  }
  fwrite($fd, $prefix.");\n");
}
