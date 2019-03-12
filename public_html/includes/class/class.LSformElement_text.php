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

LSsession :: loadLSclass('LSformElement');

/**
 * Element texte d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments textes des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_text extends LSformElement {

  var $JSscripts = array();
  var $CSSfiles = array();
  var $fieldTemplate = 'LSformElement_text_field.tpl';

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  public function getDisplay(){
    $return = $this -> getLabelInfos();
    // value
    if (!$this -> isFreeze()) {
      if ($this -> getParam('html_options')) {
        LSsession :: addJSconfigParam($this -> name, $this -> getParam('html_options'));
      }
      LSsession :: addHelpInfos(
        'LSformElement_text',
        array(
          'generate' => _('Generate the value')
        )
      );
      LSsession :: addJSscript('LSformElement_text_field.js');
      LSsession :: addJSscript('LSformElement_text.js');
    }
    foreach ($this -> JSscripts as $js) {
      LSsession :: addJSscript($js);
    }
    foreach ($this -> CSSfiles as $css) {
      LSsession :: addCssFile($css);
    }
    $return['html'] = $this -> fetchTemplate();
    return $return;
  }

}

