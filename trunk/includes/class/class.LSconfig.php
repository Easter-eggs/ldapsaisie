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
 * Object LSconfig
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSconfig { 
  
  // Configuration Data
  private static $data=array();
  
 /**
  * Lancement de LSconfig
  * 
  * Chargement initiale des données de configuration à partir des fichiers en
  * config.*.php du dossier LS_CONF_DIR
  * 
  * @retval boolean True si tout s'est bien passé, False sinon
  **/
  public static function start() {
    if (loadDir(LS_CONF_DIR, '^config\..*\.php$')) {
      if (is_array($GLOBALS['LSconfig'])) {
        self :: $data = $GLOBALS['LSconfig'];
        self :: $data['LSaddons'] = $GLOBALS['LSaddons'];
        return true;
      }
    }
    return;
  }
  
 /**
  * Récupération d'une valeur
  * 
  * @param[in] $var string Le nom de valeur à récupérer (Exemple : cacheSearch)
  * 
  * @retval mixed La valeur de la variable, ou false si son nom n'est parsable
  **/
  public static function get($var) {
    $vars=explode('.',$var);
    if(is_array($vars)) {
      $data=self :: $data;
      foreach ($vars as $v) {
        $data=$data[$v];
      }
      return $data;
    }
    return;
  }

 /**
  * Définition d'une valeur
  * 
  * @param[in] $var string Le nom de valeur à définir
  * @param[in] $val mixed La valeur de la variable
  * 
  * @retval boolean True si tout s'est bien passé, False sinon
  **/  
  public static function set($var,$val) {
    $vars=explode('.',$var);
    if(is_array($vars)) {
      $code='self :: $data';
      foreach ($vars as $v) {
        $code.='["'.$v.'"]';
      }
      $code.='=$val;';
      return (eval($code)===NULL);
    }
    return;
  }
  
}
  

?>