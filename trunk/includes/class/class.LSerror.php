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
 * Gestion des erreurs pour LdapSaisie
 *
 * Cette classe gère les retours d'erreurs.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSerror {

  var $errors;
  /**
   * Constructeur
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */ 
  function LSerror() {
    $errors = array();
  }
  
  /**
   * Ajoute une erreur
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $code numeric Le code de l'erreur
   * @param[in] $msg mixed Un tableau ou une chaine pour la construction du message d'erreur
   *                       Tableau : '[clef]' => 'valeur' 
   *                                    La clef sera utilisé dans le format de message d'erreur
   *                                    dans le fichier 'error_code.php'.
   *
   * @retval void
   */ 
  function addErrorCode($code=-1,$msg='') {
    $this -> errors[]=array($code,$msg);
  }
  
  /**
   * Affiche les erreurs et arrête l'execution du code
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */ 
  function stop(){
    $this -> display();
    exit(1);
  }
  
  /**
   * Affiche les erreurs
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  function display() {
    $errors = $this -> getErrors();
    if ($errors) {
      $GLOBALS['Smarty'] -> assign('LSerrors',$errors);
    }
    /*if(!empty($this -> errors)) {
      print "<h3>"._('Erreurs')."</h3>\n";
      foreach ($this -> errors as $error) {
        echo "(Code ".$error[0].") ".getFData($GLOBALS['LSerror_code'][$error[0]]['msg'],$error[1])."<br />\n";
      }
    }*/
  }

 /**
  * Retourne le texte des erreurs
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat string Le texte des erreurs
  */
  function getErrors() {
    if(!empty($this -> errors)) {
      foreach ($this -> errors as $error) {
        $txt.="(Code ".$error[0].") ".getFData($GLOBALS['LSerror_code'][$error[0]]['msg'],$error[1])."<br />\n";
      }
      return $txt;
    }
    return;
  }
  
 /**
  * Définir si il y a des erreurs
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat boolean
  */
  function errorsDefined() {
    return !empty($this -> errors);
  }
}

?>
