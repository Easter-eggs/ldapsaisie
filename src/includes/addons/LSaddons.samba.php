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

// Messages d'erreur

// Support
LSerror :: defineError('SAMBA_SUPPORT_01',
  ___("SAMBA Support: Unable to load smbHash class.")
);
LSerror :: defineError('SAMBA_SUPPORT_02',
  ___("SAMBA Support: The constant %{const} is not defined.")
);

LSerror :: defineError('SAMBA_SUPPORT_03',
  ___("SAMBA Support: The constants LS_SAMBA_SID_BASE_USER and LS_SAMBA_SID_BASE_GROUP must'nt have the same parity to keep SambaSID's unicity.")
);

// Autres erreurs
LSerror :: defineError('SAMBA_01',
  ___("SAMBA Support: The attribute %{dependency} is missing. Unable to forge the attribute %{attr}.")
);
LSerror :: defineError('SAMBA_02',
  ___("SAMBA Support: Can't get the sambaUnixIdPool object.")
);
LSerror :: defineError('SAMBA_03',
  ___("SAMBA Support: Error modifying the sambaUnixIdPool object.")
);
LSerror :: defineError('SAMBA_04',
  ___("SAMBA Support: The %{attr} of the sambaUnixIdPool object is incorrect.")
);

// CONSTANTES

// Le temps infini au sens NT
define('LS_SAMBA_INFINITY_TIME',2147483647);

/**
 * Check LdapSaisie Samba support
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval boolean True if Samba is fully supported, false otherwise
 */
function LSaddon_samba_support() {

  $retval=true;

  // Dependance de librairie
  if ( !class_exists('smbHash') ) {
    if ( !LSsession::includeFile(LS_LIB_DIR . 'class.smbHash.php') ) {
      LSerror :: addErrorCode('SAMBA_SUPPORT_01');
      $retval=false;
    }
  }


  $MUST_DEFINE_CONST= array(
    'LS_SAMBA_DOMAIN_SID',
    'LS_SAMBA_DOMAIN_NAME',
    'LS_SAMBA_HOME_PATH_FORMAT',
    'LS_SAMBA_PROFILE_PATH_FORMAT',
    'LS_SAMBA_DOMAIN_OBJECT_DN',
    'LS_SAMBA_SID_BASE_USER',
    'LS_SAMBA_SID_BASE_GROUP',
    'LS_SAMBA_UIDNUMBER_ATTR',
    'LS_SAMBA_GIDNUMBER_ATTR',
    'LS_SAMBA_USERPASSWORD_ATTR'
  );

  foreach($MUST_DEFINE_CONST as $const) {
    if ( (!defined($const)) || (constant($const) == "")) {
      LSerror :: addErrorCode('SAMBA_SUPPORT_02',$const);
      $retval=false;
    }
  }

  // Check LS_SAMBA_SID_BASE_USER & LS_SAMBA_SID_BASE_GROUP values for SID integrity
  if ( (LS_SAMBA_SID_BASE_USER % 2) == (LS_SAMBA_SID_BASE_GROUP % 2) ) {
    LSerror :: addErrorCode('SAMBA_SUPPORT_03');
    $retval=false;
  }

  return $retval;
}

/**
 * Generate sambaSID value
 *
 * Generation rule:
 *   Number   = [UNIX attribute ($unix_attr) value] * 2 + $base_number
 *   sambaSID = LS_SAMBA_DOMAIN_SID-Number
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 * @param[in] $ldapObject LSldapObjet The LSldapObjet object
 * @param[in] $unix_attr string The UNIX attribute name
 * @param[in] $base_number integer The base number value
 *
 * @retval string SambaSID ou false si il y a un problème durant la génération
 */
function generate_sambaSID($ldapObject, $unix_attr, $base_number) {
  if ( get_class($ldapObject -> attrs[ $unix_attr ]) != 'LSattribute' ) {
    LSerror :: addErrorCode(
      'SAMBA_01',
      array(
        'dependency' => $unix_attr,
        'attr' => 'sambaSID'
      )
    );
    return;
  }

  $unix_id_attr_val = $ldapObject -> getValue($unix_attr, true, null);
  $object_sid = $unix_id_attr_val * 2 + $base_number;
  return LS_SAMBA_DOMAIN_SID . '-' . $object_sid;
}

/**
 * Generate user sambaSID
 *
 *   Number   = LS_SAMBA_UIDNUMBER_ATTR * 2 + LS_SAMBA_SID_BASE_USER
 *   sambaSID = LS_SAMBA_DOMAIN_SID-Number
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval string User SambaSID value on success, false otherwise
 */
function generate_user_sambaSID($ldapObject) {
  return generate_sambaSID($ldapObject, LS_SAMBA_UIDNUMBER_ATTR, LS_SAMBA_SID_BASE_USER);
}

 /**
  * Generate user sambaSID
  *
  * Note: old-name of the function keep for retro-compatibility
  *
  *   Number   = LS_SAMBA_UIDNUMBER_ATTR * 2 + LS_SAMBA_SID_BASE_USER
  *   sambaSID = LS_SAMBA_DOMAIN_SID-Number
  *
  * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval string User SambaSID value on success, false otherwise
  */
