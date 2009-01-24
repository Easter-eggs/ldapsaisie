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
 * Element select d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments select des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_select extends LSformElement {

  var $template = 'LSformElement_select.tpl';
  var $fieldTemplate = 'LSformElement_select.tpl';

 /**
  * Retourn les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();
    $params = array();
    if (!$this -> isFreeze()) {
      LSsession :: addHelpInfos (
        'LSformElement_select',
        array(
          'clear' => _("Effacer la sélection.")
        )
      );
      LSsession :: addJSscript('LSformElement_select.js');
    }
    $params['possible_values'] = $this -> params['text_possible_values'];
    $return['html'] = $this -> fetchTemplate(NULL,$params);
    return $return;
  }

}

?>
