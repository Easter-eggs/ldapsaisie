<?php
/*******************************************************************************
 * Copyright (C) 2021 Easter-eggs
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
LSerror :: defineError('DYNGROUP_SUPPORT_01',
  ___("Dynamic groups support: The constant %{const} is not defined.")
);
LSerror :: defineError('DYNGROUP_SUPPORT_02',
  ___("Dynamic groups support: You must at least define all constantes of dynamic groups's by DN or by UID.")
);

LSerror :: defineError('DYNGROUP_01',
  ___("Dynamic groups: The attribute %{dependency} is missing. Unable to forge the attribute %{attr}.")
);
LSerror :: defineError('DYNGROUP_02',
  ___("Dynamic groups: Fail to parse %{attr} value : invalid number of parts.")
);



/**
 * Check dyngroup support by ldapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval boolean true if dyngroup are fully supported, false otherwise
 */
function LSaddon_dyngroup_support() {
  $retval = true;

  $MUST_DEFINE_CONST = array(
    'DYNGROUP_OBJECT_TYPE',
  );

  foreach($MUST_DEFINE_CONST as $const) {
    if ( !defined($const) || !constant($const) ) {
      LSerror :: addErrorCode('DYNGROUP_SUPPORT_01', $const);
      $retval = false;
    }
  }

  if (
    !(constant('DYNGROUP_MEMBER_DN_URI_ATTRIBUTE') && constant('DYNGROUP_MEMBER_DN_ATTRIBUTE') && constant('DYNGROUP_MEMBER_DN_STATIC_ATTRIBUTE')) &&
    !(constant('DYNGROUP_MEMBER_UID_URI_ATTRIBUTE') && constant('DYNGROUP_MEMBER_UID_ATTRIBUTE') && constant('DYNGROUP_MEMBER_UID_STATIC_ATTRIBUTE'))
  ) {
    LSerror :: addErrorCode('DYNGROUP_SUPPORT_02');
    $retval = false;
  }

  if ($retval && php_sapi_name() == 'cli') {
    LScli :: add_command(
      'update_dyngroups_members_cache',
      'cli_updateDynGroupsMembersCache',
      'Update dynamic groups members cache'
    );
  }

  return $retval;
}

/*
 * Parse LDAP search URI
 *
 * @param[in] $uri string The LDAP search URI to parse
 *
 * @retval array|false Array of parsed LDAP search URI info, or false
 */
function parseLdapSearchURI($uri) {
  $uri_parts = explode('?', $uri);
  if (count($uri_parts) < 2) {
    return false;
  }

  return array (
    'ldap_base_uri' => $uri_parts[0],
    'requested_attributes' => $uri_parts[1],
    'scope' => (isset($uri_parts[2])?$uri_parts[2]:null),
    'filter' => (isset($uri_parts[3])?$uri_parts[3]:null),
  );
}

/*
 * Extract attributes cited in an LDAP filter string
 *
 * @param[in] $filter string The LDAP filter string
 *
 * @retval array|false Array of the attributes cited in the LDAP filter string, or false
 */
function extractAttributesFromLdapFilterString($filter) {
  if ($filter[0] != '(')
    $filter = "($filter)";

  if (!preg_match_all('#\((?P<attr>[a-z0-9]+)(?P<op>[~<>]?=)(?P<value>[^\)]+)\)#i', $filter, $parts))
    return false;

  return $parts['attr'];
}

/**
 * Generate dyngroup memberUid URI attribute value from memberDN URI attribute
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject The LSldapObject
 *
 * @retval array|null array of memberUid URI attribute values or null in case of error
 */
function generateDyngroupMemberUidURI($ldapObject) {
  if (!isset($ldapObject -> attrs[ DYNGROUP_MEMBER_DN_URI_ATTRIBUTE ])) {
    LSerror :: addErrorCode(
      'DYNGROUP_01',
      array('dependency' => DYNGROUP_MEMBER_DN_URI_ATTRIBUTE, 'attr' => DYNGROUP_MEMBER_UID_URI_ATTRIBUTE)
    );
    return;
  }

  $dn_uri = $ldapObject -> attrs[ DYNGROUP_MEMBER_DN_URI_ATTRIBUTE ] -> getValue();
  if (empty($dn_uri))
      return;

  $uri_parts = explode('?', $dn_uri[0]);
  if (count($uri_parts) < 2) {
    LSerror :: addErrorCode('DYNGROUP_02', DYNGROUP_MEMBER_DN_URI_ATTRIBUTE);
    return;
  }
  $uri_parts[1] = 'uid';
  return array(
    implode('?', $uri_parts)
  );
}

