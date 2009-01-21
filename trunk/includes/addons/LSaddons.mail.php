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
$GLOBALS['LSerror_code']['MAIL_SUPPORT_01']= array (
  'msg' => _("MAIL Support : Pear::MAIL est introuvable."),
  'level' => 'c'
);

// Autres erreurs
$GLOBALS['LSerror_code']['MAIL_00']= array (
  'msg' => _("MAIL Error : %{msg}"),
  'level' => 'c'
);

$GLOBALS['LSerror_code']['MAIL_01']= array (
  'msg' => _("MAIL : Problème durant l'envoie de votre mail"),
  'level' => 'c'
);
      
 /**
  * Verification du support MAIL par ldapSaisie
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si MAIL est pleinement supporté, false sinon
  */
  function LSaddon_mail_support() {
    $retval=true;

    // Dependance de librairie
    if (!class_exists('Mail')) {
      if(!LSsession::includeFile(PEAR_MAIL)) {
        $GLOBALS['LSerror'] -> addErrorCode('MAIL_SUPPORT_01');
        $retval=false;
      }
    }
    
    return $retval;
  }
  
  /**
  * Envoie d'un mail
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si MAIL est pleinement supporté, false sinon
  */
  function sendMail($to,$subject,$msg,$headers=array()) {
    $mail_obj  = & Mail::factory(MAIL_SEND_METHOD, $MAIL_SEND_PARAMS);
    
    if(is_array($MAIL_HEARDERS)) {
      $headers = array_merge($headers,$MAIL_HEARDERS);
    }
    if ($subject) {
      $headers["Subject"] = $subject;
    }
    if (!isset($headers['From']) && ($GLOBALS['LSsession'] -> getEmailSender() != "")) {
      $headers['From'] = $GLOBALS['LSsession'] -> getEmailSender();
    }
    $headers["To"] = $to;
    
    $ret = $mail_obj -> send($to,$headers,$msg);
    
    if ($ret instanceof PEAR_Error) {
      $GLOBALS['LSerror'] -> addErrorCode('MAIL_01');
      $GLOBALS['LSerror'] -> addErrorCode('MAIL_00',$ret -> getMessage());
      return;
    }
    return true;
  }



 
