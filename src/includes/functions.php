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

/**
 * Construction d'une chaine formatée
 *
 * Cette fonction retourne la valeur d'une chaine formatée selon le format
 * et les données passés en paramètre.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $format string Format de la chaine
 * @param[in] $data mixed Les données pour composés la chaine
 *                    Ce paramètre peut être un tableau de string, une string,
 *                    une tableau d'objet ou un objet.
 * @param[in] $meth string Le nom de la methode de/des objet(s) à appeler pour
 *                         obtenir la valeur de remplacement dans la chaine formatée.
 *
 * @retval string La chaine formatée
 */
function getFData($format,$data,$meth=NULL) {
  $unique=false;
  /*
   * Format : %{[key name][:A][:B][! ou _][~][%}}
   *
   * Extracted fields
   * - 0 : full string '%{...}'
   * - 1 : key name
   * - 2 : :A
   * - 3 : A
   * - 4 : :B
   * - 5 : B
   * - 6 : "!" / "_" / "~" / "%"
   */
  $expr="/%[{(]([A-Za-z0-9]+)(\:(-?[0-9])+)?(\:(-?[0-9]+))?([\!\_~%]*)[})]/";
  if(!is_array($format)) {
    $format=array($format);
    $unique=true;
  }
  for($i=0;$i<count($format);$i++) {
    if(is_array($data)) {
      if ($meth==NULL) {
        while (preg_match($expr,$format[$i],$ch)) {
          if (!isset($data[$ch[1]])) {
            $val = '';
          }
          elseif (is_array($data[$ch[1]])) {
            $val = $data[$ch[1]][0];
          }
          else {
            $val = $data[$ch[1]];
          }
	  $val=_getFData_extractAndModify($val,$ch);
          $format[$i]=str_replace($ch[0],$val,$format[$i]);
        }
      }
      else {
        while (preg_match($expr,$format[$i],$ch)) {
          if (method_exists($data[$ch[1]],$meth)) {
            $value = $data[$ch[1]] -> $meth();
            if (is_array($value)) {
              $value = $value[0];
            }
	    $value=_getFData_extractAndModify($value,$ch);
            $format[$i]=str_replace($ch[0],$value,$format[$i]);
          }
          else {
            LSerror :: addErrorCode('fct_getFData_01',array('meth' => $meth,'obj' => $ch[1]));
            break;
          }
        }
      }
    }
    elseif (is_object($data)) {
      if ($meth==NULL) {
        while (preg_match($expr,$format[$i],$ch)) {
          $value = $data -> $ch[1];
          if (is_array($value)) {
            $value = $value[0];
          }
          $value=_getFData_extractAndModify($value,$ch);
          $format[$i]=str_replace($ch[0],$value,$format[$i]);
        }
      }
      else {
        while (preg_match($expr,$format[$i],$ch)) {
          if (method_exists($data,$meth)) {
            $value = $data -> $meth($ch[1]);
            if (is_array($value)) {
              $value = $value[0];
            }
	          $value=_getFData_extractAndModify($value,$ch);
            $format[$i]=str_replace($ch[0],$value,$format[$i]);
          }
          else {
            LSerror :: addErrorCode(0,getFData(_("Function 'getFData' : The method %{meth} of the object %{obj} doesn't exist."),array('meth' => $meth,'obj' => get_class($data))));
            break;
          }
        }
      }
    }
    else {
      while (preg_match($expr,$format[$i],$ch)) {
	      $val=_getFData_extractAndModify($data,$ch);
        $format[$i]=str_replace($ch[0],$val,$format[$i]);
      }
    }
  }
  if($unique) {
    return $format[0];
  }
  return $format;
}

