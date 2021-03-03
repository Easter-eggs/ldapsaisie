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

LSsession :: loadLSclass('LSformElement_text');

/**
 * Element mail d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments textes des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_mail extends LSformElement_text {

  var $JSscripts = array(
    'LSformElement_mail.js'
  );

  var $fetchVariables = array(
    'additionalCssClass' => array('LSformElement_mail'),
    'uriPrefix' => 'mailto:'
  );

  var $fieldTemplate = 'LSformElement_uri_field.tpl';

  // Flag to trigger warning about old Autocomplete config style
  // (detect in constructor and show on getDisplay())
  private $warnOldAutocompleteConfigStyle = false;

  /**
   * Constructor
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] &$form LSform The LSform parent object
   * @param[in] $name string The name of the element
   * @param[in] $label string The label of the element
   * @param[in] $params array The parameters of the element
   * @param[in] &$attr_html LSattr_html The LSattr_html object of the corresponding attribute
   *
   * @retval void
   */
  public function __construct(&$form, $name, $label, $params, &$attr_html){
    parent::__construct($form, $name, $label, $params, $attr_html);

    // Handle autocomplete retro-compatibility & default value attributes
    if ($this -> getParam('html_options.autocomplete')) {
      $mail_attributes = $this -> getParam('html_options.autocomplete.mail_attributes');
      if ($mail_attributes) {
        $this -> params['html_options']['autocomplete']['value_attributes'] = $mail_attributes;
        $this -> warnOldAutocompleteConfigStyle = true;
      }
      elseif (!$this -> getParam('html_options.autocomplete.value_attributes')) {
        if (!is_array($this -> params['html_options']['autocomplete']))
          $this -> params['html_options']['autocomplete'] = array();
        $this -> params['html_options']['autocomplete']['value_attributes'] = array('mail');
        $this -> warnOldAutocompleteConfigStyle = true;
      }
    }
  }

  public function getDisplay() {
    LStemplate :: addHelpInfo(
      'LSformElement_mail',
      array(
        'mail' => _("Send a mail from here.")
      )
    );
    if (LSsession :: loadLSclass('LSmail')) {
      LSmail :: loadDependenciesDisplay();
    }
    if ($this -> warnOldAutocompleteConfigStyle)
      LSerror :: addErrorCode('LSformElement_mail_01');
    return parent :: getDisplay();
  }

  /**
   * Return HTML code of the LSformElement based on its (smarty) template file
   *
   * @param[in] $template string The template filename (optional, default: $this -> template)
   * @param[in] $variables array Array of template variables to assign before template compilation (optional)
   *
   * @retval string HTML code of the LSformElement
   */
  public function fetchTemplate($template=NULL,$variables=array()) {
    if ($this -> getParam('html_options.disableMailSending', false, 'bool')) {
      $this -> fetchVariables['additionalCssClass'][] = " LSformElement_mail_disableMailSending";
    }
    return  parent :: fetchTemplate($template,$variables);
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSformElement_mail_01',
___("LSformElement_mail: the autocomplete feature was moved to parent LSformElement_text class and you still use old configuration style with parameter mail_attributes (and its default value). Please upgrade your configuration by renaming (or setting) this parameter to value_attributes.")
);
