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
  * Données de configuration pour le support Maildir
  */

      // Serveur FTP - Host
      define('LS_MAILDIR_FTP_HOST','127.0.0.1');

      // Serveur FTP - Port
      define('LS_MAILDIR_FTP_PORT',21);

      // Serveur FTP - User
      define('LS_MAILDIR_FTP_USER','vmail');

      // Serveur FTP - Passorwd
      define('LS_MAILDIR_FTP_PWD','password'); 
      
      // Serveur FTP - Maildir Path
      define('LS_MAILDIR_FTP_MAILDIR_PATH','%{uid}');

      // Message d'erreur

      $GLOBALS['LSerror_code']['MAILDIR_SUPPORT_01']= array (
        'msg' => _("MAILDIR Support : Impossible de charger LSaddons::FTP."),
        'level' => 'c'
      );
      $GLOBALS['LSerror_code']['MAILDIR_SUPPORT_02']= array (
        'msg' => _("MAILDIR Support : La constante %{const} n'est pas définie."),
        'level' => 'c'
      );
      $GLOBALS['LSerror_code']['MAILDIR_01']= array (
        'msg' => _("MAILDIR Support : Erreur durant la création de la maildir sur le serveur distant."),
        'level' => 'c'
      );
      $GLOBALS['LSerror_code']['MAILDIR_02']= array (
        'msg' => _("MAILDIR Support : Erreur durant la création de la maildir sur le serveur distant."),
        'level' => 'c'
      );
      
 /**
  * Fin des données de configuration
  */


 /**
  * Verification du support Maildir par ldapSaisie
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si Maildir est pleinement supporté, false sinon
  */
  function LSaddon_maildir_support() {
$retval=true;

    // Dependance de librairie
    if (!function_exists('createDirsByFTP')) {
      if(!$GLOBALS['LSsession'] -> loadLSaddon('ftp')) {
        $GLOBALS['LSerror'] -> addErrorCode('MAILDIR_SUPPORT_01');
        $retval=false;
      }
    }

    $MUST_DEFINE_CONST= array(
      'LS_MAILDIR_FTP_HOST',
      'LS_MAILDIR_FTP_USER',
      'LS_MAILDIR_FTP_MAILDIR_PATH'
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( constant($const) == '' ) {
        $GLOBALS['LSerror'] -> addErrorCode('MAILDIR_SUPPORT_02',$const);
        $retval=false;
      }
    }
    return $retval;
  }

 /**
  * Creation d'une Maildir via FTP
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  * 
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string True ou false si il y a un problème durant la création de la Maildir
  */
  function createMaildirByFTP($ldapObject) {
    $dir = getFData(LS_MAILDIR_FTP_MAILDIR_PATH,$ldapObject,'getValue');
    $dirs = array(
      $dir.'/cur',
      $dir.'/new',
      $dir.'/tmp'
    );
    if (!createDirsByFTP(LS_MAILDIR_FTP_HOST,LS_MAILDIR_FTP_PORT,LS_MAILDIR_FTP_USER,LS_MAILDIR_FTP_PWD,$dirs)) {
      $GLOBALS['LSerror'] -> addErrorCode('MAILDIR_01');
      return;
    }
    return true;
  }
  
  /**
  * Suppression d'une Maildir via FTP
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  * 
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string True ou false si il y a un problème durant la suppression de la Maildir
  */
  function removeMaildirByFTP($ldapObject) {
    $dir = getFData(LS_MAILDIR_FTP_MAILDIR_PATH,$ldapObject,'getValue');
    if (!removeDirsByFTP(LS_MAILDIR_FTP_HOST,LS_MAILDIR_FTP_PORT,LS_MAILDIR_FTP_USER,LS_MAILDIR_FTP_PWD,$dir)) {
      $GLOBALS['LSerror'] -> addErrorCode('MAILDIR_02');
      return;
    }
    return true;
  } 
