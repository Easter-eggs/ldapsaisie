<?php
/*******************************************************************************
 * Copyright (C) 2007-2021 Easter-eggs
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

LSsession :: loadLSclass('LSformElement');

/**
 * Element password d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments password des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_password extends LSformElement {

  var $fieldTemplate = 'LSformElement_password_field.tpl';
  var $template = 'LSformElement_password.tpl';

  var $sendMail = false;

  /**
   * Recupère la valeur de l'élement passée en POST
   *
   * Cette méthode vérifie la présence en POST de la valeur de l'élément et la récupère
   * pour la mettre dans le tableau passer en paramètre avec en clef le nom de l'élément
   *
   * @param[in] &$return array Reference of the array for retrieved values
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  public function getPostData(&$return, $onlyIfPresent=false) {
    // Récupère la valeur dans _POST, et les vérifie avec la fonction générale
    $retval = parent :: getPostData($return, $onlyIfPresent);
    // Si une valeur est recupérée
    if ($retval) {
      $val = $this -> form -> ldapObject -> attrs[$this -> name] -> getValue();
      if( (empty($return[$this -> name][0]) ) && ( ! empty( $val ) ) ) {
        unset($return[$this -> name]);
        $this -> form -> _notUpdate[$this -> name] = true;
        return true;
      }

      if (!$this -> form -> api_mode && $this -> getParam('html_options.confirmInput', False, 'bool')) {
        $confirm_data = self :: getData($_POST, $this -> name . '_confirm');
        $confirmed = false;
        if (!is_array($confirm_data)) {
          if (!isset($return[$this -> name]) || empty($return[$this -> name]) || empty($return[$this -> name][0])) {
            self :: log_debug(
              'getPostData('.$this -> name.'): no confirm data, but empty password provided => confirmed'
            );
            $confirmed = true;
          }
          elseif ($onlyIfPresent) {
            self :: log_debug(
              'getPostData('.$this -> name.'): no confirm data, but onlyIfPresent mode => confirmed'
            );
            $confirmed = true;
          }
        }
        elseif ($confirm_data == $return[$this -> name]) {
          self :: log_debug(
            'getPostData('.$this -> name.'): confirm password value matched with new password'
          );
          $confirmed = true;
        }
        if (!$confirmed) {
          unset($return[$this -> name]);
          self :: log_debug(
            'getPostData('.$this -> name.'): '.
            varDump($return[$this -> name])." != ".varDump($confirm_data)
          );
          $this -> form -> setElementError($this -> attr_html, _('%{label}: passwords entered did not match.'));
          return true;
        }
      }

      if (($return[$this -> name] && $this -> verifyPassword($return[$this -> name][0])) || ((empty($return[$this -> name][0])) && empty($val))) {
        self :: log_debug('getPostData('.$this -> name.'): no change');
        unset($return[$this -> name]);
        $this -> form -> _notUpdate[$this -> name] = true;
        return true;
      }

      //Mail

      // Do not send mail if password is not set :
      if (empty($return[$this -> name])) {
        return true;
      }

      if (isset($_POST['LSformElement_password_'.$this -> name.'_send'])) {
        if ($_POST['LSformElement_password_'.$this -> name.'_send']==1) {
          $this -> sendMail = true;
          self :: log_debug('getPostData('.$this -> name.'): send new password enabled by form');
        }
      }
      else if ($this -> getParam('html_options.mail.send')) {
        $this -> sendMail = true;
        self :: log_debug('getPostData('.$this -> name.'): send new password enabled by config');
      }
      if ($this -> sendMail && LSsession :: loadLSaddon('mail')) {
        $msg = $this -> getParam('html_options.mail.msg');
        $subject = $this -> getParam('html_options.mail.subject');
        $mail = "";
        if (isset($_POST['LSformElement_password_'.$this -> name.'_msg'])) {
          $msgInfos = json_decode($_POST['LSformElement_password_'.$this -> name.'_msg']);
          if ($msgInfos -> subject) {
            $subject = $msgInfos -> subject;
          }
          if ($msgInfos -> msg) {
            $msg = $msgInfos -> msg;
          }
          if ($msgInfos -> mail) {
            $mail = $msgInfos -> mail;
            if (!checkEmail(
              $mail,
              $this -> getParam('html_options.mail.domain'),
              $this -> getParam('html_options.mail.checkDomain', true, 'bool')
            )) {
              $this -> form -> setElementError(
                $this -> attr_html,
                _('%{label}: invalid email address provided to send new password.')
              );
              return true;
            }
          }
        }
        $this -> sendMail = array (
          'subject' => $subject,
          'msg' => $msg,
          'mail' => $mail,
          'pwd' => $return[$this -> name][0]
        );
        if ($this -> form -> idForm == 'create')
          $this -> attr_html -> attribute -> addObjectEvent('after_create', $this, 'send');
        else
          $this -> attr_html -> attribute -> addObjectEvent('after_modify', $this, 'send');
      }
    }
    return $retval;
  }

 /**
  * Retourne les infos d'affichage de l'élément
  *
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  public function getDisplay(){
    LStemplate :: addCssFile('LSformElement_password.css');
    $return = $this -> getLabelInfos();
    $pwd = "";
    if ($this -> getParam('html_options.clearView') or $this -> getParam('html_options.clearEdit')) {
      $pwd = $this -> values[0];
    }
    if (!$this -> isFreeze()) {

      // Help Infos
      LStemplate :: addHelpInfo(
        'LSformElement_password',
        array(
          'generate' => _('Generate a password.'),
          'verify' => _('Compare with stored password.'),
          'view' => _('Display password.'),
          'viewHash' => _('Display hashed password.'),
          'hide' => _('Hide password.'),
          'mail' => _("The password will be sent by mail if changed. Click to disable automatic notification."),
          'nomail' => _("The password will not be sent if changed. Click to enable automatic notification."),
          'editmail' => _("Modify the mail sent to notice the user")
        )
      );

      if (
        $this -> getParam('html_options.generationTool') &&
        $this -> getParam('html_options.autoGenerate') &&
        empty($this -> values)
      ) {
        $pwd=$this->generatePassword($this -> params);
      }

      $params = array(
        'generate' => $this -> getParam('html_options.generationTool', true, 'bool'),
        'clearEdit' => $this -> getParam('html_options.clearEdit', false, 'bool'),
        'viewHash' => $this -> getParam('html_options.viewHash', false, 'bool'),
        'verify' => (
          !$this -> attr_html -> attribute -> ldapObject-> isNew() &&
          $this -> getParam('html_options.verify', True, 'bool')
        ),
        'confirmChange' => (
          !$this -> attr_html -> attribute -> ldapObject-> isNew() &&
          $this -> getParam('html_options.confirmChange', False, 'bool')
        ),
        'confirmInput' => $this -> getParam('html_options.confirmInput', False, 'bool'),
      );

      if ($params['confirmChange']) {
        $defaultConfirmChangeQuestion = ___('%{label}: Do you confirm the password change?');
        $params['confirmChangeQuestion'] = $this -> attr_html -> attribute -> ldapObject -> getDisplayFData(
          __($this -> getParam('html_options.confirmChangeQuestion', $defaultConfirmChangeQuestion)),
          $this -> label
        );
      }

      if ($params['confirmInput']) {
        $defaultConfirmInputError = ___('Passwords entered did not match.');
        $params['confirmInputError'] = $this -> attr_html -> attribute -> ldapObject -> getDisplayFData(
          __($this -> getParam('html_options.confirmInputError', $defaultConfirmInputError)),
          $this -> label
        );
      }

      if ($this -> getParam('html_options.mail')) {
        $params['mail'] = $this -> getParam('html_options.mail');
        $params['mail']['mail_attr'] = $this -> getMailAttrs();
      }
      LStemplate :: addJSconfigParam($this -> name, $params);

      LStemplate :: addJSscript('LSformElement_password_field.js');
      LStemplate :: addJSscript('LSformElement_password.js');
    }
    $return['html'] = $this -> fetchTemplate (
      NULL,
      array(
        'pwd' => $pwd,
        'clearView' => $this -> getParam('html_options.clearView'),
        'clearEdit' => $this -> getParam('html_options.clearEdit'),
        'confirmInput' => $this -> getParam('html_options.confirmInput', False, 'bool'),
      )
    );
    return $return;
  }

  public static function generatePassword($params=NULL) {
    if (LSconfig :: get('html_options.use_pwgen', false, null, $params)) {
      $args = LSconfig :: get('html_options.pwgen_opts', '', 'string', $params);
      $len = LSconfig :: get('html_options.lenght', 8, 'int', $params);
      $bin = LSconfig :: get('html_options.pwgen_path', 'pwgen', 'string', $params);
      $cmd = "$bin ".escapeshellcmd($args)." $len 1";
      exec($cmd,$ret,$retcode);
      LSdebug("Generate password using pwgen. Cmd : '$cmd' / Return code : $retcode / Return : ".print_r($ret,1));
      if ($retcode==0 && count($ret)>0) {
        return $ret[0];
      }
      else {
        LSerror :: addErrorCode('LSformElement_password_03');
      }
    }
    return generatePassword(
      LSconfig :: get('html_options.chars', null, null, $params),
      LSconfig :: get('html_options.lenght', 8, 'int', $params)
    );
  }

  public function verifyPassword($pwd) {
    if ($this -> attr_html -> attribute -> ldapObject -> isNew()) {
      return false;
    }
    if ($this -> isLoginPassword()) {
      return LSsession :: checkUserPwd($this -> attr_html -> attribute -> ldapObject, $pwd);
    }
    else {
      return $this -> attr_html -> attribute -> ldap -> verify($pwd);
    }
  }

  public function getMailAttrs() {
    if (!$this -> getParam('html_options.mail'))
      return False;
    if ($this -> getParam('html_options.mail.get_mail_attr_function')) {
      $func = $this -> getParam('html_options.mail.get_mail_attr_function');
      if (is_callable($func)) {
        try {
          return call_user_func_array($func, array(&$this));
        }
        catch(Exception $e) {
          LSerror :: addErrorCode('LSformElement_password_05', $e->getMessage());
        }
      }
      else {
        LSerror :: addErrorCode('LSformElement_password_04');
        return False;
      }
    }
    return $this -> getParam('html_options.mail.mail_attr');
  }

  public function send($params) {
    if (!is_array($this -> sendMail))
      return true;
    $mail = (String)$this -> sendMail['mail'];
    self :: log_debug("send(): mail from params: '$mail'");
    if (!$mail) {
      $mail_attrs = ensureIsArray($this -> getMailAttrs());
      self :: log_debug('send(): mail attrs: '.varDump($mail_attrs));
      $checkDomainsList = $this -> getParam('html_options.mail.domain');
      $checkDomain = $this -> getParam('html_options.mail.checkDomain', true, 'bool');
      foreach($mail_attrs as $attr) {
        $mail_attr = $this -> attr_html -> attribute -> ldapObject -> attrs[$attr];
        if ($mail_attr instanceOf LSattribute) {
          $mail_values = ensureIsArray($mail_attr -> getValue());
          foreach($mail_values as $mail_value) {
            if ($mail_value && checkEmail($mail_value, $checkDomainsList, $checkDomain)) {
              $mail = $mail_value;
              break;
            }
          }
          if ($mail)
            break;
          else
            self :: log_debug("send(): $attr attribute empty (or does not contain valid email)");
        }
        else {
          self :: log_warning("send(): '$attr' attribute to send new password does not exists.");
        }
      }
      if (!$mail) {
        LSerror :: addErrorCode('LSformElement_password_01');
        return;
      }
    }

    self :: log_info(
      $this -> attr_html -> attribute -> ldapObject -> getDn().": send new '".$this -> name."' to '$mail'."
    );
    $this -> attr_html -> attribute -> ldapObject -> registerOtherValue('password', $this -> sendMail['pwd']);
    $msg = $this -> attr_html -> attribute -> ldapObject -> getDisplayFData($this -> sendMail['msg']);
    $headers = $this -> getParam('html_options.mail.headers', array());
    $bcc = $this -> getParam('html_options.mail.bcc');
    if ($bcc)
      $headers['Bcc'] = $bcc;
    if (sendMail(
      $mail,
      $this -> sendMail['subject'],
      $msg,
      $headers
    )) {
      LSsession :: addInfo(_('Notice mail sent.'));
      // Set $this -> sendMail to false to avoid potential multiple sent email
      $this -> sendMail = false;
    }
    else {
      LSerror :: addErrorCode('LSformElement_password_02', $mail);
      return;
    }
  }

  public static function ajax_verifyPassword(&$data) {
    if (
      isset($_REQUEST['attribute']) &&
      isset($_REQUEST['objecttype']) &&
      isset($_REQUEST['fieldValue']) &&
      isset($_REQUEST['idform']) &&
      isset($_REQUEST['objectdn'])
    ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $object -> loadData($_REQUEST['objectdn']);
        $form = $object -> getForm($_REQUEST['idform']);
        if ($form) {
          $field=$form -> getElement($_REQUEST['attribute']);
          if ($field) {
            $val = $field -> verifyPassword($_REQUEST['fieldValue']);
            $data = array(
              'verifyPassword' => $val
            );
          }
          else {
            LSdebug('Impossible de récupérer le LSformElement');
          }
        }
        else {
          LSdebug('Impossible de recuperer le LSform.');
        }
      }
    }
  }

  public static function ajax_generatePassword(&$data) {
    if (
      isset($_REQUEST['attribute']) &&
      isset($_REQUEST['objecttype']) &&
      isset($_REQUEST['objectdn']) &&
      isset($_REQUEST['idform'])
    ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $params = LSconfig :: get("LSobjects.".$_REQUEST['objecttype'].".attrs.".$_REQUEST['attribute']);
        $val = self :: generatePassword($params);
        if ( $val ) {
          $data = array(
            'generatePassword' => $val
          );
        }
      }
    }
  }

  public static function ajax_viewHash(&$data) {
    if (
      isset($_REQUEST['attribute']) &&
      isset($_REQUEST['objecttype']) &&
      isset($_REQUEST['objectdn'])
    ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $object -> loadData($_REQUEST['objectdn']);
        if (LSsession::canAccess($_REQUEST['objecttype'],$_REQUEST['objectdn'],null,$_REQUEST['attribute'])) {
          $values = $object -> getValue($_REQUEST['attribute']);
          if (is_string($values[0])) {
            $data = array (
              'hash' => $values[0]
            );
          }
        }
      }
    }
  }

  public function isLoginPassword() {
    return $this -> getParam('html_options.isLoginPassword', false, 'bool');
  }

  /**
   * CLI autocompleter for form element attribute values
   *
   * @param[in] &$opts      array                 Reference of array of avalaible autocomplete options
   * @param[in] $comp_word  string                The (unquoted) command word to autocomplete
   * @param[in] $attr_value string                The current attribute value in command word to autocomplete (optional, default: empty string)
   * @param[in] $multiple_value_delimiter string  The multiple value delimiter (optional, default: "|")
   * @param[in] $quote_char string                The quote character detected (optional, default: empty string)
   *
   * @retval void
   */
  public function autocomplete_attr_values(&$opts, $comp_word, $attr_value="", $multiple_value_delimiter="|", $quote_char='') {
    // Split attribute values and retrieved splited value in $attr_values and $last_attr_value
    if (!$this -> split_autocomplete_attr_values($attr_value, $multiple_value_delimiter, $attr_values, $last_attr_value))
      return;
    $pwd = $this->generatePassword($this -> params);
    $this -> add_autocomplete_attr_value_opts($opts, $attr_values, $pwd, $multiple_value_delimiter, $quote_char);
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSformElement_password_01',
___("LSformElement_password : No valid contact mail address available : Can't send new password.")
);
LSerror :: defineError('LSformElement_password_02',
___("LSformElement_password : Fail to send new password by email to %{mail}.")
);
LSerror :: defineError('LSformElement_password_03',
___("LSformElement_password : Fail to exec pwgen. Check it's correctly installed.")
);
LSerror :: defineError('LSformElement_password_04',
___("LSformElement_password : Fail to determine witch e-mail attribute to use to send new password : get_mail_attr_function parameter not refer to a valid function.")
);
LSerror :: defineError('LSformElement_password_05',
___("LSformElement_password : Fail to determine witch e-mail attribute to use to send new password : get_mail_attr_function throwed an exception : %{msg}")
);
