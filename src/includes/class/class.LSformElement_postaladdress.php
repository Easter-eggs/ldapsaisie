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
  public function getDisplay(){
    $return = parent :: getDisplay();
    if ($this -> isFreeze()) {
      if (!empty($this->values)) {
        $map_url_format = $this -> getParam('html_options.map_url_format', 'http://nominatim.openstreetmap.org/search.php?q=%{pattern}', 'string');
        $map_url_pattern_generate_function = $this -> getParam('html_options.map_url_pattern_generate_function');
        $map_url_pattern_format = $this -> getParam('html_options.map_url_pattern_format');
        if ($map_url_pattern_generate_function) {
          if (is_callable($map_url_pattern_generate_function)) {
            $this -> attr_html -> attribute -> ldapObject -> registerOtherValue('pattern', call_user_func($map_url_pattern_generate_function, $this));
          }
          else {
            LSerror::addErrorCode('LSformElement_postaladdress_01', $map_url_pattern_generate_function);
          }
	}
	elseif ($map_url_pattern_format) {
          $pattern = $this -> attr_html -> attribute -> ldapObject -> getFData($map_url_pattern_format);
          $pattern = str_replace("\n"," ",$pattern);
          $pattern = urlencode($pattern);
          $this -> attr_html -> attribute -> ldapObject -> registerOtherValue('pattern', $pattern);
	}
        else {
          $this -> attr_html -> attribute -> ldapObject -> registerOtherValue('pattern', LSformElement_postaladdress__generate_pattern($this));
        }
        LStemplate :: addJSconfigParam('LSformElement_postaladdress_'.$this -> name, array (
            'map_url' => $this -> attr_html -> attribute -> ldapObject -> getFData($map_url_format)
          )
        );
        LStemplate :: addHelpInfo(
          'LSformElement_postaladdress',
            array(
              'viewOnMap' => _('View on map')
            )
        );
        LStemplate :: addJSscript('LSformElement_postaladdress.js');
      }
    }
    return $return;
  }

}

function LSformElement_postaladdress__generate_pattern($LSformElement) {
  return str_replace("\n"," ",$LSformElement->attr_html->attribute->getDisplayValue());
}

LSerror :: defineError('LSformElement_postaladdress_01',
_("LSformElement_postaladdress : Map URL pattern generate function is not callabled (%{function}).")
);
