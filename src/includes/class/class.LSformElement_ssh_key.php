<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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
   * Parse one value
   *
   * @param[in] $value string The value to parse
   * @param[in] $details boolean Enable/disable details return (optional, default: true)
   *
   * @retval array Parsed value
   */
  public function parseValue($value, $details=true) {
    if (!$details)
      return $value;
    if (preg_match('/^ssh-([a-z0-9]+) +([^ ]+) +(.*)$/', $value, $regs)) {
      return array(
        'type' => $regs[1],
        'mail' => $regs[3],
        'value' => $value
      );
    }
    return array(
      'type' => null,
      'mail' => null,
      'value' => $value
    );
  }

 /**
  * Retourne les infos d'affichage de l'élément
  *
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  public function getDisplay(){
    LStemplate :: addCssFile('LSformElement_ssh_key.css');
    $return = $this -> getLabelInfos();
    $params = array();
    if (!$this -> isFreeze()) {
      $params['values_txt'] = $this -> values;
    }
    else {
      LStemplate :: addJSscript('LSformElement_ssh_key.js');
      LStemplate :: addHelpInfo(
        'LSformElement_ssh_key',
        array(
          'display' => _("Display the full key.")
        )
      );

      $values_txt = array();
      foreach ($this -> values as $value) {
        $parsedValue = $this -> parseValue($value);
        $parsedValue['shortTxt'] = substr($value, 0, 15);
        $values_txt[] = $parsedValue;
      }
      $params['values_txt'] = $values_txt;
      $params['unknowTypeTxt'] = _('Unknown type');
    }
    $return['html'] = $this -> fetchTemplate(NULL,$params);
    return $return;
  }

}
