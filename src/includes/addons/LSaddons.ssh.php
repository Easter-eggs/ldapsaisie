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

use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

// Messages d'erreur

// Support
LSerror :: defineError('SSH_SUPPORT_01',
  _("SSH Support : PhpSecLib is missing.")
);

LSerror :: defineError('SSH_SUPPORT_02',
  _("SSH Support : The constant %{const} is not defined.")
);


// Autres erreurs
LSerror :: defineError('SSH_01',
  _("SSH : Invalid connection paramater : %{param} parameter is missing.")
);
LSerror :: defineError('SSH_02',
  _("SSH : Authentication key file not found (or not accessible, file path : '%{path}')")
);
LSerror :: defineError('SSH_03',
  _("SSH : Fail to load authentication key (%{path}).")
);
LSerror :: defineError('SSH_04',
  _("SSH : Unable to connect to SSH Server (%{host}:%{port}).")
);
LSerror :: defineError('SSH_05',
  _("SSH : Unable to make directory %{dir} on the remote server.")
);
LSerror :: defineError('SSH_06',
  _("SSH : Unable to delete directory %{dir} on the remote server.")
);
LSerror :: defineError('SSH_07',
  _("SSH : Unable to rename folder from %{old} to %{new} on the remote server.")
);

 /**
  * Check LdapSaisie SSH support
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true if SSH is fully supported, false otherwise
  */
  function LSaddon_ssh_support() {
    $retval=true;

    // Check PhpSecLib library
    if (!defined('PHPSECLIB_AUTOLOAD')) {
      LSerror :: addErrorCode('SSH_SUPPORT_02','PHPSECLIB_AUTOLOAD');
      $retval=false;
    } else if(!LSsession::includeFile(PHPSECLIB_AUTOLOAD, true)) {
      LSerror :: addErrorCode('SSH_SUPPORT_01');
      $retval=false;
    }

    return $retval;
  }



 /**
  * Connect to an SFTP server
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $params array The SSH connexion parameters :
  *                          array (
  *                            'host' => '[SSH server hostname/IP]', // required
  *                            'port' => [SSH port], // optional, default : 22
  *                            'timeout' => [SSH connection timeout], // optional, default : 10
  *
  *                            // Authentication
  *                            'user' => '[SSH remote user]', // required
  *
  *                            // Auth method :
  *                            // One of the following method configuration is required.
  *
  *                            // Auth using simple password
  *                            'password' => '[secret password]'
  *
  *                            // Auth using a key
  *                            'auth_key' => array (
  *                              'file_path' => '[SSH private key file path]',
  *                              'password' => '[SSH private key file password]' // Only if need
  *                            )
  *                          )
  * @param[in] $sftp boolean Enable SFTP mode (default : false)
  *
  * @retval mixed SSH2/SFTP object or false
  */
  function connectToSSH($params, $sftp=false) {
    $logger = LSlog :: get_logger('LSaddon_ssh');
    if (!isset($params['host'])) {
      LSerror :: addErrorCode('SSH_01',"host");
      return false;
    }
    $host = $params['host'];

    if (!isset($params['user'])) {
      LSerror :: addErrorCode('SSH_01',"user");
      return false;
    }
    $user = $params['user'];

    $port = (isset($params['port'])?$params['port']:22);
    $timeout = (isset($params['timeout'])?$params['timeout']:10);

    if (isset($params['auth_key'])) {
      if (!isset($params['auth_key']['file_path'])) {
        LSerror :: addErrorCode('SSH_01',"auth_key -> file_path");
        return false;
      }
      $key_file_path = $params['auth_key']['file_path'];
      if (!is_file($key_file_path) || !is_readable($key_file_path)) {
        LSerror :: addErrorCode('SSH_02', $key_file_path);
        return false;
      }

      $password = new RSA();

      if (isset($params['auth_key']['password'])) {
        $password -> setPassword($params['auth_key']['password']);
      }

      $key_content = file_get_contents($key_file_path);
      if (!$password -> loadKey($key_content)) {
        LSerror :: addErrorCode('SSH_03', $key_file_path);
        return;
      }
      $logger -> debug("Connect on $user@$host:$port (timeout: $timeout sec) with key authentication using file '$key_file_path'.");
    }
    elseif (isset($params['password'])) {
      $logger -> debug("Connect on $user@$host:$port (timeout: $timeout sec) with password authentication.");
      $password = $params['password'];
    }
    else {
      LSerror :: addErrorCode('SSH_01',"authentication");
      return false;
    }

    if (isset($sftp))
      $cnx = new SFTP($host, $port, $timeout);
    else
      $cnx = new SSH2($host, $port, $timeout);

    if (!$cnx->login($user, $password)) {
      LSerror :: addErrorCode('SSH_04', array('host' => $host, 'port' => $port));
      return false;
    }
    $logger -> debug("Connected.");

    return $cnx;
  }

 /**
  * Create one or more directories throught SFTP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $connection_params array Connection parameters
  * @param[in] $dirs array|string The directory/ies to add
  * @param[in] $mode integer The directory/ies mode (default : default umask on the SSH server)
  * @param[in] $recursive boolean Enable recursive mode (default : false)
  * @param[in] $continue boolean Enable continue mode : do not on error (default : false)
  *
  * @retval boolean
  */
  function createDirsBySFTP($connection_params, $dirs, $chmod=-1, $recursive=false, $continue=false) {
    $cnx = connectToSSH($connection_params, true);
    if (! $cnx){
      return;
    }
    if (!is_array($dirs)) {
      $dirs = array($dirs);
    }
    $retval=true;
    foreach($dirs as $dir) {
      LSlog :: get_logger('LSaddon_ssh') -> debug("mkdir($dir) with chmod=$chmod and recursie ".($recursive?'enabled':'disabled'));
      if (!$cnx -> mkdir($dir, $chmod, $recursive)) {
        LSerror :: addErrorCode('SSH_05',$dir);
        if (!$continue) return false;
        $retval=false;
      }
    }
    return $retval;
  }

 /**
  * Delete one or more directories throught SFTP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $connection_params array Connection parameters
  * @param[in] $dirs array|string The directory/ies to remove
  * @param[in] $recursive boolean Enable recursive mode (default : false)
  * @param[in] $continue boolean Enable continue mode : do not on error (default : false)
  *
  * @retval boolean
  */
  function removeDirsBySFTP($connection_params, $dirs, $recursive=false) {
    $cnx = connectToSSH($connection_params, true);
    if (! $cnx){
      return;
    }
    if (!is_array($dirs)) {
      $dirs = array($dirs);
    }
    $retval=true;
    foreach($dirs as $dir) {
      LSlog :: get_logger('LSaddon_ssh') -> debug("delete($dir) with recursive ".($recursive?'enabled':'disabled'));
      if (!$cnx -> delete($dir, $recursive)) {
        LSerror :: addErrorCode('SSH_06',$dir);
        if (!$continue) return false;
        $retval=false;
      }
    }
    return $retval;
  }

 /**
  * Rename a directory throught SFTP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $connection_params array Connection parameters
  * @param[in] $old  string The actual directory path to rename
  * @param[in] $new  string The new directory path
  *
  * @retval boolean
  */
  function renameDirBySFTP($connection_params, $old, $new) {
    $cnx = connectToSSH($connection_params, true);
    if (! $cnx){
      return;
    }
    LSlog :: get_logger('LSaddon_ssh') -> debug("rename($old, $new)");
    if (!$cnx -> rename($old, $new)) {
      LSerror :: addErrorCode('SSH_07',array('old' => $old,'new' => $new));
      return;
    }
    return true;
  }

 /**
  * Exec a command throught SSH
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $connection_params array Connection parameters
  * @param[in] $cmd  string The command to run on remote server
  *
  * @retval mixed False if connection fail and an array otherwise, with
  *               exit code as first value and the command outup as second
  *               one (stdout + stderr).
  */
  function execBySSH($connection_params, $cmd) {
    $cnx = connectToSSH($connection_params);
    if (! $cnx){
      return;
    }
    LSlog :: get_logger('LSaddon_ssh') -> debug("exec($cmd)");
    $result = $cnx -> exec($cmd);
    $exit_status = $cnx->getExitStatus();
    return array($exit_status, $result);
  }