function _getFData_extractAndModify($data,$ch) {
  /*
   * Format : %{[key name][:A][:B][-][! ou _][~][%}}
   *
   * Extracted fields
   * - 0 : full string '%{...}'
   * - 1 : key name
   * - 2 : :A
   * - 3 : A
   * - 4 : :B
   * - 5 : B
   * - 6 : "!" / "_" / "~" / "%"
   */
  // If A
  if($ch[3]!="") {
    // If A and B
    if ($ch[5]!="") {
      // If A and B=0
      if ($ch[5]==0) {
        // If A<0 and B=0
        if ($ch[3]<0) {
          $s=strlen((string)$data)-(-1*$ch[3]);
          $l=strlen((string)$data);
        }
        // If A >= 0 and B
        else {
          $s=$ch[3];
          $l=strlen((string)$data);
        }
      }
      // If A and B > 0
      elseif ($ch[5]>0) {
        // If A < 0 and B > 0 or A >= 0 and B > 0
        $s=$ch[3];
        $l=$ch[5];
      }
      // If A and B < 0
      else {
        // If A < 0 and B < 0
        if ($ch[3]<0) {
          $s=$ch[5];
          $l=false;
        }
        // If A >= 0 and B < 0
        else {
          $s=$ch[3]+$ch[5];
          $l=abs($ch[5]);
        }
      }
    }
    // If only A
    else {
      if ($ch[3]<0) {
        $s=$ch[3];
        $l=false;
      }
      else {
        $s=0;
        $l=$ch[3];
      }
    }

    if ($l==false) {
      $val=mb_substr((string)$data,$s);
    }
    else {
      $val=mb_substr((string)$data,$s, abs($l));
    }
  }
  else {
    try {
      $val=strval($data);
    }
    catch (Exception $e) {
      $val=_('[not string value]');
    }
  }

  if ($ch[6]) {
    # Without Accent
    if (strpos($ch[6], '~')!==false) {
      $val = withoutAccents($val);
    }

    # Upper / Lower case
    if (strpos($ch[6], '!')!==false) {
      $val=mb_strtoupper($val);
    }
    elseif (strpos($ch[6], '_')!==false) {
      $val=mb_strtolower($val);
    }

    # Escape HTML entities
    if (strpos($ch[6], '%')!==false) {
      $val = htmlentities($val);
    }
  }

  return $val;
}

function getFieldInFormat($format) {
  $fields=array();
  $expr='/%[{(]([A-Za-z0-9]+)(\:(-?[0-9])+)?(\:(-?[0-9]+))?(-)?(\!|\_)?(~)?(%)?[})]/';
  while (preg_match($expr,$format,$ch)) {
    $fields[]=$ch[1];
    $format=str_replace($ch[0],'',$format);
  }
  return $fields;
}

function loadDir($dir,$regexpr='/^.*\.php$/') {
  if ($handle = opendir($dir)) {
    while (false !== ($file = readdir($handle))) {
      if (preg_match($regexpr,$file)) {
        require_once($dir.'/'.$file);
      }
    }
  }
  else {
    die(_('Folder not found').' : '.$dir);
  }
  return true;
}


function valid($obj) {
  LSdebug('function valid() : ok');
  return true;
}

function validPas($obj=null) {
  LSdebug('function valid() : nok');
  return false;
}

function return_data($data) {
  return $data;
}

function varDump($data) {
  ob_start();
  var_dump($data);
  $data=ob_get_contents();
  ob_end_clean();
  return $data;
}

$GLOBALS['LSdebug_fields']=array();
function LSdebug($data,$dump=false) {
  if ($dump) {
    $data=varDump($data);
  }
  if (class_exists('LSlog'))
    LSlog :: debug($data);

  if (!is_array($data) && !is_object($data)) {
    $data="[$data]";
  }
  $GLOBALS['LSdebug_fields'][]=$data;
  return true;
}

function LSdebug_print($return=false,$ul=true) {
  if (( $GLOBALS['LSdebug_fields'] ) && (LSdebug)) {
    if ($ul) $txt='<ul>'; else $txt="";
    foreach($GLOBALS['LSdebug_fields'] as $debug) {
      if (is_array($debug)||is_object($debug)) {
        $txt.='<li><pre>'.htmlentities(print_r($debug,true)).'</pre></li>';
      }
      else {
        $txt.='<li><pre>'.htmlentities(strval($debug)).'</pre></li>';
      }
    }
    if ($ul) $txt.='</ul>';
    LStemplate :: assign('LSdebug',$txt);
    if ($return) {
      return $txt;
    }
  }
  return;
}