function generate_sambaUserSID($ldapObject) {
  LSerror :: addErrorCode(
    'LSsession_27',
    array(
      'old' => 'generate_sambaUserSID()',
      'new' => 'generate_user_sambaSID()',
      'context' => LSlog :: get_debug_backtrace_context(),
    )
  );
  return generate_user_sambaSID($ldapObject);
}

/**
 * Generate group sambaSID
 *
 *   Number   = LS_SAMBA_GIDNUMBER_ATTR * 2 + LS_SAMBA_SID_BASE_GROUP
 *   sambaSID = LS_SAMBA_DOMAIN_SID-Number
 *
 * @param[in] $ldapObject LSldapObjet The group LSldapObjet object
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval string Group SambaSID value on success, false otherwise
 */
function generate_group_sambaSID($ldapObject) {
  return generate_sambaSID($ldapObject, LS_SAMBA_GIDNUMBER_ATTR, LS_SAMBA_SID_BASE_GROUP);
}

 /**
  * Generate group sambaSID
  *
  * Note: old-name of the function keep for retro-compatibility. An error
  * message is raised when this function is used.
  *
  * @param[in] $ldapObject LSldapObjet The group LSldapObjet object
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval string Group SambaSID value on success, false otherwise
  */
function generate_sambaGroupSID($ldapObject) {
  LSerror :: addErrorCode(
    'LSsession_27',
    array(
      'old' => 'generate_sambaGroupSID()',
      'new' => 'generate_group_sambaSID()',
      'context' => LSlog :: get_debug_backtrace_context(),
    )
  );
  return generate_group_sambaSID($ldapObject);
}

/**
 * Generate sambaPrimaryGroupSID
 *
 *   Number   = LS_SAMBA_GIDNUMBER_ATTR * 2 + LS_SAMBA_SID_BASE_GROUP
 *   sambaSID = LS_SAMBA_DOMAIN_SID-Number
 *
 * @param[in] $ldapObject LSldapObjet The LSldapObjet object
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval string The sambaPrimaryGroupSID value on success, false otherwise
 */
function generate_sambaPrimaryGroupSID($ldapObject) {
  return generate_sambaSID($ldapObject, LS_SAMBA_GIDNUMBER_ATTR, LS_SAMBA_SID_BASE_GROUP);
}


 /**
  * Generation de sambaNTPassword
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
  *
  * @retval string|false sambaNTPassword value on success, false otherwise
  */
  function generate_sambaNTPassword($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SAMBA_01',array('dependency' => LS_SAMBA_USERPASSWORD_ATTR, 'attr' => 'sambaNTPassword'));
      return;
    }

    $password = $ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ] -> ldap -> getClearPassword();
    $sambapassword = new smbHash;
    $sambaNTPassword = $sambapassword -> nthash($password);

    if($sambaNTPassword == '') {
      return;
    }
    return $sambaNTPassword;
  }

 /**
  * Generation de sambaLMPassword
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
  *
  * @retval string|false sambaLMPassword value on success, false otherwise
  */
  function generate_sambaLMPassword($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SAMBA_01',array('dependency' => LS_SAMBA_USERPASSWORD_ATTR, 'attr' => 'sambaLMPassword'));
      return;
    }

    $password = $ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ] -> ldap -> getClearPassword();
    $sambapassword = new smbHash;
    $sambaLMPassword = $sambapassword -> lmhash($password);

    if($sambaLMPassword == '') {
      return;
    }
    return $sambaLMPassword;
  }

/**
 * Generate UNIX ID value from sambaUnixIdPool object
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $attr string The sambaUnixIdPool attribute name that contain next ID value
 *
 * @retval integer UNIX ID value on succes, false otherwise
 */
function get_samba_unix_pool_next_id($attr) {
  $unix_id_pool_dn = (constant('LS_SAMBA_UNIX_ID_POOL_DN')?LS_SAMBA_UNIX_ID_POOL_DN:LS_SAMBA_DOMAIN_OBJECT_DN);
  $unix_id_pool = LSldap :: getLdapEntry ($unix_id_pool_dn);
  if ($unix_id_pool === false) {
    LSerror :: addErrorCode('SAMBA_02');
    return;
  }

  $next_id = $unix_id_pool->getValue($attr, 'single');
  if (Net_LDAP2::isError($next_id) || $next_id == 0) {
    LSerror :: addErrorCode('SAMBA_04', $attr);
    return;
  }

  $unix_id_pool->replace(array($attr => ($next_id+1)));
  $res = $unix_id_pool->update();
  if(!Net_LDAP2::isError($res)) {
    return $next_id;
  }
  else {
    LSerror :: addErrorCode('SAMBA_03');
    return;
  }
}

