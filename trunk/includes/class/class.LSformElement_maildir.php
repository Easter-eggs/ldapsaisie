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

$GLOBALS['LSsession'] -> loadLSclass('LSformElement_text');
$GLOBALS['LSsession'] -> loadLSaddon('maildir');

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
 *      'modify' => 0   // Example : LSform 'create' => Triggers disable by default
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
    $GLOBALS['LSsession'] -> addHelpInfos (
      'LSformElement_maildir',
      array(
        'do' => _("La création ou modification de la maildir en même temps que l'utilisateur est activée. Cliquer sur ce bouton pour la désactiver."),
        'nodo' => _("Cliquer sur ce bouton pour activer la création/modification de la maildir en même temps que l'utilisateur.")
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
          if ($this -> params['html_options']['remoteRootPathRegex']) {
            if (
              (ereg($this -> params['html_options']['remoteRootPathRegex'],$new,$r_new) ||empty($new))
              && 
              (ereg($this -> params['html_options']['remoteRootPathRegex'],$cur,$r_cur)||empty($cur))
            )
            {
              $new = $r_new[1];
              $cur = $r_cur[1];
            }
            else {
              LSdebug('Pbl remoteRootPathRegex');
            }
          }
          $this -> _toDo = array (
            'action' => $action,
            'old' => $cur,
            'new' => $new
          );
          $this -> attr_html -> attribute -> addObjectEvent('after_modify',$this,'toDo');
        }
      }
    }
    return $retval;
  }
  
  function toDo() {
    if (is_array($this -> _toDo)) {
      switch($this -> _toDo['action']) {
        case 'delete':
          if ($this -> params['html_options']['archiveNameFormat']) {
            $newname=getFData($this -> params['html_options']['archiveNameFormat'],$this -> _toDo['old']);
            if ($newname) {
              if (renameMaildirByFTP($this -> _toDo['old'],$newname)) {
                $GLOBALS['LSsession'] -> addInfo("La boîte mail a été archivée.");
                return true;
              }
              return;
            }
            LSdebug($this -> name." - LSformElement_maildir->toDo() : Nom d'archivage incorrect.");
            return;
          }
          else {
            if (removeMaildirByFTP(null,$this -> _toDo['old'])) {
              $GLOBALS['LSsession'] -> addInfo("La boîte mail a été supprimée.");
              return true;
            }
            return;
          }
          break;
        case 'modify':
          if (renameMaildirByFTP($this -> _toDo['old'],$this -> _toDo['new'])) {
            $GLOBALS['LSsession'] -> addInfo("La boîte mail a été déplacée.");
            return true;
          }
          return;
          break;
        case 'create':
          if (createMaildirByFTP(null,$this -> _toDo['new'])) {
            $GLOBALS['LSsession'] -> addInfo("La boîte mail a été créée.");
            return true;
          }
          return;
          break;
        default:
          LSdebug($this -> name.' - LSformElement_maildir->toDo() : Action inconnu.');
      }
    }
    LSdebug($this -> name.' - LSformElement_maildir->toDo() : Rien à faire.');
    return true;
  }
}

?>
