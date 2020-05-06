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
    'LSformElement_mail_field.js',
    'LSformElement_mail.js'
  );

  var $CSSfiles = array(
    'LSformElement_mail.css',
  );

  var $fetchVariables = array(
    'uriClass' => 'LSformElement_mail',
    'uriPrefix' => 'mailto:'
  );

  var $fieldTemplate = 'LSformElement_uri_field.tpl';

  public function getDisplay() {
    LSsession :: addHelpInfos (
      'LSformElement_mail',
      array(
        'mail' => _("Send a mail from here.")
      )
    );
    if (LSsession :: loadLSclass('LSmail')) {
      LSmail :: loadDependenciesDisplay();
    }
    if (!$this -> isFreeze() && $this -> getParam('html_options.autocomplete')) {
      LSsession :: addJSconfigParam('LSformElement_mail_autocomplete_noResultLabel', _('No result'));
    }
    return parent :: getDisplay();
  }

  public function fetchTemplate($template=NULL,$variables=array()) {
    if ($this -> getParam('html_options.disableMailSending', false, 'bool')) {
      $this -> fetchVariables['uriClass'] .= " LSformElement_mail_disableMailSending";
    }
    if ($this -> getParam('html_options.autocomplete', false, 'bool')) {
      $this -> fetchVariables['uriClass'] .= " LSformElement_mail_autocomplete";
    }
    return  parent :: fetchTemplate($template,$variables);
  }

  /**
   * Autocomplete email
   *
   * @param[in] $pattern The pattern of the search
   *
   * @retval array(mail -> displayName) Found emails
   */
  public function autocomplete($pattern) {
    $ret = array();
    if ($this -> getParam('html_options.autocomplete')) {
      $mail_attributes = $this -> getParam('html_options.autocomplete.mail_attributes', array('mail'));
      if (!is_array($mail_attributes)) $mail_attributes = array($mail_attributes);

      $obj_type = $this -> getParam('html_options.autocomplete.object_type');
      if ($obj_type) {
        // Search with a specific objectType
        if (LSsession :: loadLSobject($obj_type)) {
          $obj = new $obj_type();
          $filters = array();
          foreach($mail_attributes as $attr) {
            $filters[] = Net_LDAP2_Filter::create($attr, 'present');
          }
          $filter = (count($filters)==1?$filters[0]:Net_LDAP2_Filter::combine('or', $filters));
          if ($this -> getParam('html_options.autocomplete.filter')) {
            $filter = Net_LDAP2_Filter::combine(
              'and',
              array(
                Net_LDAP2_Filter::parse($this -> getParam('html_options.autocomplete.filter')),
                $filter,
              )
            );
          }
          $sparams = array(
            'pattern' => $pattern,
            'attributes' => $mail_attributes,
            'displayFormat' => $this -> getParam('html_options.autocomplete.display_name_format'),
            'filter' => $filter,
            'onlyAccessible' => $this -> getParam('html_options.autocomplete.onlyAccessible', false, 'bool'),
          );
          LSdebug($filter->as_string());
          $search = new LSsearch(
            $obj_type,
            'LSformElement_mail::autocomplete',
            $sparams,
            true
          );
          $search -> run();
          foreach($search -> getSearchEntries() as $e) {
            foreach($mail_attributes as $attr) {
              $mails = $e->get($attr);
              if (!$mails) continue;
              if (!is_array($mails)) $mails = array($mails);
              foreach($mails as $mail)
                $ret[$mail] = $e->displayName;
            }
          }
        }
      }
      else {
        $filters = array();
        foreach($mail_attributes as $attr) {
          $filters[] = Net_LDAP2_Filter::create($attr, 'contains', $pattern);
        }
        $filter = (count($filters)==1?$filters[0]:Net_LDAP2_Filter::combine('or', $filters));
        if ($this -> getParam('html_options.autocomplete.filter')) {
          $filter = Net_LDAP2_Filter::combine(
            'and',
            array(
              Net_LDAP2_Filter::parse($this -> getParam('html_options.autocomplete.filter')),
              $filter,
            )
          );
        }

        $displayNameFormat = $this -> getParam('html_options.autocomplete.display_name_format', false);
        $attributes = $mail_attributes;
        if ($displayNameFormat)
          foreach(getFieldInFormat($displayNameFormat) as $attr)
            if(!in_array($attr, $attributes))
              $attributes[] = $attr;

        $objects = LSldap :: search (
          $filter,
          $this -> getParam('html_options.autocomplete.basedn', null),
          array (
            'attributes' => $attributes,
            'scope' => $this -> getParam('html_options.autocomplete.scope', 'sub'),
          )
        );

        if (is_array($objects)) {
          foreach($objects as $object) {
            $displayName = ($displayNameFormat?getFData($displayNameFormat, $object['attrs']):null);
            foreach($mail_attributes as $attr) {
              if (!isset($object['attrs'][$attr])) continue;
              $mails = $object['attrs'][$attr];
              if (!is_array($mails)) $mails = array($mails);
              foreach($mails as $mail)
                $ret[$mail] = ($displayName?$displayName:$mail);
            }
          }
        }
      }
    }
    return $ret;
  }

  /**
   * This ajax method is used by the autocomplete function of the form element.
   *
   * @param[in] $data The address to the array of data witch will be return by the ajax request
   *
   * @retval void
   **/
  public static function ajax_autocomplete(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['pattern'])) && (isset($_REQUEST['idform'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $form = $object -> getForm($_REQUEST['idform']);
        $field=$form -> getElement($_REQUEST['attribute']);
        $data['mails'] = $field -> autocomplete($_REQUEST['pattern']);
      }
    }
  }

}
