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
      
      //Mail
      if (isset($_POST['LSformElement_password_'.$this -> name.'_send'])) {
        if ($_POST['LSformElement_password_'.$this -> name.'_send']==1) {
          $this -> sendMail = true;
          LSdebug ('send by form');
        }
      }
      else if ($this -> params['html_options']['mail']['send']==1) {
        $this -> sendMail = true;
        LSdebug ('send by config');
      }
      if ($this -> sendMail && LSsession :: loadLSaddon('mail')) {
        $msg = getFData($this -> params['html_options']['mail']['msg'],$return[$this -> name][0]);
        $subject = $this -> params['html_options']['mail']['subject'];
        if (isset($_POST['LSformElement_password_'.$this -> name.'_msg'])) {
          $msgInfos = json_decode($_POST['LSformElement_password_'.$this -> name.'_msg']);
          if ($msgInfos -> subject) {
            $subject = $msgInfos -> subject;
          }
          if ($msgInfos -> msg) {
            $msg = getFData($msgInfos -> msg,$return[$this -> name][0]);
          }
          if ($msgInfos -> mail) {
            $mail = $msgInfos -> mail;
          }
        }
        $this -> sendMail = array (
          'subject' => $subject,
          'msg' => $msg,
          'mail' => $mail
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
    if (!$this -> isFreeze()) {
      
      // Help Infos
      LSsession :: addHelpInfos(
        'LSformElement_password',
        array(
          'generate' => _('Generate a password.'),
          'verify' => _('Compare with stored password.'),
          'view' => _('Display password.'),
          'hide' => _('Hide password.'),
          'mail' => _("The password will be sent by mail if changed. Click to disable automatic notification."),
          'nomail' => _("The password will not be sent if changed. Click to enable automatic notification."),
          'editmail' => _("Modify the mail sent to notice the user")
        )
      );
      
      if (($this -> params['html_options']['generationTool'])&&($this -> params['html_options']['autoGenerate'])&&(empty($this -> values))) {
        $pwd=$this->generatePassword($this -> params);
      }
      
      $params = array(
        'generate' => ($this -> params['html_options']['generationTool']==True),
        'verify' => (!$this -> attr_html -> attribute -> ldapObject-> isNew())
      );
      if (isset($this -> params['html_options']['mail'])) {
        $params['mail'] = $this -> params['html_options']['mail'];
      }
      LSsession :: addJSconfigParam($this -> name,$params);
      
      LSsession :: addJSscript('LSformElement_password_field.js');
      LSsession :: addJSscript('LSformElement_password.js');
    }
    $return['html'] = $this -> fetchTemplate(NULL,array('pwd' => $pwd));
    return $return;
  }
  
  function generatePassword($param=NULL) {
    return generatePassword($params['html_options']['chars'],$params['html_options']['lenght']);
  }
  
  function verifyPassword($pwd) {
    if ($this -> attr_html -> attribute -> ldapObject -> isNew()) {
      return false;
    }
    return LSsession :: checkUserPwd($this -> attr_html -> attribute -> ldapObject,$pwd);
  }
  
  function send($params) {
    if (is_array($this -> sendMail)) {
      $mail = (String)$this -> sendMail['mail'];
      Lsdebug($mail);
      if ($mail=="") {
        $mail_attr = $this -> attr_html -> attribute -> ldapObject -> attrs[$this -> params['html_options']['mail']['mail_attr']];
        if ($mail_attr instanceOf LSattribute) {
          $mail = $mail_attr -> getValue();
          $mail=$mail[0];
        }
        else {
          LSdebug("L'attribut $mail_attr pour l'envoie du nouveau mot de passe n'existe pas.");
          return;
        }
      }
              
      if (checkEmail($mail,NULL,true)) {
        if (sendMail(
          $mail,
          $this -> sendMail['subject'],
          $this -> sendMail['msg']
        )) {
          LSsession :: addInfo(_('Notice mail sent.'));
        }
      }
      else {
        LSdebug('Adresse mail incorrect : '.$mail);
        return;
      }
    }
    return true;
  }
  
  public static function ajax_verifyPassword(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['fieldValue'])) && (isset($_REQUEST['idform'])) && (isset($_REQUEST['objectdn'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $form = $object -> getForm($_REQUEST['idform']);
        $object -> loadData($_REQUEST['objectdn']);
        $field=$form -> getElement($_REQUEST['attribute']);
        $val = $field -> verifyPassword($_REQUEST['fieldValue']);
        $data = array(
          'verifyPassword' => $val
        );
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
}
  
?>
