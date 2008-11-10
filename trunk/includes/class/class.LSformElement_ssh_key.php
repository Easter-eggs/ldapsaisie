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
 * Element ssh_key d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments ssh_key des formulaires.
 * Elle étend la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_ssh_key extends LSformElement {

  var $template = 'LSformElement_ssh_key.tpl';
  var $fieldTemplate = 'LSformElement_ssh_key_field.tpl';

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $GLOBALS['LSsession'] -> addCssFile('LSformElement_ssh_key.css');
    $return = $this -> getLabelInfos();
    $params = array();
    if (!$this -> isFreeze()) {
      $params['values_txt'] = $this -> values;
    }
    else {
      $GLOBALS['LSsession'] -> addJSscript('LSformElement_ssh_key.js');
      $GLOBALS['LSsession'] -> addHelpInfos (
        'LSformElement_ssh_key',
        array(
          'display' => _("Afficher la clef complète.")
        )
      );
      
      $values_txt = array();
      foreach ($this -> values as $value) {
        if (ereg('^ssh-([a-z]+) (.*)== (.*)$',$value,$regs)) {
          $values_txt[] = array(
            'type' => $regs[1],
            'shortTxt' => substr($regs[2],0,10),
            'mail' => $regs[3],
            'value' => $value
          );
        }
        else {
          $values_txt[] = array(
            'shortTxt' => substr($value,0,15),
            'value' => $value
          );
        }
      }
      $params['values_txt'] = $values_txt;
      $params['unknowTypeTxt'] = _('Type non reconnu');
    }
    $return['html'] = $this -> fetchTemplate(NULL,$params);
    return $return;
  }
  
}

?>
