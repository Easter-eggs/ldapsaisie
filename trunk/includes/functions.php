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
  $expr="%{([A-Za-z0-9]+)(\:(-?[0-9])+)?(\:(-?[0-9])+)?}";
  if(!is_array($format)) {
    $format=array($format);
    $unique=true;
  }
  for($i=0;$i<count($format);$i++) {
    if(is_array($data)) {
      if ($meth==NULL) {
        while (ereg($expr,$format[$i],$ch)) {
          if (is_array($data[$ch[1]])) {
            $val = $data[$ch[1]][0];
          }
          else {
            $val = $data[$ch[1]];
          }
          if($ch[3]) {
            if ($ch[5]) {
              $s=$ch[3];
              $l=$ch[5];
            }
            else {
              $s=0;
              $l=$ch[3];
            }
            $val=substr((string)$val,$s,$l);
          }
          $format[$i]=ereg_replace($ch[0],$val,$format[$i]);
        }
      }
      else {
        while (ereg($expr,$format[$i],$ch)) {
          if (method_exists($data[$ch[1]],$meth)) {
            $value = $data[$ch[1]] -> $meth();
            if (is_array($value)) {
              $value = $value[0];
            }
            if($ch[3]) {
              if ($ch[5]) {
                $s=$ch[3];
                $l=$ch[5];
              }
              else {
                $s=0;
                $l=$ch[3];
              }
              $value=substr((string)$value,$s,$l);
            }
            $format[$i]=ereg_replace($ch[0],$value,$format[$i]);
          }
          else {
            LSerror :: addErrorCode('fct_getFData_01',array('meth' => $meth,'obj' => $ch[1]));
            break;
          }
        }
      }
    }
    else {
      if ($meth==NULL) {
        while (ereg($expr,$format[$i],$ch)) {
          if($ch[3]) {
            if ($ch[5]) {
              $s=$ch[3];
              $l=$ch[5];
            }
            else {
              $s=0;
              $l=$ch[3];
            }
            $val=substr((string)$data,$s,$l);
          }
          else {
            $val=$data;
          }
          $format[$i]=ereg_replace($ch[0],$val,$format[$i]);
        }
      }
      else {
        while (ereg($expr,$format[$i],$ch)) {
          if (method_exists($data,$meth)) {
            $value = $data -> $meth($ch[1]);
            if (is_array($value)) {
              $value = $value[0];
            }
            if($ch[3]) {
              if ($ch[5]) {
                $s=$ch[3];
                $l=$ch[5];
              }
              else {
                $s=0;
                $l=$ch[3];
              }
              $value=substr((string)$value,$s,$l);
            }
            $format[$i]=ereg_replace($ch[0],$value,$format[$i]);
          }
          else {
            LSerror :: addErrorCode(0,getFData(_("Function 'getFData' : The method %{meth} of the object %{obj} doesn't exist."),array('meth' => $meth,'obj' => get_class($data))));
            break;
          }
        }
      }
    }
  }
  if($unique) {
    return $format[0];
  }
  return $format;
}

function getFieldInFormat($format) {
  $fields=array();
  $expr="%{([A-Za-z0-9]+)(\:(-?[0-9])+)?(\:(-?[0-9])+)?}";
  while (ereg($expr,$format,$ch)) {
    $fields[]=$ch[1];
    $format=ereg_replace($ch[0],'',$format);
  }
  return $fields;
}

function loadDir($dir,$regexpr='^.*\.php$') {
  if ($handle = opendir($dir)) {
    while (false !== ($file = readdir($handle))) {
      if (ereg($regexpr,$file)) {
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

$GLOBALS['LSdebug_fields']=array();
function LSdebug($data,$dump=false) {
  if ($dump) {
    ob_start();
    var_dump($data);
    $GLOBALS['LSdebug_fields'][]=ob_get_contents(); 
    ob_end_clean();
  }
  else {
    if (is_array($data)||is_object($data)) {
      $GLOBALS['LSdebug_fields'][]=$data;
    }
    else {
      $GLOBALS['LSdebug_fields'][]="[$data]";
    }
  }
  return true;
}

function LSdebug_print($return=false) {
  if (( $GLOBALS['LSdebug_fields'] ) && (LSdebug)) {
    $txt='<ul>';
    foreach($GLOBALS['LSdebug_fields'] as $debug) {
      if (is_array($debug)||is_object($debug)) {
        $txt.='<li><pre>'.print_r($debug,true).'</pre></li>';
      }
      else {
        $txt.='<li>'.$debug.'</li>';
      }
    }
    $txt.='</ul>';
    $GLOBALS['Smarty'] -> assign('LSdebug',$txt);
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
      if(($infos1[$i]==$infos2[$i])||(!isset($infos2[$i])))
        continue;
      else
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
    $regex = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';

    if (!preg_match($regex, $value)) {
      LSdebug('checkEmail : regex fail');
      return false;
    }

    $nd = explode('@', $value);
    $nd=$nd[1];
    
    if ($domain) {
      if(is_array($domain)) {
        if (!in_array($nd,$domain)) {
          return false;
        }
      }
      else {
        if($nd!=$domain) {
          return false;
        }
      }
    }

    if ($checkDns && function_exists('checkdnsrr')) {
      if (!(checkdnsrr($nd, 'MX') || checkdnsrr($nd, 'A'))) {
        LSdebug('checkEmail : DNS fail');
        return false;
      }
    }

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
        if (!is_int($chs['nb'])) {
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
      if(strlen($chars)>0) {
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
  
  function LSlog($msg) {
    if ($GLOBALS['LSlog']['enable']) {
      global $LSlogFile;
      if (!$LSlogFile) {
        $LSlogFile=fopen($GLOBALS['LSlog']['filename'],'a');
      }
      fwrite($LSlogFile,$_SERVER['REQUEST_URI']." : ".$msg."\n");
    }
  }
  
  function __($msg) {
    if (isset($GLOBALS['LSlang'][$msg])) {
      return $GLOBALS['LSlang'][$msg];
    }
    return _($msg);
  }
  
  function tr($msg,$key=null) {
    if (is_array($msg)) {
      echo __($msg[$key]);
    }
    else {
      $val = $GLOBALS['Smarty']->get_template_vars($msg);
      if (!$val)
        $val=$msg;
      if (is_array($val)) {
        echo __($val[$key]);
      }
      else {
        echo __($val);
      }
    }
  }
?>
