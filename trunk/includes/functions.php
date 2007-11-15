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

function valid($obj) {
  echo 'ok';
  return true;
}

function return_data($data) {
  return $data;
}

function debug($data,$get=true) {
	if ($get) {
		if (is_array($data)) {
			$GLOBALS['LSdebug'][]=$data;
		}
		else {
			$GLOBALS['LSdebug'][]="[$data]";
		}
	}
	return true;
}

function debug_print() {
	echo "<fieldset><legend>Debug</legend><pre>";
	print_r( $GLOBALS['LSdebug']);
	echo "</pre></fieldset>";
}

?>
