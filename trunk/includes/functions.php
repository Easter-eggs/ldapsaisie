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
 * Construction d'une chaine format�e
 *
 * Cette fonction retourne la valeur d'une chaine format�e selon le format
 * et les donn�es pass�s en param�tre.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $format string Format de la chaine
 * @param[in] $data mixed Les donn�es pour compos�s la chaine
 *                    Ce param�tre peut �tre un tableau de string, une string,
 *                    une tableau d'objet ou un objet.
 * @param[in] $meth string Le nom de la methode de/des objet(s) � appeler pour
 *                         obtenir la valeur de remplacement dans la chaine format�e.
 * 
 * @retval string La chaine format�e
 */
function getFData($format,$data,$meth=NULL) {
  if(is_array($data)) {
    if ($meth==NULL) {
      while (ereg("%{([A-Za-z0-9]+)}",$format,$ch)) {
        $format=ereg_replace($ch[0],$data[$ch[1]],$format);
      }
    }
    else {
      while (ereg("%{([A-Za-z0-9]+)}",$format,$ch)) {
        if (method_exists($data[$ch[1]],$meth)) {
          $format=ereg_replace($ch[0],$data[$ch[1]] -> $meth(),$format);
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
      while (ereg("%{([A-Za-z0-9]+)}",$format,$ch))
        $format=ereg_replace($ch[0],$data,$format);
    }
    else {
      while (ereg("%{([A-Za-z0-9]+)}",$format,$ch)) {
        if (method_exists($data,$meth)) {
          $format=ereg_replace($ch[0],$data -> $meth($ch[1]),$format);
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(901,array('meth' => $meth,'obj' => get_class($data)));
          break;
        }
      }
    }
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

?>