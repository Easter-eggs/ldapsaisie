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
  public function getDisplay(){
    $return = $this -> getLabelInfos();
    if (!$this -> isFreeze()) {
      // Help Infos
      LStemplate :: addHelpInfo(
        'LSformElement_boolean',
        array(
          'clear' => _('Reset the choice.')
        )
      );
      LStemplate :: addJSscript('LSformElement_boolean.js');
    }
    $return['html'] = $this -> fetchTemplate(
      NULL,
      array(
        'yesTxt' => __($this -> getParam('html_options.true_label', ___('Yes'))),
        'noTxt' => __($this -> getParam('html_options.false_label', ___('No'))),
      )
    );
    return $return;
  }

  /**
   * CLI autocompleter for form element attribute values
   *
   * @param[in] &$opts      array                 Reference of array of avalaible autocomplete options
   * @param[in] $comp_word  string                The (unquoted) command word to autocomplete
   * @param[in] $attr_value string                The current attribute value in command word to autocomplete (optional, default: empty string)
   * @param[in] $multiple_value_delimiter string  The multiple value delimiter (optional, default: "|")
   * @param[in] $quote_char string                The quote character detected (optional, default: empty string)
   *
   * @retval void
   */
  public function autocomplete_attr_values(&$opts, $comp_word, $attr_value="", $multiple_value_delimiter="|", $quote_char='') {
    // Split attribute values and retreived splited value in $attr_values and $last_attr_value
    if (!$this -> split_autocomplete_attr_values($attr_value, $multiple_value_delimiter, $attr_values, $last_attr_value))
      return;

    // Add yes/no values
    foreach(array('yes', 'no') as $value) {
      $this -> add_autocomplete_attr_value_opts($opts, $attr_values, $value, $multiple_value_delimiter, $quote_char);
    }
  }

}
