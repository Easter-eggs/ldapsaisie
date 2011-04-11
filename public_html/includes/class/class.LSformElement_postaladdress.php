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

LSsession :: loadLSclass('LSformElement_textarea');

/**
 * Element postaladdress d'un formulaire pour LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_postaladdress extends LSformElement_textarea {

  var $fieldTemplateExtraClass = 'LSformElement_postaladdress';

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = parent :: getDisplay();
    if ($this -> isFreeze()) {
      if (isset($this -> params['html_options']['map_url_format']) && !empty($this->values)) {
        LSsession :: addJSconfigParam('LSformElement_postaladdress_'.$this -> name, array (
            'map_url' => $this -> attr_html -> attribute -> ldapObject -> getFData($this -> params['html_options']['map_url_format']) 
          )
        );
        LSsession :: addHelpInfos(
          'LSformElement_postaladdress',
            array(
              'viewOnMap' => _('View on map')
            )
        );
        LSsession :: addJSscript('LSformElement_postaladdress.js');
      }
    }
    return $return;
  }
}

?>