function LSdebugDefined() {
  if (!LSdebug)
    return;
  return (!empty($GLOBALS['LSdebug_fields']));
}

  /**
   * Vérifie la compatibilite des DN
   *
   * Vérifie que les DNs sont dans la même branche de l'annuaire.
   *
   * @param[in] $dn Un premier DN.
   * @param[in] $dn Un deuxième DN.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si les DN sont compatibles, false sinon.
   */
  function isCompatibleDNs($dn1,$dn2) {
    $infos1=ldap_explode_dn($dn1,0);
    if(!$infos1)
      return;
    $infos2=ldap_explode_dn($dn2,0);
    if(!$infos2)
      return;
    if($infos2['count']>$infos1['count']) {
      $tmp=$infos1;
      $infos1=$infos2;
      $infos2=$tmp;
    }
    $infos1=array_reverse($infos1);
    $infos2=array_reverse($infos2);

    for($i=0;$i<$infos1['count'];$i++) {
      if (!isset($infos2[$i])) continue;
      if($infos1[$i]==$infos2[$i]) continue;
      return false;
    }
    return true;
  }

  /**
   * Fait la somme de DN
   *
   * Retourne un DN qui correspond au point de séparation des DN si les DN
   * ne sont pas dans la meme dans la meme branche ou le dn le plus long sinon.
   *
   * @param[in] $dn Un premier DN.
   * @param[in] $dn Un deuxième DN.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Un DN (ou false si les DN ne sont pas valide)
   */
  function sumDn($dn1,$dn2) {
    $infos1=ldap_explode_dn($dn1,0);
    if(!$infos1)
      return;
    $infos2=ldap_explode_dn($dn2,0);
    if(!$infos2)
      return;
    if($infos2['count']>$infos1['count']) {
      $tmp=$infos1;
      $infos1=$infos2;
      $infos2=$tmp;
    }
    $infos1=array_reverse($infos1);
    $infos2=array_reverse($infos2);

    $first=true;
    $basedn='';
    for($i=0;$i<$infos1['count'];$i++) {
      if(($infos1[$i]==$infos2[$i])||(!isset($infos2[$i]))) {
        if($first) {
          $basedn=$infos1[$i];
          $first=false;
        }
        else
          $basedn=$infos1[$i].','.$basedn;
      }
      else {
        return $basedn;
      }
    }
    return $basedn;
  }

  function checkEmail($value,$domain=NULL,$checkDns=true) {
    $log = LSlog :: get_logger('checkEmail');
    $regex = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';

    if (!preg_match($regex, $value)) {
      $log -> debug("'$value': regex fail");
      return false;
    }

    $nd = explode('@', $value);
    $nd=$nd[1];

    if ($domain) {
      if(is_array($domain)) {
        if (!in_array($nd,$domain)) {
          $log -> debug("'$value': domain '$nd' not authorized. Allowed domains: '".implode("', '", $domain)."'");
          return false;
        }
      }
      else {
        if($nd!=$domain) {
          $log -> debug("'$value': domain '$nd' not authorized. Allowed domains: '$domain'");
          return false;
        }
      }
    }

    if ($checkDns && function_exists('checkdnsrr')) {
      if (!(checkdnsrr($nd, 'MX') || checkdnsrr($nd, 'A'))) {
        $log -> debug("'$value': DNS check fail");
        return false;
      }
    }

    $log -> debug("'$value': validated");
    return true;
  }

  function generatePassword($chars=NULL,$lenght=NULL) {
    if (!$lenght) {
        $lenght=8;
    }
    if (is_array($chars)) {
      $retval='';
      foreach($chars as $chs) {
        if (!is_array($chs)) {
          $chs=array('chars' => $chs);
        }
        if (!isset($chs['nb']) || !is_int($chs['nb'])) {
          $chs['nb']=1;
        }
        $retval.=aleaChar($chs['chars'],$chs['nb']);
      }
      $add = ($lenght-strlen($retval));
      if ($add > 0) {
        $retval .= aleaChar($chars,$add);
      }
      return str_shuffle($retval);
    } else {
      return aleaChar($chars,$lenght);
    }
  }

  function aleaChar($chars=NULL,$lenght=1) {
    if (is_array($chars)) {
      $nchars="";
      foreach($chars as $chs) {
        if (is_string($chs)) {
          $nchars.=$chs;
        }
        else if (is_string($chs['chars'])) {
          $nchars.=$chs['chars'];
        }
      }
      if(is_string($chars) && strlen($chars)>0) {
        $chars=$nchars;
      }
      else {
        $chars=NULL;
      }
    }
    if (!$chars) {
      $chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
    }
    $nbChars=strlen($chars);
    $retval="";
    if(is_int($lenght)) {
      for ($i=0;$i<$lenght;$i++) {
        $retval.=$chars[rand(0,$nbChars-1)];
      }
    }
    return $retval;
  }

  function compareDn($a,$b) {
    if (substr_count($a,',') > substr_count($b,','))
      return -1;
    else
      return 1;
  }

  /**
   * Translate message by using LSlang or Gettext methods
   *
   * @param[in] @msg string The message
   *
   * @retval string The translated message if translation available, the original message otherwise
   **/
  function __($msg) {
    if (empty($msg)) return $msg;
    if (isset($GLOBALS['LSlang'][$msg])) {
      return $GLOBALS['LSlang'][$msg];
    }
    return _($msg);
  }

  /**
   * Non-translate message
   *
   * Just-return the input message. This function permit the detection of message
   * that will be translated only at display time and not at declare time.
   *
   * @param[in] @msg string The message
   *
   * @retval string The message (unchanged)
   **/
  function ___($msg) {
    return $msg;
  }

 /**
  * Supprime les accents d'une chaine
  *
  * @param[in] $string La chaine originale
  *
  * @retval string La chaine sans les accents
  */
  function withoutAccents($string){
    $replaceAccent = Array(
      "à" => "a",
      "á" => "a",
      "â" => "a",
      "ã" => "a",
      "ä" => "a",
      "ç" => "c",
      "è" => "e",
      "é" => "e",
      "ê" => "e",
      "ë" => "e",
      "ì" => "i",
      "í" => "i",
      "î" => "i",
      "ï" => "i",
      "ñ" => "n",
      "ò" => "o",
      "ó" => "o",
      "ô" => "o",
      "õ" => "o",
      "ö" => "o",
      "ù" => "u",
      "ú" => "u",
      "û" => "u",
      "ü" => "u",
      "ý" => "y",
      "ÿ" => "y",
      "À" => "A",
      "Á" => "A",
      "Â" => "A",
      "Ã" => "A",
      "Ä" => "A",
      "Ç" => "C",
      "È" => "E",
      "É" => "E",
      "Ê" => "E",
      "Ë" => "E",
      "Ì" => "I",
      "Í" => "I",
      "Î" => "I",
      "Ï" => "I",
      "Ñ" => "N",
      "Ò" => "O",
      "Ó" => "O",
      "Ô" => "O",
      "Õ" => "O",
      "Ö" => "O",
      "Ù" => "U",
      "Ú" => "U",
      "Û" => "U",
      "Ü" => "U",
      "Ý" => "Y"
    );
    return strtr($string, $replaceAccent);
  }


 /**
  * Supprime les espaces d'une chaine en les remplacant par un motif ou non
  *
  * @param[in] $str La chaine originale
  * @param[in] $to Le motif de remplacement. S'il n'est pas spécifié, les espaces seront simplement supprimés
  *
  * @retval string La chaine sans les espaces
  **/
  function replaceSpaces($str,$to='') {
    return strtr($str,array (
               ' ' => $to,
               "\t" => $to
             )
           );
  }

 /**
  * List files in a directory corresponding to a regex
  *
  * @param[in] $dir The path of the directory
  * @param[in] $regex The regex apply on filename
  *
  * @retval array() List of file name
  **/
  function listFiles($dir,$regex) {
    $retval=array();
    if (is_dir($dir)) {
      $d = dir($dir);
      while (false !== ($file = $d->read())) {
        if (is_file("$dir/$file")) {
          if (preg_match($regex, $file, $m)) {
            $retval[]=((is_array($m) && count($m)>1)?$m:$file);
          }
        }
      }
    }
    return $retval;
  }

 /**
  * Return current date in LDAP format
  *
  * @param[in] $mixed Anything (to permit using as generated function)
  *
  * @retval string The current date in LDAP format (YYYYMMDDHHMMSSZ)
  **/
  function now($mixed=Null) {
    return date ('YmdHis').'Z';
  }


 /**
  * Format callable name
  *
  * @param[in] $callable The callable
  *
  * @retval string The formated callable name
  **/
  function getCallableName($callable) {
    if (is_string($callable)) {
      return $callable;
    }
    elseif(is_array($callable) && count($callable)==2) {
      if (is_string($callable[0])) {
        return $callable[0].'::'.$callable[1].'()';
      }
      elseif(is_object($callable[0])) {
        return "object ".get_class($callable[0])."->".$callable[1].'()';
      }
    }
    return "unknown : ".(string)$callable;
  }