/**
 * Generate uidNumber using sambaUnixIdPool object
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval integer|false uidNumber value on success, false otherwise
 */
function generate_samba_uidNumber($ldapObject) {
  return get_samba_unix_pool_next_id('uidNumber');
}

/**
 * Generate uidNumber using sambaUnixIdPool object
 *
 * Note: old-name of the function keep for retro-compatibility. An error
 * message is raised when this function is used.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval integer|false uidNumber value on success, false otherwise
 */
function generate_uidNumber_withSambaDomainObject($ldapObject) {
  LSerror :: addErrorCode(
    'LSsession_27',
    array(
      'old' => 'generate_uidNumber_withSambaDomainObject()',
      'new' => 'generate_samba_uidNumber()',
      'context' => LSlog :: get_debug_backtrace_context(),
    )
  );
  return generate_samba_uidNumber($ldapObject);
}

/**
 * Generate gidNumber using sambaUnixIdPool object
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval integer|false gidNumber value on success, false otherwise
 */
function generate_samba_gidNumber($ldapObject) {
  return get_samba_unix_pool_next_id('gidNumber');
}

/**
 * Generate gidNumber using sambaUnixIdPool object
 *
 * Note: old-name of the function keep for retro-compatibility. An error
 * message is raised when this function is used.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval integer|false gidNumber value on success, false otherwise
 */
function generate_gidNumber_withSambaDomainObject($ldapObject) {
  LSerror :: addErrorCode(
    'LSsession_27',
    array(
      'old' => 'generate_gidNumber_withSambaDomainObject()',
      'new' => 'generate_samba_gidNumber()',
      'context' => LSlog :: get_debug_backtrace_context(),
    )
  );
  return generate_samba_gidNumber($ldapObject);
}

/**
 * Return NT infinity time
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $anything anything
 *
 * @retval integer NT infinity time
 */
function get_samba_infinity_time($anything=null) {
  return LS_SAMBA_INFINITY_TIME;
}

/**
 * Return NT infinity time
 *
 * Note: old-name of the function keep for retro-compatibility. An error
 * message is raised when this function is used.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $anything anything
 *
 * @retval integer NT infinity time
 */
function return_sambaInfinityTime($anything=null) {
  LSerror :: addErrorCode(
    'LSsession_27',
    array(
      'old' => 'return_sambaInfinityTime()',
      'new' => 'get_samba_infinity_time()',
      'context' => LSlog :: get_debug_backtrace_context(),
    )
  );
  return get_samba_infinity_time($anything);
}

/**
 * Generate sambaPwdLastSet attribute value
 *
 * Just return current timestamp.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $anything anything
 *
 * @retval integer The sambaPwdLastSet attribute value (=current timestamp)
 */
function generate_sambaPwdLastSet($anything) {
  return time();
}

/**
 * Generate sambaDomainName attribute value
 *
 * Just return samba domain name.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $anything anything
 *
 * @retval string The sambaDomainName attribute value
 */
function generate_sambaDomainName($anything) {
  return LS_SAMBA_DOMAIN_NAME;
}

/**
 * Generate sambaHomePath attribute value
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval string The sambaHomePath attribute value
 */
function generate_sambaHomePath($ldapObject) {
  return $ldapObject -> getFData(LS_SAMBA_HOME_PATH_FORMAT);
}

/**
 * Generate sambaProfilePath attribute value
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval string The sambaProfilePath attribute value
 */
function generate_sambaProfilePath($ldapObject) {
  return $ldapObject -> getFData(LS_SAMBA_PROFILE_PATH_FORMAT);
}

/**
 * Generate shadowExpire attribute value from sambaPwdMustChange
 * attribute.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval string The shadowExpire attribute value
 */
function generate_shadowExpire_from_sambaPwdMustChange($ldapObject) {
  $time = $ldapObject -> getValue('sambaPwdMustChange', true, null);
  if ($time)
    return str_val(round(int_val($time)/86400));
  return '';
}

/**
 * Generate timestamp from shadowExpire attribute value
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval string Timestamp corresponding to shadowExpire
 */
function generate_timestamp_from_shadowExpire($ldapObject) {
  $days = $ldapObject -> getValue('shadowExpire', true, null);
  if ($days)
    return str_val(int_val($days) * 86400);
  return '';
}

/**
 * Generate sambaPwdMustChange attribute value from shadowExpire
 * attribute.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval string The sambaPwdMustChange attribute value
 */
function generate_sambaPwdMustChange_from_shadowExpire($ldapObject) {
  return generate_timestamp_from_shadowExpire($ldapObject);
}

/**
 * Generate sambaKickoffTime attribute value from shadowExpire
 * attribute.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject LSldapObjet The user LSldapObjet object
 *
 * @retval string The sambaKickoffTime attribute value
 */
function generate_sambaKickoffTime_from_shadowExpire($ldapObject) {
  return generate_timestamp_from_shadowExpire($ldapObject);
}
