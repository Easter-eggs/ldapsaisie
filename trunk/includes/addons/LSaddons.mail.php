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
  * Données de configuration pour le support FTP
  */

      // Pear :: Mail
      define('PEAR_MAIL','/usr/share/php/Mail.php');
      
      /*
       * Méthode d'envoie :
       *  - mail : envoie avec la méthode PHP mail()
       *  - sendmail : envoie la commande sendmail du système
       *  - smtp : envoie en utilisant un serveur SMTP
       */
      define('MAIL_SEND_METHOD','smtp');
      
      /*
       * Paramètres d'envoie :
       *   Ces paramètres dépende de la méthode utilisé. Repporté vous à la documentation
       * de PEAR :: Mail pour plus d'information.
       * Lien : http://pear.php.net/manual/en/package.mail.mail.factory.php
       * Infos : 
       *  List of parameter for the backends
       *  mail
       *    o If safe mode is disabled, $params will be passed as the fifth 
       *      argument to the PHP mail() function. If $params is an array, 
       *      its elements will be joined as a space-delimited string. 
       *  sendmail
       *    o $params["sendmail_path"] - The location of the sendmail program 
       *      on the filesystem. Default is /usr/bin/sendmail.
       *    o $params["sendmail_args"] - Additional parameters to pass to the 
       *      sendmail. Default is -i. 
       *  smtp
       *    o $params["host"] - The server to connect. Default is localhost.
       *    o $params["port"] - The port to connect. Default is 25.
       *    o $params["auth"] - Whether or not to use SMTP authentication. 
       *      Default is FALSE.
       *    o $params["username"] - The username to use for SMTP authentication.
       *    o $params["password"] - The password to use for SMTP authentication.
       *    o $params["localhost"] - The value to give when sending EHLO or HELO.
       *      Default is localhost
       *    o $params["timeout"] - The SMTP connection timeout. 
       *      Default is NULL (no timeout).
       *    o $params["verp"] - Whether to use VERP or not. Default is FALSE.
       *    o $params["debug"] - Whether to enable SMTP debug mode or not. 
       *      Default is FALSE.
       *    o $params["persist"] - Indicates whether or not the SMTP connection 
       *      should persist over multiple calls to the send() method.
       */
      $MAIL_SEND_PARAMS = NULL;
      
      /*
       * Headers :
       */
      $MAIL_HEARDERS = array(
        "Content-Type"  =>  "text/plain",
        "charset"       =>  "UTF-8",
        "format"        =>  "flowed"
      );

      // Message d'erreur

      $GLOBALS['LSerror_code']['FTP_SUPPORT_01']= array (
        'msg' => _("MAIL Support : Pear::MAIL est introuvable."),
        'level' => 'c'
      );
      
      $GLOBALS['LSerror_code']['MAIL_00']= array (
        'msg' => _("MAIL Error : %{msg}"),
        'level' => 'c'
      );
      
      $GLOBALS['LSerror_code']['MAIL_01']= array (
        'msg' => _("MAIL : Problème durant l'envoie de votre mail"),
        'level' => 'c'
      );
      
 /**
  * Fin des données de configuration
  */


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
      if(!@include(PEAR_MAIL)) {
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
  function sendMail($to,$subject,$msg) {
    $mail_obj  = & Mail::factory(MAIL_SEND_METHOD, $MAIL_SEND_PARAMS);
    
    if(is_array($MAIL_HEARDERS)) {
      $headers = $MAIL_HEARDERS;
    }
    else {
      $headers = array();
    }
    $headers["Subject"] = $subject;
    if (!isset($headers['From']) && ($GLOBALS['LSsession'] -> getEmailSender() != "")) {
      $headers['From'] = $GLOBALS['LSsession'] -> getEmailSender();
    }
    
    $ret = $mail_obj -> send($to,$headers,$msg);
    
    if ($ret instanceof PEAR_Error) {
      $GLOBALS['LSerror'] -> addErrorCode('MAIL_01');
      $GLOBALS['LSerror'] -> addErrorCode('MAIL_00',$ret -> getMessage());
      return;
    }
    return true;
  }



 