/**
 * Check if a path is absolute
 *
 * @param[in] $path string The path
 *
 * @retval boolean True if path is absolute, False otherwise
 */
function isAbsolutePath($path) {
  return strStartWith($path, '/') || strStartWith($path, './') || strStartWith($path, '../');
}

/**
 * Check if a string start with another specified string
 *
 * @param[in] $string string The string to search in
 * @param[in] $start_string string The starting string to check
 *
 * @retval boolean True if string start by specified one, False otherwise
 */
function strStartWith($string, $start_string) {
  if (strlen($start_string) > strlen($string))
    return false;
  return substr($string, 0, strlen($start_string)) === $start_string;
}

/**
 * Dump file content
 *
 * @param[in] $file_path string The file path to dump
 * @param[in] $mime_type string|null The MIME type return as Content-type (optional, default: auto-detected)
 * @param[in] $max_age integer The cache max_age value, as return in Cache-Control HTTP header
 *                             (optional, default: 3600)
 * @param[in] $force_download boolean Set to true to force download (optional, default: false)
 * @param[in] $filename string Specific filename in case of force download (optional, default: orignal filename)
 *
 * @retval void
 **/
function dumpFile($file_path, $mime_type=null, $max_age=3600, $force_download=false, $filename=null) {
  if (is_file($file_path)) {
    header('Content-Type: '.(is_null($mime_type)?mime_content_type($file_path):$mime_type));
    if ($force_download)
      header("Content-disposition: attachment; filename=\"".($filename?$filename:basename($file_path))."\"");
    $last_modified_time = filemtime($file_path);
    $etag = md5_file($file_path);
    header("Cache-Control: max-age=$max_age, must-revalidate");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
    header("Etag: $etag");

    if (
      (
        isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
        @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time
      )
      ||
      (
        isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
        trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag
      )
    ) {
            header("HTTP/1.1 304 Not Modified");
            exit();
    }

    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit();
  }

  // File not found, Trigger error 404 (via LSurl if defined)
  if (class_exists('LSurl')) {
    LSurl :: error_404();
  }
  else {
    header("HTTP/1.1 404 Not found");
    exit();
  }
}

