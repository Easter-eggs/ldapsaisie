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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA	02111-1307, USA.

******************************************************************************/

$GLOBALS['LSobjects']['pwdPolicy'] = array (
	'objectclass' => array(
		'top',
		'device',
		'pwdPolicy',
		'pwdPolicyChecker',
	),
	'rdn' => 'cn',
	'container_dn' => 'ou=ppolicies',

	'display_name_format' => '%{cn}',
	'displayAttrName' => true,
	'label' => 'Password policies',

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
	),

	// LSform
	'LSform' => array (
		'ajaxSubmit' => 1,
		// Layout
		'layout' => array (
			'general' => array(
				'label' => 'General information',
				'args' => array (
					'cn',
					'pwdAttribute',
					'pwdAllowUserChange',
					'pwdSafeModify',
					'pwdInHistory',
				),
			),
			'quality' => array (
				'label' => 'Password quality',
				'args' => array (
					'pwdCheckQuality',
					'pwdMinLength',
					'pwdCheckModule',
				),
			),
			'expiration' => array (
				'label' => 'Password expiration',
				'args' => array (
					'pwdMaxAge',
					'pwdMinAge',
					'pwdExpireWarning',
					'pwdGraceAuthNLimit',
				),
			),
			'bruteforce' => array (
				'label' => 'Brute-force attacks protection',
				'args' => array (
					'pwdLockout',
					'pwdMaxFailure',
					'pwdMaxRecordedFailure',
					'pwdLockoutDuration',
					'pwdFailureCountInterval',
					'pwdMustChange',
				),
			),
		) // fin Layout
	), // fin LSform

	'LSsearch' => array (
		'attrs' => array (
			'cn',
		),
		'params' => array (
			'sortBy' => 'displayName',
		),
	),

	// Attributes
	'attrs' => array (

		/* ----------- start -----------*/
		'cn' => array (
			'label' => 'Name',
			'ldap_type' => 'ascii',
			'html_type' => 'text',
			'required' => 1,
			'validation' => array (
				array (
					'filter' => 'cn=%{val}',
					'object_type' => 'pwdPolicy',
					'result' => 0,
					'msg' => 'This name is already used.',
				),
			),
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

		/* ----------- start -----------*/
		'pwdAttribute' => array (
			'label' => 'Password attribute',
			'ldap_type' => 'ascii',
			'html_type' => 'text',
			'required' => 1,
			'default_value' => 'userPassword',
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

		/* ----------- start -----------*/
		'pwdAllowUserChange' => array (
			'label' => 'User can change its password',
			'ldap_type' => 'boolean',
			'html_type' => 'boolean',
			'no_value_label' => 'Yes (default)',
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

		/* ----------- start -----------*/
		'pwdSafeModify' => array (
			'label' => 'User must provide its old password to change it',
			'help_info' => 'Default: No.',
			'ldap_type' => 'boolean',
			'html_type' => 'boolean',
			'no_value_label' => 'No (default)',
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

		/* ----------- start -----------*/
		'pwdInHistory' => array (
			'label' => 'Number of old passwords kept in history',
			'help_info' => "User can't reused an old password in its history. Default: zero.",
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'No history (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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

		/*
		 *******************************************************************************************
		 * Check password quality
		 *******************************************************************************************
		 */

		/* ----------- start -----------*/
		'pwdCheckQuality' => array (
			'label' => 'Check password quality',
			'ldap_type' => 'ascii',
			'html_type' => 'select_box',
			'html_options' => array (
				'possible_values' => array (
					'0' => 'Disabled (default)',
					'1' => "If password is already hashed (can't check it), accept it",
					'2' => "If password is already hashed (can't check it), refuse it",
				),
			),
			'no_value_label' => 'Disabled (default)',
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

		/* ----------- start -----------*/
		'pwdMinLength' => array (
			'label' => 'Minimum length a password',
			'help_info' => "If zero (default), no minimum length. Note: if password is provided already hashed, this check could not be performed and the policy define by the attribute <em>pwdCheckQuality</em> is applied.",
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'No minimum length (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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

		/* ----------- start -----------*/
		'pwdCheckModule' => array (
			'label' => 'Check OpenLDAP module to used',
			'help_info' => '<strong>Used with caution !</strong> The name of the OpenLDAP module to used to check the password quality.',
			'ldap_type' => 'ascii',
			'html_type' => 'text',
			'no_value_label' => 'Only length check (default)',
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

		/*
		 *******************************************************************************************
		 * Password expiration
		 *******************************************************************************************
		 */

		/* ----------- start -----------*/
		'pwdMaxAge' => array (
			'label' => 'Maximum validity duration of a password',
			'help_info' => "In second. After this delay, the password will expired and must be changed. If zero (default), no password expiration.",
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'No password expiration (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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

		/* ----------- start -----------*/
		'pwdMinAge' => array (
			'label' => 'Minimum time between two password changes',
			'help_info' => "In second. If zero (default), no minimum time.",
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'No minimum time (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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

		/* ----------- start -----------*/
		'pwdExpireWarning' => array (
			'label' => 'Warning delay before password expiration',
			'help_info' => 'In seconds. Put zero to disabled.',
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'No warning (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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

		/* ----------- start -----------*/
		'pwdGraceAuthNLimit' => array (
			'label' => 'Grace delay after password expiration',
			'help_info' => "Number of time that a user can log in with its expired password. If zero (default), no grace delay and the user can't log in with its expired password.",
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'No grace delay (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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


		/*
		 *******************************************************************************************
		 * Blocking brute-force attacks,
		 *******************************************************************************************
		 */


		/* ----------- start -----------*/
		'pwdLockout' => array (
			'label' => 'Lock account after too many login failures',
			'help_info' => 'The limit is configured using <em>pwdMaxFailure</em> attribute.',
			'ldap_type' => 'boolean',
			'html_type' => 'boolean',
			'no_value_label' => 'No (default)',
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

		/* ----------- start -----------*/
		'pwdMaxFailure' => array (
			'label' => 'Maximum allowed login failures',
			'help_info' => "After the number of login failures, the action defined by attribute <em>pwdLockout</em> will be executed. If zero (default), no limit.",
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'No limit (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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

		/* ----------- start -----------*/
		'pwdMaxRecordedFailure' => array (
			'label' => 'Maximum number of failed connections to store',
			'help_info' => "Define the maximum number of failed connections to store for a user. If zero (default), the <em>Maximum allowed login failures (pwdMaxFailure)</em> value is used, or 5 if it's also zero.",
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'Default (see pwdMaxFailure if defined, otherwise: 5)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('min' => 0),
				),
			),
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

		/* ----------- start -----------*/
		'pwdLockoutDuration' => array (
			'label' => 'Lock duration of an account',
			'help_info' => "In second. After this delay, the account will be automatically unlocked. If zero (default), the account will be locked until an administrator manually unlock it.",
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'Until an administrator manually unlock it (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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

		/* ----------- start -----------*/
		'pwdFailureCountInterval' => array (
			'label' => 'Delay before reseting authentication fail count',
			'help_info' => 'In seconds. After this delay, authentication fail count will be reseted if no fail occured in the meantime. If zero (default), authentication fail count will be reseted only after a successful connection.',
			'ldap_type' => 'numeric',
			'html_type' => 'text',
			'no_value_label' => 'After successful connection (default)',
			'check_data' => array (
				'integer' => array(
					'msg' => "Must be a positive integer.",
					'params' => array('positive' => true),
				),
			),
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

		/* ----------- start -----------*/
		'pwdMustChange' => array (
			'label' => 'User must change its password after administrator unlock it',
			'help_info' => 'Default: No. Note: if the <em>pwdReset</em> attribute of the account is defined, its value override this parameter.',
			'ldap_type' => 'boolean',
			'html_type' => 'boolean',
			'no_value_label' => 'No (default)',
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

	) // Fin args
);
