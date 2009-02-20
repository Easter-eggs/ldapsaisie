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

class LSselect {

 /*
  * Méthode chargeant les dépendances d'affichage
  * 
  * @retval void
  */
  public static function loadDependenciesDisplay() {
    if (LSsession :: loadLSclass('LSsmoothbox')) {
      LSsmoothbox :: loadDependenciesDisplay();
    }
    LSsession :: addJSscript('LSselect.js');
    LSsession :: addCssFile('LSselect.css');
  }
  
  public static function ajax_addItem(&$data) {
    if ((isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['multiple']))) {
      if (!$_REQUEST['multiple']) {
        $_SESSION['LSselect'][$_REQUEST['objecttype']]=array($_REQUEST['objectdn']);
      }
      else if (is_array($_SESSION['LSselect'][$_REQUEST['objecttype']])) {
        if (!in_array($_REQUEST['objectdn'],$_SESSION['LSselect'][$_REQUEST['objecttype']])) {
          $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$_REQUEST['objectdn'];
        }
      }
      else {
        $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$_REQUEST['objectdn'];
      }
    }
  }
  
  public static function ajax_dropItem(&$data) {
    if ((isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn']))) {
      if (is_array($_SESSION['LSselect'][$_REQUEST['objecttype']])) {
        $result=array();
        foreach ($_SESSION['LSselect'][$_REQUEST['objecttype']] as $val) {
          if ($val!=$_REQUEST['objectdn']) {
            $result[]=$val;
          }
        }
        $_SESSION['LSselect'][$_REQUEST['objecttype']]=$result;
      }
    }
  }
  
  public static function ajax_refreshSession(&$data) {
    if ((isset($_REQUEST['objecttype'])) && (isset($_REQUEST['values'])) ) {
      $_SESSION['LSselect'][$_REQUEST['objecttype']]=array();
      $values=json_decode($_REQUEST['values'],false);
      if (is_array($values)) {
        foreach($values as $val) {
          $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$val;
        }
      }
      $data=array(
        'values' => $values
      );
    }
    else {
      LSerror :: addErrorCode('LSsession_12');
    }
  }
}

?>