/**
 * Format a callable object for logging
 * @param  callable $callable The callable object
 * @return string The callable object string representation
 */
function format_callable($callable) {
        if (is_array($callable))
                if (is_string($callable[0]))
                        return $callable[0]."::".$callable[1]."()";
                elseif (is_object($callable[0]))
                        return get_class($callable[0])."->".$callable[1]."()";
                else
                        return "Unkown->".$callable[1]."()";
        else
                return $callable."()";
}

function is_empty($val) {
  switch(gettype($val)) {
    case "boolean":
    case "integer":
    case "double":
    case "object":
    case "resource":
            return False;
    case "array":
    case "string":
      if ($val == "0") return false;
      return empty($val);
    case "NULL":
      return True;
  }
  return empty($val);
}

function ensureIsArray($value) {
  if (is_array($value))
    return $value;
  if (is_empty($value))
    return array();
  return array($value);
}

function ldapDate2DateTime($value, $naive=False, $format=null) {
  if (is_null($format))
    $format = ($naive?'YmdHis*':'YmdHisO');
  $datetime = date_create_from_format($format, $value);
  if ($datetime instanceof DateTime)
    return $datetime;
  return False;
}

function ldapDate2Timestamp($value, $naive=False, $format=null) {
  $datetime = ldapDate2DateTime($value, $naive, $format);
  if ($datetime instanceof DateTime)
    return $datetime -> format('U');
  return False;
}

function dateTime2LdapDate($datetime, $timezone=null, $format=null) {
  if ($timezone != 'naive' && $timezone != 'local') {
    $datetime -> setTimezone(timezone_open(is_null($timezone)?'UTC':$timezone));
  }
  if (is_null($format))
    $format = ($naive?'YmdHis':'YmdHisO');
  $datetime_string = $datetime -> format($format);

  // Replace +0000 or -0000 end by Z
  $datetime_string = preg_replace('/[\+\-]0000$/', 'Z', $datetime_string);

  return $datetime_string;
}

function timestamp2LdapDate($value, $timezone=null, $format=null) {
  $datetime = date_create("@$value");
  if ($datetime instanceof DateTime)
    return dateTime2LdapDate($datetime, $timezone, $format);
  return false;
}
