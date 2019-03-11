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
   * @param[] array Pointeur sur le tableau qui recupèrera la valeur.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  function getPostData(&$return) {
    // Récupère la valeur dans _POST, et les vérifie avec la fonction générale
    $retval = parent :: getPostData($return);
    // Si une valeur est recupérée
    if ($retval) {
      $val = $this -> form -> ldapObject -> attrs[$this -> name] -> getValue(); 
      if( (empty($return[$this -> name][0]) ) && ( ! empty( $val ) ) ) {
        unset($return[$this -> name]);
        $this -> form -> _notUpdate[$this -> name] == true;
        return true;
      }
      
      if ($this -> verifyPassword($return[$this -> name][0]) || (empty($return[$this -> name][0]) && empty($val))) {
        LSdebug("Password : no change");
        unset($return[$this -> name]);
        $this -> form -> _notUpdate[$this -> name] == true;
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
          LSdebug ('send by form');
        }
      }
      else if ($this -> getParam('html_options.mail.send')) {
        $this -> sendMail = true;
        LSdebug ('send by config');
      }
      if ($this -> sendMail && LSsession :: loadLSaddon('mail')) {
        $msg = $this -> getParam('html_options.mail.msg');
        $subject = $this -> getParam('html_options.mail.subject');
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
          }
        }
        $this -> sendMail = array (
          'subject' => $subject,
          'msg' => $msg,
          'mail' => $mail,
          'pwd' => $return[$this -> name][0]
        );
        $this -> attr_html -> attribute -> addObjectEvent('after_modify',$this,'send');
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
  function getDisplay(){
    LSsession :: addCssFile('LSformElement_password.css');
    $return = $this -> getLabelInfos();
    $pwd = "";
    if ($this -> getParam('html_options.clearView') or $this -> getParam('html_options.clearEdit')) {
      $pwd = $this -> values[0];
    }
    if (!$this -> isFreeze()) {
      
      // Help Infos
      LSsession :: addHelpInfos(
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
      
      if ($this -> getParam('html_options.generationTool') && $this -> getParam('html_options.autoGenerate') && empty($this -> values)) {
        $pwd=$this->generatePassword($this -> params);
      }
      
      $params = array(
        'generate' => $this -> getParam('html_options.generationTool', true, 'bool'),
        'clearEdit' => $this -> getParam('html_options.clearEdit', false, 'bool'),
        'viewHash' => $this -> getParam('html_options.viewHash', false, 'bool'),
        'verify' => ( (!$this -> attr_html -> attribute -> ldapObject-> isNew()) && $this -> getParam('html_options.verify', True, 'bool') )
      );

      if ($this -> getParam('html_options.mail')) {
        $params['mail'] = $this -> getParam('html_options.mail');
        $params['mail']['mail_attr'] = $this -> getMailAttrs();
      }
      LSsession :: addJSconfigParam($this -> name, $params);
      
      LSsession :: addJSscript('LSformElement_password_field.js');
      LSsession :: addJSscript('LSformElement_password.js');
    }
    $return['html'] = $this -> fetchTemplate (
      NULL,
      array(
        'pwd' => $pwd,
        'clearView' => $this -> getParam('html_options.clearView'),
        'clearEdit' => $this -> getParam('html_options.clearEdit'),
      )
    );
    return $return;
  }
  
  function generatePassword($params=NULL) {
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
    return generatePassword(LSconfig :: get('html_options.chars', null, null, $params), LSconfig :: get('html_options.lenght', 8, 'int', $params));
  }
  
  function verifyPassword($pwd) {
    if ($this -> attr_html -> attribute -> ldapObject -> isNew()) {
      return false;
    }
    if ($this -> isLoginPassword()) {
      return LSsession :: checkUserPwd($this -> attr_html -> attribute -> ldapObject,$pwd);
    }
    else {
      $hash = $this -> attr_html -> attribute -> ldap -> encodePassword($pwd);
      $find=false;
      if (is_array($this -> attr_html -> attribute -> data)) {
        $data = $this -> attr_html -> attribute -> data;
      }
      elseif (!is_array($this -> attr_html -> attribute -> data) && !empty($this -> attr_html -> attribute -> data)) {
        $data = array($this -> attr_html -> attribute -> data);
      }
      else {
        return $find;
      }
      foreach($data as $val) {
        if ($hash == $val)
          $find=true;
      }
      return $find;
    }
  }

  function getMailAttrs() {
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

  function send($params) {
    if (is_array($this -> sendMail)) {
      $mail = (String)$this -> sendMail['mail'];
      Lsdebug($mail);
      if ($mail=="") {
        $mail_attrs = $this -> getMailAttrs();
        if (!is_array($mail_attrs)) {
          $mail_attrs=array($mail_attrs);
        }
        foreach($mail_attrs as $attr) {
          $mail_attr = $this -> attr_html -> attribute -> ldapObject -> attrs[$attr];
          if ($mail_attr instanceOf LSattribute) {
            $mail = $mail_attr -> getValue();
            if (!empty($mail) && checkEmail($mail[0],NULL,true)) {
              $mail=$mail[0];
              break;
            }
            else {
              $mail="";
            }
          }
          else {
            LSdebug("L'attribut $mail_attr pour l'envoie du nouveau mot de passe n'existe pas.");
          }
        }
        if ($mail=="") {
          LSerror :: addErrorCode('LSformElement_password_01');
          return;
        }
      }
              
      if (checkEmail($mail,NULL,true)) {
        $this -> attr_html -> attribute -> ldapObject -> registerOtherValue('password',$this -> sendMail['pwd']);
        $msg = $this -> attr_html -> attribute -> ldapObject -> getFData($this -> sendMail['msg']);
        $headers = $this -> getParam('html_options.mail.headers', array());
        $bcc = $this -> getParam('html_options.mail.bcc');
        if ($bcc) $headers['Bcc'] = $bcc;
        if (sendMail(
          $mail,
          $this -> sendMail['subject'],
          $msg,
          $headers
        )) {
          LSsession :: addInfo(_('Notice mail sent.'));
        }
      }
      else {
        LSerror :: addErrorCode('LSformElement_password_02',$mail);
        return;
      }
    }
    return true;
  }
  
  public static function ajax_verifyPassword(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['fieldValue'])) && (isset($_REQUEST['idform'])) && (isset($_REQUEST['objectdn'])) ) {
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
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) ) {
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
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) ) {
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
    return $this -> getParam('html_options.isLoginPassword', true);
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSformElement_password_01',
_("LSformElement_password : No contact mail available to send password.")
);
LSerror :: defineError('LSformElement_password_02',
_("LSformElement_password : Contact mail invalid (%{mail}). Can't send password.")
);
LSerror :: defineError('LSformElement_password_03',
_("LSformElement_password : Fail to exec pwgen. Check it's correctly installed.")
);
LSerror :: defineError('LSformElement_password_04',
_("LSformElement_password : Fail to determine witch e-mail attribute to use to send new password : get_mail_attr_function parameter not refer to a valid function.")
);
LSerror :: defineError('LSformElement_password_05',
_("LSformElement_password : Fail to determine witch e-mail attribute to use to send new password : get_mail_attr_function throwed an exception : %{msg}")
);
