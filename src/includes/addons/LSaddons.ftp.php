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
LSerror :: defineError('FTP_SUPPORT_01',
  ___("FTP Support : Pear::Net_FTP is missing.")
);

LSerror :: defineError('FTP_SUPPORT_02',
  ___("FTP Support : The constant %{const} is not defined.")
);


// Autres erreurs
LSerror :: defineError('FTP_00',
  ___("Net_FTP Error : %{msg}")
);

LSerror :: defineError('FTP_01',
  ___("FTP Support : Unable to connect to FTP Server (Step : %{step}).")
);
LSerror :: defineError('FTP_02',
  ___("FTP Support : Unable to make directory %{dir} on the remote server.")
);
LSerror :: defineError('FTP_03',
  ___("FTP Support : Unable to delete directory %{dir} on the remote server.")
);
LSerror :: defineError('FTP_04',
  ___("FTP Support : Unable to modify rights on the directory %{dir} on the remote server.")
);
LSerror :: defineError('FTP_05',
  ___("FTP Support : Unable to rename folder from %{old} to %{new} on the remote server.")
);

 /**
  * Verification du support FTP par ldapSaisie
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si FTP est pleinement supporté, false sinon
  */
  function LSaddon_ftp_support() {
    $retval=true;

    // Dependance de librairie
    if (!class_exists('Net_FTP')) {
      if (!defined('NET_FTP')) {
        LSerror :: addErrorCode('FTP_SUPPORT_02','NET_FTP');
        $retval=false;
      } else if(!LSsession::includeFile(NET_FTP, true)) {
        LSerror :: addErrorCode('FTP_SUPPORT_01');
        $retval=false;
      }
    }

    return $retval;
  }



 /**
  * Connexion a un serveur FTP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $host string Le nom ou l'IP du serveur FTP
  * @param[in] $port string Le port de connexion au serveur ftp
  * @param[in] $user string Le nom d'utilidateur de connexion
  * @param[in] $pwd  string Le mot de passe de connexion
  *
  * @retval mixed Net_FTP object en cas de succès, false sinon
  */
  function connectToFTP($host,$port,$user,$pwd) {
    $cnx = new Net_FTP();
    $do = $cnx -> connect($host,$port);
    if (! $do instanceof PEAR_Error){
      $do = $cnx -> login($user,$pwd);
      if (! $do instanceof PEAR_Error) {
        return $cnx;
      }
      else {
        LSerror :: addErrorCode('FTP_01',"2");
        LSerror :: addErrorCode('FTP_00',$do -> getMessage());
        return;
      }
    }
    else {
      LSerror :: addErrorCode('FTP_01',"1");
      LSerror :: addErrorCode('FTP_00',$do -> getMessage());
      return;
    }
  }

 /**
  * Creation d'un ou plusieurs dossiers via FTP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $host string Le nom ou l'IP du serveur FTP
  * @param[in] $port string Le port de connexion au serveur ftp
  * @param[in] $user string Le nom d'utilidateur de connexion
  * @param[in] $pwd  string Le mot de passe de connexion
  * @param[in] $dirs array ou string Le(s) dossier(s) à ajouter
  *
  * @retval string True ou false si il y a un problème durant la création du/des dossier(s)
  */
  function createDirsByFTP($host,$port,$user,$pwd,$dirs,$chmod=NULL) {
    $cnx = connectToFTP($host,$port,$user,$pwd);
    if (! $cnx){
      return;
    }
    if (!is_array($dirs)) {
      $dirs = array($dirs);
    }
    foreach($dirs as $dir) {
      $do = $cnx -> mkdir($dir,true);
      if ($do instanceof PEAR_Error) {
        LSerror :: addErrorCode('FTP_02',$dir);
        LSerror :: addErrorCode('FTP_00',$do -> getMessage());
        return;
      }
      if ($chmod) {
        $do = $cnx -> chmod($dir,$chmod);
        if ($do instanceof PEAR_Error) {
          LSerror :: addErrorCode('FTP_04',$dir);
          LSerror :: addErrorCode('FTP_00',$do -> getMessage());
        }
      }
    }
    return true;
  }

 /**
  * Suppression d'un ou plusieurs dossiers via FTP
  *
  * Note : Attention : suppression récursive. Cela veut dire que les sous-dossiers
  * lister par un LS FTP seront supprimé d'abord. Attention : Si votre serveur
  * FTP est configuré pour caché certains fichiers ou dossiers (dont le nom
  * commence par un '.' par exempl), ces fichiers ne seront pas supprimés et la
  * suppression du dossier parent échoura.
  *
  * Pour VsFTPd : Ajouter force_dot_files=1 dans la configuration.
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $host string Le nom ou l'IP du serveur FTP
  * @param[in] $port string Le port de connexion au serveur ftp
  * @param[in] $user string Le nom d'utilidateur de connexion
  * @param[in] $pwd  string Le mot de passe de connexion
  * @param[in] $dirs array ou string Le(s) dossier(s) à supprimer
  *
  * @retval string True ou false si il y a un problème durant la suppression du/des dossier(s)
  */
  function removeDirsByFTP($host,$port,$user,$pwd,$dirs) {
    $cnx = connectToFTP($host,$port,$user,$pwd);
    if (! $cnx){
      return;
    }
    if (!is_array($dirs)) {
      $dirs = array($dirs);
    }
    foreach($dirs as $dir) {
      if ($dir[strlen($dir)-1]!='/') {
        $dir.='/';
      }
      $do = $cnx -> rm($dir,true);
      if ($do instanceof PEAR_Error) {
        LSerror :: addErrorCode('FTP_03',$dir);
        LSerror :: addErrorCode('FTP_00',$do -> getMessage());
        return;
      }
    }
    return true;
  }

 /**
  * Renomage d'un dossier via FTP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $host string Le nom ou l'IP du serveur FTP
  * @param[in] $port string Le port de connexion au serveur ftp
  * @param[in] $user string Le nom d'utilidateur de connexion
  * @param[in] $pwd  string Le mot de passe de connexion
  * @param[in] $old  string Le dossier à renomer
  * @param[in] $new  string Le nouveau nom du dossier à renomer
  *
  * @retval string True ou false si il y a un problème durant le renomage du/des dossier(s)
  */
  function renameDirByFTP($host,$port,$user,$pwd,$old,$new) {
    $cnx = connectToFTP($host,$port,$user,$pwd);
    if (! $cnx){
      return;
    }
    $do = $cnx -> rename($old,$new);
    if ($do instanceof PEAR_Error) {
      LSerror :: addErrorCode('FTP_05',array('old' => $old,'new' => $new));
      LSerror :: addErrorCode('FTP_00',$do -> getMessage());
      return;
    }
    return true;
  }
