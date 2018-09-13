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
 * Element texte d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments boolean des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_boolean extends LSformElement {

  var $fieldTemplate = 'LSformElement_boolean_field.tpl';
  var $template = 'LSformElement_boolean.tpl';

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
      // Help Infos
      LSsession :: addHelpInfos(
        'LSformElement_boolean',
        array(
          'clear' => _('Reset the choice.')
        )
      );
      LSsession :: addJSscript('LSformElement_boolean.js');
    }
    $return['html'] = $this -> fetchTemplate(
      NULL,
      array(
        'yesTxt' => (isset($this -> params['html_options']['true_label']) && !empty($this -> params['html_options']['true_label']))?__($this -> params['html_options']['true_label']):_('Yes'),
        'noTxt' => (isset($this -> params['html_options']['false_label']) && !empty($this -> params['html_options']['false_label']))?__($this -> params['html_options']['false_label']):_('No'),
      )
    );
    return $return;
  }

}

?>
