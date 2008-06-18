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
  if(!is_array($format)) {
    $format=array($format);
    $unique=true;
  }
  for($i=0;$i<count($format);$i++) {
    if(is_array($data)) {
      if ($meth==NULL) {
        while (ereg("%{([A-Za-z0-9]+)}",$format[$i],$ch)) {
          $format[$i]=ereg_replace($ch[0],$data[$ch[1]],$format[$i]);
        }
      }
      else {
        while (ereg("%{([A-Za-z0-9]+)}",$format[$i],$ch)) {
          if (method_exists($data[$ch[1]],$meth)) {
            $format[$i]=ereg_replace($ch[0],$data[$ch[1]] -> $meth(),$format[$i]);
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(901,array('meth' => $meth,'obj' => $ch[1]));
            break;
          }
        }
      }
    }
    else {
      if ($meth==NULL) {
        while (ereg("%{([A-Za-z0-9]+)}",$format[$i],$ch))
          $format[$i]=ereg_replace($ch[0],$data,$format[$i]);
      }
      else {
        while (ereg("%{([A-Za-z0-9]+)}",$format[$i],$ch)) {
          if (method_exists($data,$meth)) {
            $format[$i]=ereg_replace($ch[0],$data -> $meth($ch[1]),$format[$i]);
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(901,array('meth' => $meth,'obj' => get_class($data)));
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

function loadDir($dir,$regexpr='^.*\.php$') {
  if ($handle = opendir($dir)) {
    while (false !== ($file = readdir($handle))) {
      if (ereg($regexpr,$file)) {
        require_once($dir.'/'.$file);
      }
    }
  }
  else {
    die(_('Dossier introuvable ('.$dir.').'));
  }
  return true;
}


function valid($obj) {
  debug('Validation : ok');
  return true;
}

function return_data($data) {
  return $data;
}

function debug($data,$get=true) {
  if ($get) {
    if (is_array($data)||is_object($data)) {
      $GLOBALS['LSdebug']['fields'][]=$data;
    }
    else {
      $GLOBALS['LSdebug']['fields'][]="[$data]";
    }
  }
  return true;
}

function debug_print($return=false) {
  if (( $GLOBALS['LSdebug']['fields'] ) && ( $GLOBALS['LSdebug']['active'] )) {
    $txt='<ul>';
    foreach($GLOBALS['LSdebug']['fields'] as $debug) {
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
  
  function checkEmail($value,$checkDns=true) {
    $regex = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';

    if (!preg_match($regex, $value)) {
      debug('checkEmail : regex fail');
      return false;
    }

    if ($checkDns && function_exists('checkdnsrr')) {
      $tokens = explode('@', $value);
      if (!(checkdnsrr($tokens[1], 'MX') || checkdnsrr($tokens[1], 'A'))) {
        debug('checkEmail : DNS fail');
        return false;
      }
    }

    return true;
  }
  
  function generatePassword($chars=NULL,$lenght=NULL) {
    if (!$chars) {
      $chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
    }
    $nbChars=strlen($chars);
    
    if (!$lenght) {
      $lenght=8;
    }
    $retVal='';
    for($i=0;$i<$lenght;$i++){
      $retVal.=$chars[rand(0,$nbChars-1)];
    }
    return $retVal;
  }
  
  function compareDn($a,$b) {
    if (substr_count($a,',') > substr_count($b,','))
      return -1;
    else 
      return 1;
  }

?>
