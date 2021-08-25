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
 * Check support of showTechInfo addon by LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval boolean true if LSaccessRightsMatrixView addon is totally supported, false in other case
 */
function LSaddon_showTechInfo_support() {
  return True;
}

function showTechInfo($object) {
  $dn = $object -> getDn();

  // Retrieve internal attributes
  $internal_attrs = LSldap :: getAttrs(
    $dn,
    null,
    array('objectClass'),
    true
  );

  // Extract object classes
  $object_classes = array();
  $structural_object_class = null;
  if (array_key_exists('objectClass', $internal_attrs)) {
    $object_classes = (
      is_array($internal_attrs['objectClass'])?
      $internal_attrs['objectClass']:
      array($internal_attrs['objectClass'])
    );
    sort($object_classes);
    unset($internal_attrs['objectClass']);
    if (array_key_exists('structuralObjectClass', $internal_attrs)) {
      $structural_object_class = $internal_attrs['structuralObjectClass'];
    }
  }

  // Handle special internal attributes
  $special_internal_attributes_label = array(
    'structuralObjectClass' => _('Structural object class'),
    'createTimestamp' => _('Creation date'),
    'creatorsName' => _('Creator DN'),
    'modifyTimestamp' => _('Last modification date'),
    'modifiersName' => _('Last modifier DN'),
    'modifiersName' => _('Last modifier DN'),
    'entryCSN' => _('LDAP entry change sequence number'),
    'entryUUID' => _('LDAP entry UUID'),
    'hasSubordinates' => _('LDAP entry has children'),
  );
  $datetime_special_internal_attributes = array('createTimestamp', 'modifyTimestamp');
  $boolean_special_internal_attributes = array('hasSubordinates');
  $special_internal_attributes = array();
  foreach($special_internal_attributes_label as $attr => $label) {
    if (!array_key_exists($attr, $internal_attrs))
      continue;
    if (in_array($attr, $datetime_special_internal_attributes)) {
      $datetime = date_create_from_format('YmdHisO', $internal_attrs[$attr]);
      if ($datetime instanceof DateTime) {
        $special_internal_attributes[$attr] = array(
          'label' => $label,
          'values' => strftime("%Y/%m/%d %T", $datetime -> format('U')),
        );
      }
      else
        continue;
    }
    elseif (in_array($attr, $boolean_special_internal_attributes)) {
      if ($internal_attrs[$attr] == 'TRUE')
        $value = _('Yes');
      elseif ($internal_attrs[$attr] == 'FALSE')
        $value = _('No');
      else
        continue;
      $special_internal_attributes[$attr] = array(
        'label' => $label,
        'values' => $value,
      );
    }
    else {
      $special_internal_attributes[$attr] = array(
        'label' => $label,
        'values' => $internal_attrs[$attr],
      );
    }
    unset($internal_attrs[$attr]);
  }

  // Sort other internal attributes by name
  ksort($internal_attrs);

  LStemplate :: assign('pagetitle', getFData(_('%{name}: Technical information'), $object -> getDisplayName()));

  $LSview_actions=array();
  $LSview_actions['return'] = array (
    'label' => _('Go back'),
    'url' => 'object/'.$object->getType().'/'.urlencode($dn),
    'action' => 'view'
  );
  LStemplate :: assign('LSview_actions', $LSview_actions);

  if (LSsession :: loadLSclass('LSform')) {
    LSform :: loadDependenciesDisplayView();
  }

  LStemplate :: assign('object', $object);
  LStemplate :: assign('object_classes', $object_classes);
  LStemplate :: assign('structural_object_class', $structural_object_class);
  LStemplate :: assign('special_internal_attributes', $special_internal_attributes);
  LStemplate :: assign('other_internal_attrs', $internal_attrs);

  LStemplate :: addCssFile('showTechInfo.css');
  LSsession :: setTemplate('showTechInfo.tpl');
  // Display template
  LSsession :: displayTemplate();
  exit();
}
