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

LSsession :: loadLSclass('LSformElement_text');

/**
 * Element maildir d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments maildir des formulaires.
 * Elle étant la classe LSformElement_text.
 * 
 * Options HTML : 
 * // *************************************
 * 'html_options' => array (
 *    // Required
 *    'LSform' => array (  // To define if the user can active triggers for the LSform
 *      'create' => 1,  // Example : LSform 'create' => Triggers active by default
 *      'modify' => 0   // Example : LSform 'modify' => Triggers disable by default
 *    ),
 *    // Optional
 *    'remoteRootPathRegex' => "^\/home\/vmail\/([^\/]*)\/+$", // Regex to determine the path of
 *                                                             // maildir from the attribute value
 *    'archiveNameFormat' => "archives/%{old}" // To archive rather than destroyed :
 *                                             // At the elimination, the maildir is moved
 *                                             // rather than deleted. The new name / path
 *                                             // of the maildir is determined from the old
 *                                             // name and LSformat.
 * ),
 * // *************************************
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_maildir extends LSformElement_text {

  var $_toDo=null;

  var $JSscripts = array(
    'LSformElement_maildir_field.js',
    'LSformElement_maildir.js'
  );
  
  var $fieldTemplate = 'LSformElement_maildir_field.tpl';
  
  function getDisplay() {
    LSsession :: addHelpInfos (
      'LSformElement_maildir',
      array(
        'do' => _("Maildir creation/modification on user creation/modification is enabled. Click to disable."),
        'nodo' => _("Click to enable maildir creation/modification on user creation/modification.")
      )
    );
    return parent :: getDisplay($return);
  }
  
  /**
   * Recupère la valeur de l'élement passée en POST
   *
   * Cette méthode vérifie la présence en POST de la valeur de l'élément et la récupère
   * pour la mettre dans le tableau passer en paramètre avec en clef le nom de l'élément
   *
   * @param[] array Pointeur sur le tableau qui recupèrera la valeur.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  function getPostData(&$return) {
    // Récupère la valeur dans _POST, et les vérifie avec la fonction générale
    $retval = parent :: getPostData($return);
    
    // Si une valeur est recupérée
    if ($retval&&$_POST['LSformElement_maildir_'.$this -> name.'_do']) {
      $cur = $this -> form -> ldapObject -> attrs[$this -> name] -> getValue();
      $cur=$cur[0];
      $new = $return[$this -> name][0];
      $action=null;
      
      if ( $new != $cur ) {
        if( ($new=="") && ( $cur!="" ) ) {
          $action='delete';
        }
        else if ( ($new!="") && ( $cur!="" ) ) {
          $action='modify';
        }
        else {
          $action='create';
        }
        
        if ($action) {
          $new = $this -> attr_html -> getRemoteRootPathRegex($new);
          $cur = $this -> attr_html -> getRemoteRootPathRegex($cur);
          $this -> attr_html -> doOnModify($action,$cur,$new);
        }
      }
    }
    return $retval;
  }

}

