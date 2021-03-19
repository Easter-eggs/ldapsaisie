<?php

$GLOBALS['pwdPolicyAccountAttrs_LSform_layout'] = array (
	'label' => 'Password policy',
	'args' => array (
		'pwdPolicySubentry',
		'pwdChangedTime',
		'pwdGraceUseTime',
		'pwdFailureTime',
		'pwdUniqueAttempts',
		'pwdAccountLockedTime',
		'pwdReset',
		'pwdHistory',
	),
);

$GLOBALS['pwdPolicyAccountAttrs'] = array (
		/* ----------- start -----------*/
		'pwdChangedTime' => array (
			'label' => 'Password last change',
			'ldap_type' => 'date',
			'html_type' => 'date',
			'html_options' => array(
				'firstDayOfWeek' => 1,
			),
			'no_value_label' => 'Never',
			'rights' => array(
				'self' => 'r',
				'admin' => 'r',
				'LSsysaccount' => 'r',
			),
			'view' => 1,
		),
		/* ----------- end -----------*/

		/* ----------- start -----------*/
		'pwdGraceUseTime' => array (
			'label' => 'Grace use of the expired password',
			'help_info' => 'List the time of each succesful authentications after the password has expired.',
			'ldap_type' => 'date',
			'html_type' => 'date',
			'html_options' => array(
				'firstDayOfWeek' => 1,
			),
			'no_value_label' => 'Never',
			'multiple' => 1,
			'rights' => array(
				'self' => 'r',
				'admin' => 'r',
				'LSsysaccount' => 'r',
			),
			'view' => 1,
		),
		/* ----------- end -----------*/

		/* ----------- start -----------*/
		'pwdFailureTime' => array (
			'label' => 'Last failed connection attempts',
			'ldap_type' => 'date',
			'ldap_options' => array(
				'format' => 'YmdHis.uO',
			),
			'html_type' => 'date',
			'html_options' => array(
				'firstDayOfWeek' => 1,
			),
			'no_value_label' => 'No fail since last successful connection',
			'multiple' => 1,
			'rights' => array(
				'self' => 'r',
				'admin' => 'r',
				'LSsysaccount' => 'r',
			),
			'view' => 1,
		),
		/* ----------- end -----------*/

		/* ----------- start -----------*/
		'pwdAccountLockedTime' => array (
			'label' => 'Locked time',
			'help_info' => 'Indicates the time the account was locked time. Delete this date and set <em>pwdReset</em> attribute to unlock the account.',
			'ldap_type' => 'date',
			'html_type' => 'date',
			'html_options' => array(
				'firstDayOfWeek' => 1,
			),
			'no_value_label' => 'Not locked',
			'rights' => array(
				'self' => 'r',
				'admin' => 'w',
				'LSsysaccount' => 'r',
			),
			'view' => 1,
			'form' => array (
				'modify' => 1,
			),
		),
		/* ----------- end -----------*/

		/* ----------- start -----------*/
		'pwdHistory' => array (
			'label' => 'Passwords in history',
			'ldap_type' => 'pwdHistory',
			'html_type' => 'jsonCompositeAttribute',
			'html_options' => array (
				'components' => array (
					'time' => array (
						'label' => 'Date added to history',
						'type' => 'text',
						'required' => true,
						'multiple' => false,
					),
					'syntaxOID' => array (
						'label' => 'Syntax OID',
						'type' => 'text',
						'required' => true,
						'multiple' => false,
					),
					'length' => array (
						'label' => 'Length',
						'type' => 'text',
						'required' => true,
						'multiple' => false,
					),
					'hashed_password' => array (
						'label' => 'Hashed password',
						'type' => 'text',
						'required' => true,
						'multiple' => false,
					),
				),
				'fullWidth' => true,
			),
			'no_value_label' => 'History is empty.',
			'multiple' => 1,
			'rights' => array(
				'admin' => 'r',
			),
			'view' => 1,
		),
		/* ----------- end -----------*/

		/* ----------- start -----------*/
		'pwdReset' => array (
			'label' => 'User must change its password before next connection',
			'help_info' => 'Set this attribute and delete <em>pwdAccountLockedTime</em> attribute value to unlock the account.',
			'ldap_type' => 'boolean',
			'html_type' => 'boolean',
			'no_value_label' => 'Not set',
			'rights' => array(
				'admin' => 'w',
				'LSsysaccount' => 'r',
			),
			'view' => 1,
			'form' => array (
				'modify' => 1,
			),
		),
		/* ----------- end -----------*/

		/* ----------- start -----------*/
		'pwdPolicySubentry' => array (
			'label' => 'Password policy',
			'ldap_type' => 'ascii',
			'html_type' => 'select_object',
			'html_options' => array(
				'selectable_object' => array(
					'object_type' => 'pwdPolicy',
					'display_name_format' => '%{cn}',
					'value_attribute' => 'dn',
				),
			),
			'no_value_label' => 'Default policy',
			'validation' => array (
				array (
					'object_type' => 'pwdPolicy',
					'basedn' => '%{val}',
					'result' => 1,
				),
			),
			'view' => 1,
			'rights' => array(
				'admin' => 'w',
				'LSsysaccount' => 'r',
			),
			'form' => array (
				'modify' => 1,
				'create' => 1,
			),
		),
		/* ----------- end -----------*/
);
