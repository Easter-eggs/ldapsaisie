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
// Error messages

// Support
LSerror :: defineError('LSACCESSRIGHTSMATRIXVIEW_SUPPORT_01',
  _("Access Right Matrix Support : The global array %{array} is not defined.")
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
	if (isset(LSsession :: $ldapServer["LSprofiles"]) && is_array(LSsession :: $ldapServer["LSprofiles"]))
		foreach(LSsession :: $ldapServer["LSprofiles"] as $LSprofile => $LSprofile_conf)
			$LSprofiles[$LSprofile] = (isset($LSprofile_conf['label'])?__($LSprofile_conf['label']):$LSprofile);
	$LSobjects = array();
	foreach (LSsession :: $ldapServer["LSaccess"] as $LSobject) {
		if (!LSsession :: loadLSobject($LSobject))
			continue;

		// List attributes and rigths on their
		$attrs = array();
		foreach(LSconfig :: get("LSobjects.$LSobject.attrs", array()) as $attr_name => $attr_config) {
			$raw_attr_rights = LSconfig :: get('rights', array(), 'array', $attr_config);
			$attr_rights = array();
			if ($LSobject == LSsession :: $ldapServer["authObjectType"])
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
			if ($LSobject == LSsession :: $ldapServer["authObjectType"])
				$relation_rights['self'] = LSconfig :: get('self', False, null, $raw_relation_rights);
			foreach(array_keys($LSprofiles) as $LSprofile) {
				$relation_rights[$LSprofile] = LSconfig :: get($LSprofile, False, null, $raw_relation_rights);
			}
			$relations[$relation_name] = array (
				'label' => __(LSconfig :: get('label', $relation_name, 'string', $relation_config)),
				'rights' => $relation_rights,
			);
		}

		$LSobjects[$LSobject] = array (
			'label' => __(LSconfig :: get("LSobjects.$LSobject.label", $LSobject, 'string')),
			'attrs' => $attrs,
			'relations' => $relations,
		);
	}

	// Determine current LSobject
	reset($LSobjects);
	$LSobject = (isset($_REQUEST['LSobject']) && array_key_exists($_REQUEST['LSobject'], $LSobjects)?$_REQUEST['LSobject']:key($LSobjects));

	if ($LSobject == LSsession :: $ldapServer["authObjectType"])
		$LSprofiles = array_merge(array('self' => _('The user him-self')), $LSprofiles);

	LSlog :: get_logger('LSaddon_LSaccessRightsMatrixView') -> debug($LSobjects);

	LStemplate :: assign('pagetitle', _('Access rights matrix'));
	LStemplate :: assign('LSprofiles', $LSprofiles);
	LStemplate :: assign('LSobjects', $LSobjects);
	LStemplate :: assign('LSobject', $LSobject);

	LSsession :: addCssFile('LSaccessRightsMatrixView.css');
	LSsession :: setTemplate('LSaccessRightsMatrixView.tpl');
}
