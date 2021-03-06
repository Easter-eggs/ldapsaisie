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

// Messages d'erreur

// Support
LSerror :: defineError('MAILDIR_SUPPORT_01',
  ___("MAILDIR Support : Unable to load LSaddon::FTP.")
);
LSerror :: defineError('MAILDIR_SUPPORT_02',
  ___("MAILDIR Support : The constant %{const} is not defined.")
);

// Autres erreurs
LSerror :: defineError('MAILDIR_01',
  ___("MAILDIR : Error creating maildir on the remote server.")
);
LSerror :: defineError('MAILDIR_02',
  ___("MAILDIR : Error deleting the maildir on the remote server.")
);
LSerror :: defineError('MAILDIR_03',
  ___("MAILDIR : Error renaming the maildir on the remote server.")
);
LSerror :: defineError('MAILDIR_04',
  ___("MAILDIR : Error retrieving remote path of the maildir.")
);

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
      if(!LSsession :: loadLSaddon('ftp')) {
        LSerror :: addErrorCode('MAILDIR_SUPPORT_01');
        $retval=false;
      }
    }

    $MUST_DEFINE_CONST= array(
      'LS_MAILDIR_FTP_HOST',
      'LS_MAILDIR_FTP_USER',
      'LS_MAILDIR_FTP_MAILDIR_PATH',
      'LS_MAILDIR_FTP_MAILDIR_PATH_REGEX'
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( (!defined($const)) || (constant($const) == "")) {
        LSerror :: addErrorCode('MAILDIR_SUPPORT_02',$const);
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
  * @param[in] $dir Le chemin de la maildir. Si défini, la valeur ne sera pas
  *                 récupérée dans le ldapObject
  *
  * @retval string True ou false si il y a un problème durant la création de la Maildir
  */
  function createMaildirByFTP($ldapObject,$dir=null) {
    if (!$dir) {
      $dir = getMaildirPath($ldapObject);
      if (!$dir) {
        return;
      }
    }
    $dirs = array(
      $dir.'/cur',
      $dir.'/new',
      $dir.'/tmp'
    );
    if (!createDirsByFTP(LS_MAILDIR_FTP_HOST,LS_MAILDIR_FTP_PORT,LS_MAILDIR_FTP_USER,LS_MAILDIR_FTP_PWD,$dirs,LS_MAILDIR_FTP_MAILDIR_CHMOD)) {
      LSerror :: addErrorCode('MAILDIR_01');
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
  * @param[in] $dir Le chemin de la maildir. Si défini, la valeur ne sera pas
  *                 récupérée dans le ldapObject
  *
  * @retval string True ou false si il y a un problème durant la suppression de la Maildir
  */
  function removeMaildirByFTP($ldapObject,$dir=null) {
    if (!$dir) {
      $dir = getMaildirPath($ldapObject);
      if (!$dir) {
        return;
      }
    }
    if (!removeDirsByFTP(LS_MAILDIR_FTP_HOST,LS_MAILDIR_FTP_PORT,LS_MAILDIR_FTP_USER,LS_MAILDIR_FTP_PWD,$dir)) {
      LSerror :: addErrorCode('MAILDIR_02');
      return;
    }
    return true;
  }

 /**
  * Retourne le chemin distant de la maildir
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string Le chemin distant de la maildir ou false si il y a un problème
  */
  function getMaildirPath($ldapObject) {
    $dir = getFData(LS_MAILDIR_FTP_MAILDIR_PATH,$ldapObject,'getValue');

    if (LS_MAILDIR_FTP_MAILDIR_PATH_REGEX != "") {
      if (preg_match(LS_MAILDIR_FTP_MAILDIR_PATH_REGEX,$dir,$regs)) {
        $dir = $regs[1];
      }
      else {
        $dir = "";
      }
    }

    if ($dir=="") {
      LSerror :: addErrorCode('MAILDIR_04');
      return;
    }

    return $dir;
  }

  /**
  * Rename Maildir via FTP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $old L'ancien chemin de la maildir
  * @param[in] $new Le nouveau chemin de la maildir
  *
  * @retval string True ou false si il y a un problème durant le renomage de la Maildir
  */
  function renameMaildirByFTP($old,$new) {
    if (!renameDirByFTP(LS_MAILDIR_FTP_HOST,LS_MAILDIR_FTP_PORT,LS_MAILDIR_FTP_USER,LS_MAILDIR_FTP_PWD,$old,$new)) {
      LSerror :: addErrorCode('MAILDIR_03');
      return;
    }
    return true;
  }