/**
 * Update dyngroup cache members attributes
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $dyngroup The LSldapObject
 *
 * @retval boolean True on success, False otherwise
 */
function updateDynGroupMembersCache($dyngroup, $reload=true) {
  if ($reload && !$dyngroup -> reloadData()) {
    LSlog :: get_logger('LSaddon_dyngroup') -> error("Fail to reload $dyngroup data");
    return false;
  }
  $attrs_map = array(
    'DYNGROUP_MEMBER_DN_ATTRIBUTE' => 'DYNGROUP_MEMBER_DN_STATIC_ATTRIBUTE',
    'DYNGROUP_MEMBER_UID_ATTRIBUTE' => 'DYNGROUP_MEMBER_UID_STATIC_ATTRIBUTE'
  );
  $old_attrs = array();
  $attrs = array();
  foreach ($attrs_map as $src_attr => $dst_attr) {
    $src_attr = constant($src_attr);
    $dst_attr = constant($dst_attr);
    if (!$src_attr || !$dst_attr)
      continue;
    LSlog :: get_logger('LSaddon_dyngroup') -> trace(
      "updateDynGroupMembersCache($dyngroup): update attribute '$dst_attr' from '$dst_attr'"
    );
    $old_attrs[$dst_attr] = $dyngroup -> getValue($dst_attr, false, array());
    ksort($old_attrs[$dst_attr]);

    $attrs[$dst_attr] = $dyngroup -> getValue($src_attr, false, array());
    ksort($attrs[$dst_attr]);
  }

  if ($attrs == $old_attrs) {
    LSlog :: get_logger('LSaddon_dyngroup') -> debug(
      "updateDynGroupMembersCache($dyngroup): no member change"
    );
    return true;
  }
  LSlog :: get_logger('LSaddon_dyngroup') -> debug(
    "updateDynGroupMembersCache($dyngroup): change detected:\n - Current: ".varDump($old_attrs).
    "\n\n - New: ".varDump($attrs)
  );

  if (!$old_attrs) {
    LSlog :: get_logger('LSaddon_dyngroup') -> error(
      "updateDynGroupMembersCache($dyngroup): No member attribute defined !"
    );
    return false;
  }

  if (!LSldap :: update(DYNGROUP_OBJECT_TYPE, $dyngroup -> getDn(), $attrs)) {
    LSlog :: get_logger('LSaddon_dyngroup') -> error("Fail to update $dyngroup cache members attributes");
    return false;
  }
  LSlog :: get_logger('LSaddon_dyngroup') -> debug(
    "updateDynGroupMembersCache($dyngroup): cache members attributes updated"
  );
  return true;
}

function updateDynGroupsMembersCache() {
  if (!LSsession :: loadLSobject(DYNGROUP_OBJECT_TYPE))
    LSlog :: get_logger('LSaddon_dyngroup') -> fatal('Fail to load dyngroup object type');

  // List dyn groups
  $dyngroup_class = constant('DYNGROUP_OBJECT_TYPE');
  $dyngroup = new $dyngroup_class();
  $error = false;
  foreach($dyngroup -> listObjects(null, null, array('withoutCache' => true)) as $group) {
    if (!updateDynGroupMembersCache($group, false))
      $error = true;
  }
  return !$error;
}

function triggerUpdateDynGroupsMembersCacheOnUserModify($user) {
  $changed_attrs = array();
  foreach($user -> attrs as $attr_name => $attr) {
    if ($attr -> isUpdate())
      $changed_attrs[] = strtolower($attr_name);
  }
  if (!$changed_attrs) {
    LSlog :: get_logger('LSaddon_dyngroup') -> debug(
      "triggerUpdateDynGroupsMembersCacheOnUserModify($user): no attribute changed"
    );
    return true;
  }
  LSlog :: get_logger('LSaddon_dyngroup') -> debug(
    "triggerUpdateDynGroupsMembersCacheOnUserModify($user): changed attributes = ".implode(', ', $changed_attrs)
  );

  return triggerUpdateDynGroupsMembersCacheOnUserChanges($user, $changed_attrs);
}

function triggerUpdateDynGroupsMembersCacheOnUserCreateOrDelete($user) {
  $changed_attrs = array_keys($user -> attrs);
  return triggerUpdateDynGroupsMembersCacheOnUserChanges($user, $changed_attrs);
}

