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
LSerror :: defineError('POSIX_SUPPORT_01',
  ___("POSIX Support : The constant %{const} is not defined.")
);

LSerror :: defineError('POSIX_SUPPORT_02',
  ___("POSIX Support : Unable to load LSaddon::FTP.")
);

// Autres erreurs
LSerror :: defineError('POSIX_01',
  ___("POSIX : The attribute %{dependency} is missing. Unable to forge the attribute %{attr}.")
);

 /**
  * Verification du support POSIX par ldapSaisie
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si POSIX est pleinement supporté, false sinon
  */
  function LSaddon_posix_support() {

    $retval=true;

    // Dependance de librairie
    if (!function_exists('createDirsByFTP')) {
      if(!LSsession :: loadLSaddon('ftp')) {
        LSerror :: addErrorCode('POSIX_SUPPORT_02');
        $retval=false;
      }
    }

    $MUST_DEFINE_CONST= array(
      'LS_POSIX_UID_ATTR',
      'LS_POSIX_UIDNUMBER_ATTR',
      'LS_POSIX_GIDNUMBER_ATTR',
      'LS_POSIX_UIDNUMBER_MIN_VAL',
      'LS_POSIX_GIDNUMBER_MIN_VAL',
      'LS_POSIX_HOMEDIRECTORY',
      'LS_POSIX_HOMEDIRECTORY_FTP_HOST',
      'LS_POSIX_HOMEDIRECTORY_FTP_PORT',
      'LS_POSIX_HOMEDIRECTORY_FTP_USER',
      'LS_POSIX_HOMEDIRECTORY_FTP_PWD',
      'LS_POSIX_HOMEDIRECTORY_FTP_PATH',
      'LS_POSIX_DN_TO_UID_PATTERN',
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( (!defined($const)) || (constant($const) == "")) {
        LSerror :: addErrorCode('POSIX_SUPPORT_O1',$const);
        $retval=false;
      }
    }

    return $retval;
  }

 /**
  * Generation de uidNumber
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval integer uidNumber ou false si il y a un problème durant la génération
  */
  function generate_uidNumber($ldapObject) {

    $objects = LSldap :: search (
      LS_POSIX_UIDNUMBER_ATTR.'=*',
      NULL,
      array(
        'attributes' => array(
          LS_POSIX_UIDNUMBER_ATTR
        )
      )
    );
    $uidNumber = LS_POSIX_UIDNUMBER_MIN_VAL;

    if (!is_array($objects))
      return;

    foreach($objects as $object) {
      if($object['attrs'][LS_POSIX_UIDNUMBER_ATTR] > $uidNumber)
        $uidNumber = $object['attrs'][LS_POSIX_UIDNUMBER_ATTR];
    }

    $uidNumber++;
    return $uidNumber;

  }

 /**
  * Generation de gidNumber
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval integer gidNumber ou false si il y a un problème durant la génération
  */
  function generate_gidNumber($ldapObject) {

    $objects = LSldap :: search (
      LS_POSIX_GIDNUMBER_ATTR.'=*',
      NULL,
      array(
        'attributes' => array(
          LS_POSIX_GIDNUMBER_ATTR
        )
      )
    );
    $gidNumber = LS_POSIX_GIDNUMBER_MIN_VAL;

    if (!is_array($objects))
      return;

    foreach($objects as $object) {
      if($object['attrs'][LS_POSIX_GIDNUMBER_ATTR] > $gidNumber)
        $gidNumber = $object['attrs'][LS_POSIX_GIDNUMBER_ATTR];
    }

    $gidNumber++;
    return $gidNumber;

  }

 /**
  * Generation de homeDirectory
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string homeDirectory ou false si il y a un problème durant la génération
  */
  function generate_homeDirectory($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_POSIX_UID_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('POSIX_01',array('dependency' => 'uid', 'attr' => 'homeDirectory'));
      return;
    }

    $uid = $ldapObject -> attrs[ LS_POSIX_UID_ATTR ] -> getValue();
    $home = LS_POSIX_HOMEDIRECTORY . $uid[0];
    return $home;

  }

 /**
  * Generation de homeDirectory
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string homeDirectory ou false si il y a un problème durant la génération
  */
  function createHomeDirectoryByFTP($ldapObject) {
    $dir = getFData(LS_POSIX_HOMEDIRECTORY_FTP_PATH,$ldapObject,'getValue');
    if (!createDirsByFTP(LS_POSIX_HOMEDIRECTORY_FTP_HOST,LS_POSIX_HOMEDIRECTORY_FTP_PORT,LS_POSIX_HOMEDIRECTORY_FTP_USER,LS_POSIX_HOMEDIRECTORY_FTP_PWD,$dir)) {
      LSerror :: addErrorCode('POSIX_02');
      return;
    }
    return true;
  }

 /**
  * Generate member attribute value from memberUid
  *
  * IMPORTANT : The attribute memberUid must be define in configuration
  * of the same object and must use HTML type select_object.
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject The LSldapObject
  *
  * @retval array|null array of member attribute values or null in case of error
  */
  function generateMemberFromMemberUid($ldapObject) {
    if ( get_class($ldapObject -> attrs[ 'memberUid' ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('POSIX_01',array('dependency' => 'memberUid', 'attr' => 'member'));
      return;
    }

    if ( get_class($ldapObject -> attrs[ 'member' ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('POSIX_01',array('dependency' => 'member', 'attr' => 'member'));
      return;
    }

    $obj_type=LSconfig::get('LSobjects.'.get_class($ldapObject).'.attrs.memberUid.html_options.selectable_object.object_type');
    if (empty($obj_type))
        return;

    $uids = $ldapObject -> attrs[ 'memberUid' ] -> getValue();
    $member = array();
    if (is_array($uids)) {
      foreach ( $uids as $uid ) {
        $member[]='uid='.$uid.','.LSconfig::get('LSobjects.'.$obj_type.'.container_dn').','.LSsession::getTopDn();
      }
    }
    return $member;

  }

 /**
  * Generate memberUid attribute value from uniqueMember
  *
  * IMPORTANT : The attribute uniqueMember must be define in configuration
  * of the same object.
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject The LSldapObject
  *
  * @retval array|null array of memberUid values or null in case of error
  */
  function generate_memberUidFromUniqueMember($ldapObject) {
    if ( get_class($ldapObject -> attrs[ 'memberUid' ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('POSIX_01',array('dependency' => 'memberUid', 'attr' => 'memberUid'));
      return;
    }

    if ( get_class($ldapObject -> attrs[ 'uniqueMember' ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('POSIX_01',array('dependency' => 'uniqueMember', 'attr' => 'memberUid'));
      return;
    }

    $dns = $ldapObject -> attrs[ 'uniqueMember' ] -> getValue();
    $uids = array();
    if (is_array($dns)) {
      foreach($dns as $dn) {
        if(preg_match(LS_POSIX_DN_TO_UID_PATTERN,$dn,$matches)) {
          $uids[]=$matches[1];
        }
      }
    }
    return $uids;
  }


/**
 * Generate shadowLastChange attribute value
 *
 * Just return a shadowLastChange value corresponding to current date.
 *
 * Important: this function is designed to be used with LSattr_html_date
 * & LSattr_ldap_shadowExpire classes. The returned value is just a timestamp
 * and it will be converted by LSattr_ldap_shadowExpire as raw LDAP value, that is,
 * divided by 86400.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $anything anything
 *
 * @retval integer The shadowLastChange attribute value (=current date)
 */
function generate_shadowLastChange($anything) {
  return time();
}
