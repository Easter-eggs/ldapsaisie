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

class LSmail {

 /*
  * Méthode chargeant les dépendances d'affichage
  * 
  * @retval void
  */
  public static function loadDependenciesDisplay() {
    if (LSsession :: loadLSclass('LSsmoothbox')) {
      LSsmoothbox :: loadDependenciesDisplay();
    }
    
    LSsession :: addJSscript('LSmail.js');
    LSsession :: addCssFile('LSmail.css');
  }
  
  public static function ajax_display(&$data) {
    if (isset($_REQUEST['object']['type']) && isset($_REQUEST['object']['dn'])) {
      if (LSsession ::loadLSobject($_REQUEST['object']['type'])) {
        $obj = new $_REQUEST['object']['type']();
        $obj -> loadData($_REQUEST['object']['dn']);
        $msg = $obj -> getFData($_REQUEST['msg']);
        $subject = $obj -> getFData($_REQUEST['subject']);
      }
    }
    else {
      $msg = $_REQUEST['msg'];
      $subject = $_REQUEST['subject'];
    }

    LStemplate :: assign('LSmail_msg',$msg);
    LStemplate :: assign('LSmail_subject',$subject);
    LStemplate :: assign('LSmail_options',$_REQUEST['options']);

    if (is_array($_REQUEST['mails'])) {
      LStemplate :: assign('LSmail_mails',$_REQUEST['mails']);
    }
    else if(empty($_REQUEST['mails'])) {
      LStemplate :: assign('LSmail_mails',array($_REQUEST['mails']));
    }
    LStemplate :: assign('LSmail_mail_label',_('Email'));
    LStemplate :: assign('LSmail_subject_label',_('Title'));
    LStemplate :: assign('LSmail_msg_label',_('Message'));

    $data = array(
      'html' => LSsession :: fetchTemplate('LSmail.tpl')
    );
  }
  
  public static function ajax_send(&$data) {
    if (isset($_REQUEST['infos'])) {
      if (LSsession ::loadLSaddon('mail')) {
        if(sendMail($_REQUEST['infos']['mail'],$_REQUEST['infos']['subject'],$_REQUEST['infos']['msg'])) {
          $data = array(
            'msgok' => _("Your message has been sent successfully.")
          );
        }
      }
    }
    else {
      LSerror :: addErrorCode('LSsession_12');
    }
  }
  
}

?>
