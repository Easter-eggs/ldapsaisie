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
// Error messages

// Support
LSerror :: defineError('LSACCESSRIGHTSMATRIXVIEW_SUPPORT_01',
  ___("Access Right Matrix Support : The global array %{array} is not defined.")
);

/**
 * Check support of LSaccessRightsMatrixView addon by LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval boolean true if LSaccessRightsMatrixView addon is totally supported, false in other case
 */
function LSaddon_LSaccessRightsMatrixView_support() {
  $retval = True;
  $MUST_DEFINE_ARRAY= array(
    'LSaccessRightsMatrixView_allowed_LSprofiles',
  );
  foreach($MUST_DEFINE_ARRAY as $array) {
    if ( !isset($GLOBALS[$array]) || !is_array($GLOBALS[$array])) {
      LSerror :: addErrorCode('LSACCESSRIGHTSMATRIXVIEW_SUPPORT_01',$array);
      $retval=false;
    }
  }

  if ($retval)
    $retval = LSsession :: registerLSaddonView(
      'LSaccessRightsMatrixView',
      'accessRightsMatrix',
      _('Access rights matrix'),
      'LSaccessRightsMatrixView',
      $GLOBALS['LSaccessRightsMatrixView_allowed_LSprofiles']
    );

  return $retval;
}

