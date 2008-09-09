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


 /**
  * Données de configuration pour le support POSIX
  */

      // Nom de l'attribut LDAP uid
      define('LS_POSIX_UID_ATTR','uid');

      // Nom de l'attribut LDAP uidNumber
      define('LS_POSIX_UIDNUMBER_ATTR','uidNumber');

      // Valeur minimum d'un uidNumber
      define('LS_POSIX_UIDNUMBER_MIN_VAL','100000');

      // Nom de l'attribut LDAP gidNumber
      define('LS_POSIX_GIDNUMBER_ATTR','gidNumber');

      // Valeur minimum d'un gidNumber
      define('LS_POSIX_GIDNUMBER_MIN_VAL','100000');

      // Dossier contenant les homes des utilisateurs (defaut: /home/)
      define('LS_POSIX_HOMEDIRECTORY','/home/');
      
      // Create homeDirectory by FTP - Host
      define('LS_POSIX_HOMEDIRECTORY_FTP_HOST','127.0.0.1');
      
      // Create homeDirectory by FTP - Port
      define('LS_POSIX_HOMEDIRECTORY_FTP_PORT',21);
      
      // Create homeDirectory by FTP - User
      define('LS_POSIX_HOMEDIRECTORY_FTP_USER','admin');
      
      // Create homeDirectory by FTP - Password
      define('LS_POSIX_HOMEDIRECTORY_FTP_PWD','password');
      
      // Create homeDirectory by FTP - Path
      define('LS_POSIX_HOMEDIRECTORY_FTP_PATH','%{homeDirectory}');


      // -- Message d'erreur --
      // Support
      $GLOBALS['LSerror_code']['POSIX_SUPPORT_01']= array (
        'msg' => _("POSIX Support : La constante %{const} n'est pas définie."),
        'level' => 'c'
      );
      
      $GLOBALS['LSerror_code']['POSIX_SUPPORT_02']= array (
        'msg' => _("POSIX Support : Impossible de charger LSaddons::FTP."),
        'level' => 'c'
      );

      // Autres erreurs
      $GLOBALS['LSerror_code']['POSIX_01']= array (
        'msg' => _("POSIX : L'attribut %{dependency} est introuvable. Impossible de générer l'attribut %{attr}."),
        'level' => 'c'
      );
      
 /**
  * Fin des données de configuration
  */


 /**
  * Verification du support POSIX par ldapSaisie
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si Samba est pleinement supporté, false sinon
  */
  function LSaddon_posix_support() {
    
    $retval=true;
    
    // Dependance de librairie
    if (!function_exists('createDirsByFTP')) {
      if(!$GLOBALS['LSsession'] -> loadLSaddon('ftp')) {
        $GLOBALS['LSerror'] -> addErrorCode('POSIX_SUPPORT_02');
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
      'LS_POSIX_HOMEDIRECTORY_FTP_PATH'
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( constant($const) == '' ) {
        $GLOBALS['LSerror'] -> addErrorCode('POSIX_SUPPORT_O1',$const);
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

    $objects = $GLOBALS['LSldap'] -> search (LS_POSIX_UIDNUMBER_ATTR.'=*');
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

    $objects = $GLOBALS['LSldap'] -> search (LS_POSIX_GIDNUMBER_ATTR.'=*');
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
      $GLOBALS['LSerror'] -> addErrorCode('POSIX_01',array('dependency' => 'uid', 'attr' => 'homeDirectory'));
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
      $GLOBALS['LSerror'] -> addErrorCode('POSIX_02');
      return;
    }
    return true;
  }

?>
