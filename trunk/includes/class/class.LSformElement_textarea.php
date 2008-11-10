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
 * Element textarea d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments textarea des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_textarea extends LSformElement {

  var $fieldTemplate = 'LSformElement_textarea_field.tpl';

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();
    if (!$this -> isFreeze()) {
      $GLOBALS['LSsession'] -> addHelpInfos(
        'LSformElement_textarea',
        array(
          'clear' => _('Effacer')
        )
      );
      $GLOBALS['LSsession'] -> addJSscript('LSformElement_textarea.js');
    }
    $return['html'] = $this -> fetchTemplate();
    return $return;
  }
}

?>