function LSaccessRightsMatrixView() {
  $LSprofiles = array(
    'user' => _('All connected users'),
  );
  // Authenticable user objects types
  $authObjTypes = LSauth :: getAuthObjectTypes();
  foreach ($authObjTypes as $objType => $objParams)
    if (LSsession :: loadLSobject($objType))
      $LSprofiles[$objType] = LSldapObject :: getLabel($objType);

  // Custom configured LSprofiles
  if (isset(LSsession :: $ldapServer["LSprofiles"]) && is_array(LSsession :: $ldapServer["LSprofiles"]))
    foreach(LSsession :: $ldapServer["LSprofiles"] as $LSprofile => $LSprofile_conf)
      $LSprofiles[$LSprofile] = (isset($LSprofile_conf['label'])?__($LSprofile_conf['label']):$LSprofile);

  // List object types
  $objectTypes = array();

  // Handle LSaccess parameter
  if (isset(LSsession :: $ldapServer["LSaccess"]) && is_array(LSsession :: $ldapServer["LSaccess"]))
    foreach (LSsession :: $ldapServer["LSaccess"] as $LSobject)
      if (!in_array($LSobject, $objectTypes))
        $objectTypes[] = $LSobject;

  // Handle subDn access
  if (isset(LSsession :: $ldapServer["subDn"]) && is_array(LSsession :: $ldapServer["subDn"])) {
    // SubDn object types
    foreach (LSsession :: $ldapServer["subDn"] as $subDn_name => $subDn_conf) {
      if (isset($subDn_conf['LSobjects']) && is_array($subDn_conf['LSobjects']))
        foreach ($subDn_conf['LSobjects'] as $LSobject)
          if (!in_array($LSobject, $objectTypes))
            $objectTypes[] = $LSobject;
      // SubDn by list of objects
      if (isset($subDn_conf['LSobject']) && is_array($subDn_conf['LSobject']))
        foreach ($subDn_conf['LSobject'] as $objType => $objTypeConf)
          if (isset($objTypeConf['LSobjects']) && is_array($objTypeConf['LSobjects']))
            foreach ($objTypeConf['LSobjects'] as $LSobject)
            if (!in_array($LSobject, $objectTypes))
              $objectTypes[] = $LSobject;
    }
  }

  $LSobjects = array();
  foreach ($objectTypes as $LSobject) {
    if (!LSsession :: loadLSobject($LSobject))
      continue;

    // List attributes and rigths on their
    $attrs = array();
    foreach(LSconfig :: get("LSobjects.$LSobject.attrs", array()) as $attr_name => $attr_config) {
      $raw_attr_rights = LSconfig :: get('rights', array(), 'array', $attr_config);
      $attr_rights = array();
      if (array_key_exists($LSobject, $authObjTypes))
        $attr_rights['self'] = LSconfig :: get('self', False, null, $raw_attr_rights);
      foreach(array_keys($LSprofiles) as $LSprofile) {
        $attr_rights[$LSprofile] = LSconfig :: get($LSprofile, False, null, $raw_attr_rights);
      }
      $attrs[$attr_name] = array (
        'label' => __(LSconfig :: get('label', $attr_name, 'string', $attr_config)),
        'rights' => $attr_rights,
      );
    }

    // List relations and rigths on their
    $relations = array();
    foreach(LSconfig :: get("LSobjects.$LSobject.LSrelation", array()) as $relation_name => $relation_config) {
      $raw_relation_rights = LSconfig :: get('rights', array(), 'array', $relation_config);
      $relation_rights = array();
      if (array_key_exists($LSobject, $authObjTypes))
        $relation_rights['self'] = LSconfig :: get('self', False, null, $raw_relation_rights);
      foreach(array_keys($LSprofiles) as $LSprofile) {
        $relation_rights[$LSprofile] = LSconfig :: get($LSprofile, False, null, $raw_relation_rights);
      }
      $relations[$relation_name] = array (
        'label' => __(LSconfig :: get('label', $relation_name, 'string', $relation_config)),
        'rights' => $relation_rights,
      );
    }

    // List customActions and rigths on their
    $customActions = array();
    foreach(LSconfig :: get("LSobjects.$LSobject.customActions", array()) as $action_name => $action_config) {
      $raw_action_rights = LSconfig :: get('rights', array(), 'array', $action_config);
      $action_rights = array();
      if (array_key_exists($LSobject, $authObjTypes))
        $action_rights['self'] = in_array('self', $raw_action_rights);
      foreach(array_keys($LSprofiles) as $LSprofile)
        $action_rights[$LSprofile] = in_array($LSprofile, $raw_action_rights);
      $customActions[$action_name] = array (
        'label' => __(LSconfig :: get('label', $action_name, 'string', $action_config)),
        'rights' => $action_rights,
      );
    }

    // List customSearchActions and rigths on their
    $customSearchActions = array();
    foreach(LSconfig :: get("LSobjects.$LSobject.LSsearch.customActions", array()) as $action_name => $action_config) {
      $raw_action_rights = LSconfig :: get('rights', array(), 'array', $action_config);
      $action_rights = array();
      if (array_key_exists($LSobject, $authObjTypes))
        $action_rights['self'] = in_array('self', $raw_action_rights);
      foreach(array_keys($LSprofiles) as $LSprofile)
        $action_rights[$LSprofile] = in_array($LSprofile, $raw_action_rights);
      $customSearchActions[$action_name] = array (
        'label' => __(LSconfig :: get('label', $action_name, 'string', $action_config)),
        'rights' => $action_rights,
      );
    }

    // Handle LSform layout
    $layout = false;
    if (LSconfig :: get("LSobjects.$LSobject.LSform.layout")) {
      $layout = array();
      $displayed_attrs = array();
      foreach(LSconfig :: get("LSobjects.$LSobject.LSform.layout") as $tab => $tab_config) {
        $layout[$tab] = array(
          'label' => __(LSconfig :: get('label', $tab, 'string', $tab_config)),
          'attrs' => LSconfig :: get('args', $tab, 'array', $tab_config),
        );
        $displayed_attrs = array_merge($displayed_attrs, $layout[$tab]['attrs']);
      }
      $masked_attrs = array_diff(array_keys(LSconfig :: get("LSobjects.$LSobject.attrs", array(), 'array')), $displayed_attrs);
      if ($masked_attrs) {
        $layout['masked_attrs'] = array(
          'label' => _('Masked attributes'),
          'attrs' => $masked_attrs,
        );
      }
    }

    $LSobjects[$LSobject] = array (
      'label' => __(LSconfig :: get("LSobjects.$LSobject.label", $LSobject, 'string')),
      'attrs' => $attrs,
      'relations' => $relations,
      'customActions' => $customActions,
      'customSearchActions' => $customSearchActions,
      'layout' => $layout,
    );
  }

  // Determine current LSobject
  reset($LSobjects);
  $LSobject = (isset($_REQUEST['LSobject']) && array_key_exists($_REQUEST['LSobject'], $LSobjects)?$_REQUEST['LSobject']:key($LSobjects));

  if (array_key_exists($LSobject, $authObjTypes))
    $LSprofiles = array_merge(array('self' => _('The user him-self')), $LSprofiles);

  LSlog :: get_logger('LSaddon_LSaccessRightsMatrixView') -> debug($LSobjects);

  LStemplate :: assign('pagetitle', _('Access rights matrix'));
  LStemplate :: assign('LSprofiles', $LSprofiles);
  LStemplate :: assign('LSobjects', $LSobjects);
  LStemplate :: assign('LSobject', $LSobject);

  LStemplate :: addCssFile('LSaccessRightsMatrixView.css');
  LSsession :: setTemplate('LSaccessRightsMatrixView.tpl');
}
