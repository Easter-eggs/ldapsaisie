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
  var $CSSfiles = array(
    'LSformElement_text.css',
  );
  var $fieldTemplate = 'LSformElement_text_field.tpl';
  var $fetchVariables = array(
    'additionalCssClass' => array(),
  );

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
        LStemplate :: addJSconfigParam($this -> name, $this -> getParam('html_options'));
      }
      LStemplate :: addHelpInfo(
        'LSformElement_text',
        array(
          'generate' => _('Generate the value')
        )
      );
      if ($this -> getParam('html_options.autocomplete')) {
        LStemplate :: addJSconfigParam('LSformElement_text_autocomplete_noResultLabel', _('No result'));
      }
      LStemplate :: addJSscript('LSformElement_text_field.js');
      LStemplate :: addJSscript('LSformElement_text.js');
    }
    foreach ($this -> JSscripts as $js) {
      LStemplate :: addJSscript($js);
    }
    foreach ($this -> CSSfiles as $css) {
      LStemplate :: addCssFile($css);
    }
    $return['html'] = $this -> fetchTemplate();
    return $return;
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
    if ($this -> getParam('html_options.autocomplete.value_attributes', null, 'array')) {
      $this -> fetchVariables['additionalCssClass'][] = " LSformElement_text_autocomplete";
    }
    return  parent :: fetchTemplate($template,$variables);
  }


  /**
   * Autocomplete value
   *
   * @param[in] $pattern The pattern of the search
   *
   * @retval array(value -> displayName) Found values
   */
  public function autocomplete($pattern) {
    $ret = array();
    $value_attributes = $this -> getParam('html_options.autocomplete.value_attributes', null, 'array');
    if ($value_attributes) {
      $obj_type = $this -> getParam('html_options.autocomplete.object_type');
      if ($obj_type) {
        // Search with a specific objectType
        if (LSsession :: loadLSobject($obj_type)) {
          $obj = new $obj_type();
          $filters = array();
          foreach($value_attributes as $attr) {
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
          self :: log_debug("autocomplete($pattern): search $obj_type with pattern = '$pattern' and additional filter = '".$filter->as_string()."'");
          $sparams = array(
            'pattern' => $pattern,
            'attributes' => $value_attributes,
            'displayFormat' => $this -> getParam('html_options.autocomplete.display_name_format'),
            'filter' => $filter,
            'onlyAccessible' => $this -> getParam('html_options.autocomplete.only_accessible', false, 'bool'),
          );
          LSdebug($filter->as_string());
          $search = new LSsearch(
            $obj_type,
            'LSformElement_text::autocomplete',
            $sparams,
            true
          );
          $search -> run();
          foreach($search -> getSearchEntries() as $e) {
            foreach($value_attributes as $attr) {
              $values = ensureIsArray($e->get($attr));
              if (!$values) continue;
              foreach($values as $value) {
                $e -> registerOtherValue('value', $value);
                $ret[$value] = $e->displayName;
              }
            }
          }
        }
      }
      else {
        if ($this -> getParam('html_options.autocomplete.pattern_filter')) {
          // Filter on object with at least one of value attributes
          $filters = array();
          foreach($value_attributes as $attr) {
            $filters[] = Net_LDAP2_Filter::create($attr, 'present');
          }
          $filter = (count($filters)==1?$filters[0]:Net_LDAP2_Filter::combine('or', $filters));

          // Compute pattern filter
          $pattern_filter = getFData(
            $this -> getParam('html_options.autocomplete.pattern_filter', null, 'string'),
            Net_LDAP2_Filter::escape($pattern)
          );
          self :: log_debug("autocomplete($pattern): pattern filter = '$pattern_filter'");

          // Combine pattern and value attributes filters
          $filter = Net_LDAP2_Filter::combine(
            'and',
            array(
              Net_LDAP2_Filter::parse($pattern_filter),
              $filter,
            )
          );
        }
        else {
          foreach($value_attributes as $attr) {
            $filters[] = Net_LDAP2_Filter::create($attr, 'contains', $pattern);
          }
          $filter = (count($filters)==1?$filters[0]:Net_LDAP2_Filter::combine('or', $filters));
        }

        if ($this -> getParam('html_options.autocomplete.filter')) {
          $filter = Net_LDAP2_Filter::combine(
            'and',
            array(
              Net_LDAP2_Filter::parse($this -> getParam('html_options.autocomplete.filter')),
              $filter,
            )
          );
        }

        self :: log_debug("autocomplete($pattern): filter = '".$filter->as_string()."'");

        $displayNameFormat = $this -> getParam('html_options.autocomplete.display_name_format', false);
        $attributes = $value_attributes;
        if ($displayNameFormat)
          foreach(getFieldInFormat($displayNameFormat) as $attr)
            if(!in_array($attr, $attributes) && $attr != 'value')
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
            foreach($value_attributes as $attr) {
              if (!isset($object['attrs'][$attr])) continue;
              $values = ensureIsArray($object['attrs'][$attr]);
              foreach($values as $value)

                if ($displayNameFormat)
                  $displayName = getFData(
                    $displayNameFormat,
                    array_merge(
                      array('value' => $value, 'dn' => $object['dn']),
                      $object['attrs']
                    )
                  );
                else
                  $displayName = $value;
                $ret[$value] = $displayName;
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
        $data['values'] = $field -> autocomplete($_REQUEST['pattern']);
      }
    }
  }

}
