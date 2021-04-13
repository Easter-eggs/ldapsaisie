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
LSerror :: defineError('MAIL_SUPPORT_01',
  ___("MAIL Support : Pear::MAIL is missing.")
);
LSerror :: defineError('MAIL_SUPPORT_02',
  ___("MAIL Support : Pear::MAIL_MIME is missing.")
);

// Autres erreurs
LSerror :: defineError('MAIL_00',
  ___("MAIL Error : %{msg}")
);

LSerror :: defineError('MAIL_01',
  ___("MAIL : Error sending your email")
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
      if(!LSsession::includeFile(PEAR_MAIL, true)) {
        LSerror :: addErrorCode('MAIL_SUPPORT_01');
        $retval=false;
      }
    }

    if (!class_exists('Mail_mime')) {
      if(!LSsession::includeFile(PEAR_MAIL_MIME, true)) {
        LSerror :: addErrorCode('MAIL_SUPPORT_02');
        $retval=false;
      }
    }

    if ($retval)
      LScli :: add_command(
        'test_send_mail',
        'cli_test_send_mail',
        'Send a test email',
        "[-s subject] [-b body] [recipient]",
        array (
          "  -s/--subject             The test email subject (optional)",
          "  -b/--body                The test email body (optional)",
          "  recipient                The test email recipient (required)",
        ),
        false,  // This command does not need LDAP connection
        'cli_test_send_mail_autocompleter'
      );

    return $retval;
  }

  /**
  * Envoie d'un mail
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si MAIL est pleinement supporté, false sinon
  */
  function sendMail($to, $subject, $msg, $headers=array(), $attachments=array(), $eol="\n", $encoding="utf8", $html=false) {
    global $MAIL_SEND_PARAMS, $MAIL_HEARDERS;
    $mail_obj = Mail::factory(MAIL_SEND_METHOD, (isset($MAIL_SEND_PARAMS)?$MAIL_SEND_PARAMS:null));

    if (isset($MAIL_HEARDERS) && is_array($MAIL_HEARDERS)) {
      $headers = array_merge($headers,$MAIL_HEARDERS);
    }

    if (isset($headers['From'])) {
      $from = $headers['From'];
      unset($headers['From']);
    }
    elseif (LSsession :: getEmailSender() != "") {
      $from = LSsession :: getEmailSender();
    }
    else {
      $from = null;
    }

    $headers["To"] = $to;

    $to = array (
      'To' => $to
    );

    foreach(array_keys($headers) as $header) {
      if(strtoupper($header) == 'BCC') {
        $to['BCC'] = $headers[$header];
      }
      elseif(strtoupper($header) == 'CC') {
        $to['CC'] = $headers[$header];
      }
    }

    $mime = new Mail_mime(
      array(
        'eol' => $eol,
        ($html?'html_charset':'text_charset') => $encoding,
        'head_charset' => $encoding,
      )
    );

    if ($from)
      $mime->setFrom($from);

    if ($subject)
      $mime->setSubject($subject);

    if ($html)
      $mime->setHTMLBody($msg);
    else
      $mime->setTXTBody($msg);

    if (is_array($attachments) && !empty($attachments)) {
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      foreach ($attachments as $file => $filename) {
        $mime->addAttachment($file, $finfo->file($file), $filename);
      }
    }

    $body = $mime->get();
    $headers = $mime->headers($headers);

    $ret = $mail_obj -> send($to, $headers, $body);

    if ($ret instanceof PEAR_Error) {
      LSerror :: addErrorCode('MAIL_01');
      LSerror :: addErrorCode('MAIL_00', $ret -> getMessage());
      return;
    }
    return true;
  }


if (php_sapi_name() != 'cli')
  return true;

function cli_test_send_mail($command_args) {
  $recipient = null;
  $subject = "Test email";
  $body = "This is a test message.";
  for ($i=0; $i < count($command_args); $i++) {
    switch ($command_args[$i]) {
      case '--subject':
      case '-s':
        $i++;
        if (!isset($command_args[$i]))
          LScli :: usage("You must provide the email subject after -s/--subject parameter.");
        $subject = $command_args[$i];
        if (!$subject)
          LScli :: usage("Invalid subject provided.");
        break;

      case '--body':
      case '-b':
        $i++;
        if (!isset($command_args[$i]))
          LScli :: usage("You must provide the email body after -b/--body parameter.");
        $body = $command_args[$i];
        if (!$body)
          LScli :: usage("Invalid body provided.");
        break;

      default:
        if (!$recipient && checkEmail($command_args[$i]))
          $recipient = $command_args[$i];
        else
          LScli :: usage("Invalid parameter '".$command_args[$i]."'.");
    }
  }

  if (!$recipient)
    LScli :: usage("You must provide test email recipient as first positional parameter");

  $logger = LSlog :: get_logger('LSaddon_mail');
  if (!sendMail($recipient, $subject, $body)) {
    $logger -> error("Fail to send test email sent to '$recipient'.");
    return false;
  }
  $logger -> info("Test email sent to '$recipient'.");
  return true;
}

function cli_test_send_mail_autocompleter($comp_words, $comp_word_num, $comp_word, $opts) {
  switch ($comp_words[$comp_word_num-1]) {
    case '-s':
    case '--subject':
    case '-b':
    case '--body':
      return array();
      break;
  }
  $opts = array_merge(
    $opts,
    array (
      '-s', '--subject',
      '-b', '--body',
    )
  );
  return LScli :: autocomplete_opts($opts, $comp_word);
}
