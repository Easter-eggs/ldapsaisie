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

$GLOBALS['LSobjects']['LSdyngroup'] = array (
  'objectclass' => array(
    'LSdyngroup',
    'posixGroup',
  ),
  'rdn' => 'cn',
  'container_dn' => 'ou=dyngroups',
  'container_auto_create' => array(
    'objectclass' => array(
      'top',
      'organizationalUnit',
    ),
    'attrs' => array(
      'ou' => 'dyngroups',
    ),
  ),
  'display_name_format' => '%{cn}',
  'label' => 'Dynamic groups',

  'customActions' => array (
    'showTechInfo' => array (
      'function' => 'showTechInfo',
      'label' => 'Show technical information',
      'hideLabel' => True,
      'noConfirmation' => true,
      'disableOnSuccessMsg' => true,
      'icon' => 'tech_info',
      'rights' => array (
        'admin',
      ),
    ),
    'updateDynGroupMembersCache' => array (
      'function' => 'updateDynGroupMembersCache',
      'label' => 'Update members cache',
      'question_format' => 'Are you sure you want to update members cache of this dynamic group ?',
      'onSuccessMsgFormat' => 'Members cache updated.',
      'icon' => 'refresh',
      'rights' => array (
        'admin',
      ),
    ),
  ),

  'LSsearch' => array (
    'attrs' => array (
      'cn',
      'gidNumber' => array (
        'searchLSformat' => '(gidNumber=%{pattern})',
        'approxLSformat' => '(gidNumber=%{pattern})',
      ),
      'description',
    ),
    'params' => array (
      'sortBy' => 'displayName'
    ),
    'customActions' => array (
      'updateDynGroupsMembersCache' => array (
        'function' => 'updateDynGroupsMembersCache',
        'label' => 'Update members cache',
        'question_format' => 'Are you sure you want to update members cache of all dynamic groups <small>(could be quite long)</small> ?',
        'onSuccessMsgFormat' => 'Dynamic groups members cache updated.',
        'icon' => 'refresh',
        'rights' => array (
          'admin',
        ),
      ),
    ),
  ),

  'after_create' => 'updateDynGroupMembersCache',

  'attrs' => array (

    /* ----------- start -----------*/
    'cn' => array (
      'label' => 'Name',
      'ldap_type' => 'ascii',
      'html_type' => 'text',
      'required' => 1,
      'check_data' => array (
        'alphanumeric' => array(
          'msg' => 'Name must contain alphanumeric values only.',
        ),
      ),
      'validation' => array (
        array (
          'filter' => 'cn=%{val}',
          'result' => 0,
        ),
      ),
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'r',
      ),
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'gidNumber' => array (
      'label' => 'Identifier',
      'ldap_type' => 'numeric',
      'html_type' => 'text',
      'required' => 1,
      'generate_function' => 'generate_samba_gidNumber',
      'validation' => array (
        array (
          'filter' => 'gidNumber=%{val}',
          'result' => 0,
        ),
      ),
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
      ),
      'form' => array (
        'modify' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'lsDynGroupMemberDnURI' => array (
      'label' => 'Member search URI',
      'help_info' => "<p>LDAP search URI or group members. A LDAP search URI is composed of the following parts separated by semicolons :<ul>
<li>The LDAP URI in format <code>ldap://[host]/[base DN]</code>. For instance, to make a request on the same LDAP server, use <code>ldap:///o=ls</code></li>
<li>The retreived attributes (separated by coma, optional)</li>
<li>The search scope (<code>base</code>, <code>one</code> or <code>sub</code>)</li>
<li>The LDAP filter (optional, default : <code>(objectClass=*)</code>)</li>
</ul></p><p><strong>Example :</strong> <code>ldap:///ou=people,o=ls??one?(&(objectClass=lspeople)(mail=*@ls.com))</code></p>",
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'required' => 0,
      'default_value' => 'ldap:///ou=people,o=ls??one?(objectClass=lspeople)',
      'check_data' => array (
        'ldapSearchURI' => array(
          'msg' => "Invalid LDAP search URI.",
        ),
      ),
      'view' => 1,
      'rights' => array(
        'admin' => 'w',
      ),
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
      'dependAttrs' => array(
        'lsDynGroupMemberUidURI'
      ),
      'after_modify' => array(
        'updateDynGroupMembersCache',
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'lsDynGroupMemberUidURI' => array (
      'label' => 'Member search URI (UID)',
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'required' => 0,
      'generate_function' => 'generateDyngroupMemberUidURI',
      'rights' => array(
        'admin' => 'w',
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'lsDynGroupMemberDn' => array (
      'label' => 'Members',
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'html_options' => array(
        'selectable_object' => array(
          'object_type' => 'LSpeople',
          'display_name_format' => '%{cn} (%{dn})',
          'value_attribute' => 'dn',
        ),
      ),
      'required' => 0,
      'multiple' => 1,
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'w',
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'lsDynGroupMemberUid' => array (
      'label' => 'Members UID',
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'html_options' => array(
        'selectable_object' => array(
          'object_type' => 'LSpeople',
          'display_name_format' => '%{cn} (%{uid})',
          'value_attribute' => 'uid',
        )
      ),
      'required' => 0,
      'multiple' => 1,
      'view' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'w',
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'uniqueMember' => array (
      'label' => 'Members (cache)',
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'html_options' => array(
        'selectable_object' => array(
          array(
            'object_type' => 'LSpeople',
            'display_name_format' => '%{cn} (%{dn})',
            'value_attribute' => 'dn',
          ),
        ),
        'ordered' => true,
      ),
      'required' => 0,
      'multiple' => 1,
      'validation' => array (
        array (
          'object_type' => 'LSpeople',
          'basedn' => '%{val}',
          'result' => 1,
        ),
      ),
      'view' => 1,
      'rights' => array(
        'admin' => 'w',
        'admingroup' => 'w',
        'godfather' => 'w',
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'memberUid' => array (
      'label' => 'Members UID (cache)',
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'html_options' => array(
        'selectable_object' => array(
          array(
            'object_type' => 'LSpeople',
            'display_name_format' => '%{cn} (%{uid})',
            'value_attribute' => 'uid',
          ),
        ),
        'ordered' => true,
      ),
      'required' => 0,
      'multiple' => 1,
      'validation' => array (
        array (
          'object_type' => 'LSpeople',
          'filter' => '(uid=%{val})',
          'result' => 1,
        ),
      ),
      'view' => 1,
      'rights' => array(
        'admin' => 'w',
        'admingroup' => 'w',
        'godfather' => 'w',
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'description' => array (
      'label' => 'Description',
      'ldap_type' => 'ascii',
      'html_type' => 'textarea',
      'multiple' => 1,
      'rights' => array(
        'user' => 'r',
        'admin' => 'w',
        'godfather' => 'r',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
    ),
    /* ----------- end -----------*/

    /* ----------- start -----------*/
    'lsGodfatherDn' => array (
      'label' => 'Accountable(s)',
      'ldap_type' => 'ascii',
      'html_type' => 'select_object',
      'html_options' => array (
        'selectable_object' => array(
            'object_type' => 'LSpeople',
            'value_attribute' => 'dn',
        ),
      ),
      'validation' => array (
        array (
          'basedn' => '%{val}',
          'result' => 1,
          'msg' => "One or several of these users don't exist.",
        ),
      ),
      'multiple' => 0,
      'rights' => array(
        'admin' => 'w',
      ),
      'view' => 1,
      'form' => array (
        'modify' => 1,
        'create' => 1,
      ),
    ),
    /* ----------- end -----------*/

  ),
);
