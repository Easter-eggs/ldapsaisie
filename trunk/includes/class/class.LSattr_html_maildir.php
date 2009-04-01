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

LSsession :: loadLSaddon('maildir');

/**
 * Type d'attribut HTML maildir
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_html_maildir extends LSattr_html {

  var $LSformElement_type = 'maildir';
  var $_toDo = array();
  
  function LSattr_html_maildir ($name,$config,&$attribute) {
    $attribute -> addObjectEvent('before_delete',$this,'beforeDelete');
    $attribute -> addObjectEvent('after_delete',$this,'deleteMaildirByFTP');
    return parent :: LSattr_html($name,$config,&$attribute);
  }
  
  public function doOnModify($action,$cur,$new) {
    $this -> _toDo = array (
      'action' => $action,
      'old' => $cur,
      'new' => $new
    );
    $this -> attribute -> addObjectEvent('after_modify',$this,'toDo');
  }
  
  function toDo() {
    if (is_array($this -> _toDo)) {
      switch($this -> _toDo['action']) {
        case 'delete':
            return $this -> deleteMaildirByFTP();
          break;
        case 'modify':
          if (renameMaildirByFTP($this -> _toDo['old'],$this -> _toDo['new'])) {
            LSsession :: addInfo(_("The mailbox has been moved."));
            return true;
          }
          return;
          break;
        case 'create':
          if (createMaildirByFTP(null,$this -> _toDo['new'])) {
            LSsession :: addInfo(_("The mailbox has been created."));
            return true;
          }
          return;
          break;
        default:
          LSdebug($this -> name.' - LSformElement_maildir->toDo() : Unknown action.');
      }
    }
    LSdebug($this -> name.' - LSformElement_maildir->toDo() : Nothing to do.');
    return true;
  }
  
  public function deleteMaildirByFTP() {
    if ($this -> config['html_options']['archiveNameFormat']) {
      LSdebug('LSformElement_maildir : archive');
      $newname=getFData($this -> config['html_options']['archiveNameFormat'],$this -> _toDo['old']);
      if ($newname) {
        if (renameMaildirByFTP($this -> _toDo['old'],$newname)) {
          LSsession :: addInfo(_("The mailbox has been archived successfully."));
          return true;
        }
        return;
      }
      LSdebug($this -> name." - LSformElement_maildir->toDo() : Incorrect archive name.");
      return;
    }
    else {
      LSdebug('LSformElement_maildir : delete');
      if (removeMaildirByFTP(null,$this -> _toDo['old'])) {
        LSsession :: addInfo(_("The mailbox has been deleted."));
        return true;
      }
      return;
    }
  }
  
  public function beforeDelete() {
    $this -> _toDo = array (
      'action' => 'delete',
      'old' => $this -> getRemoteRootPathRegex(),
      'new' => ''
    );
  }
  
  public function getRemoteRootPathRegex($val='LS') {
    if ($val=='LS') {
      $val = $this -> attribute -> getValue();
      $val=$val[0];
    }
    LSdebug($this -> config['html_options']['remoteRootPathRegex']);
    if ($this -> config['html_options']['remoteRootPathRegex']) {
      if (
        ereg($this -> config['html_options']['remoteRootPathRegex'],$val,$r)
        ||
        empty($val)
      )
      {
        $val = $r[1];
      }
      else {
        LSdebug('Pbl remoteRootPathRegex');
      }
    }
    return $val;
  }
  
}

?>