function triggerUpdateDynGroupsMembersCacheOnUserChanges(&$user, &$changed_attrs) {
  if (!LSsession :: loadLSobject(DYNGROUP_OBJECT_TYPE)) {
    LSlog :: get_logger('LSaddon_dyngroup') -> error('Fail to load dyngroup object type');
    return false;
  }

  // List dyn groups
  $dyngroup_class = constant('DYNGROUP_OBJECT_TYPE');
  $dyngroup = new $dyngroup_class();
  $error = false;
  $impacted_dyngroups = 0;
  $updated_dyngroups = 0;
  foreach($dyngroup -> listObjects() as $group) {  // Leave cache enabled
    $uri = null;
    foreach(array(DYNGROUP_MEMBER_DN_URI_ATTRIBUTE, DYNGROUP_MEMBER_UID_URI_ATTRIBUTE) as $uri_attr) {
      $uri = $group -> getValue($uri_attr, true);
      if ($uri) break;
    }

    if (!$uri) {
      LSlog :: get_logger('LSaddon_dyngroup') -> debug(
        "triggerUpdateDynGroupsMembersCacheOnUserChanges($user): $group hasn't member URI attribute."
      );
      continue;
    }
    $parsed_uri = parseLdapSearchURI($uri);
    if (!$parsed_uri) {
      LSlog :: get_logger('LSaddon_dyngroup') -> warning(
        "triggerUpdateDynGroupsMembersCacheOnUserChanges($user): fail to parse member URI attribute of $group."
      );
      continue;
    }

    if (!$parsed_uri['filter']) {
      LSlog :: get_logger('LSaddon_dyngroup') -> warning(
        "triggerUpdateDynGroupsMembersCacheOnUserChanges($user): no LDAP filter found in member URI attribute of $group."
      );
      continue;
    }

    $filter_attrs = extractAttributesFromLdapFilterString($parsed_uri['filter']);
    LSlog :: get_logger('LSaddon_dyngroup') -> debug(
      "triggerUpdateDynGroupsMembersCacheOnUserChanges($user): attributes of LDAP filter of member URI attribute of $group = ".implode(', ', $filter_attrs)
    );

    if (!$filter_attrs) {
      LSlog :: get_logger('LSaddon_dyngroup') -> warning(
        "triggerUpdateDynGroupsMembersCacheOnUserChanges($user): fail to extract attribute from LDAP filter '".$parsed_uri['filter']."' from member URI attribute of $group."
      );
      continue;
    }

    $is_impacted = false;
    foreach($filter_attrs as $attr) {
      if (in_array(strtolower($attr), $changed_attrs)) {
        $is_impacted = true;
        break;
      }
    }

    if (!$is_impacted) {
      LSlog :: get_logger('LSaddon_dyngroup') -> debug(
        "triggerUpdateDynGroupsMembersCacheOnUserChanges($user): $group is NOT impacted by user's changes."
      );
      continue;
    }
    LSlog :: get_logger('LSaddon_dyngroup') -> debug(
      "triggerUpdateDynGroupsMembersCacheOnUserChanges($user): $group is impacted by user's changes ".
      "(at least by attribute '$attr')."
    );
    $impacted_dyngroups++;
    if (updateDynGroupMembersCache($group, false))
      $updated_dyngroups++;
    else
      $error = true;
  }
  LSlog :: get_logger('LSaddon_dyngroup') -> debug(
    "triggerUpdateDynGroupsMembersCacheOnUserChanges($user): $impacted_dyngroups impacted dyngroups found, ".
    "$updated_dyngroups updated."
  );
  if ($impacted_dyngroups && $impacted_dyngroups == $updated_dyngroups) {
    LSsession :: addInfo(
      getFData(
        _('Members cache of %{count} dynamic group(s) have been updated because thes were potentially impacted by your changes.'),
        $updated_dyngroups)
      );
  }
  else if ($error) {
    LSsession :: addInfo(
      getFData(
        _('Members cache of %{count} dynamic group(s) have NOT been updated but thes were potentially impacted by your changes. A delay of some minutes could be necessary to handle your changes on this groups.'),
        ($impacted_dyngroups-$updated_dyngroups)
      )
    );
  }
  return !$error;
}


if (php_sapi_name() != 'cli')
  return true;

function cli_updateDynGroupsMembersCache($command_args) {
  return updateDynGroupsMembersCache();
}
