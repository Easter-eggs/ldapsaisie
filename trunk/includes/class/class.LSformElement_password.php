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
    $GLOBALS['LSsession'] -> addCssFile('LSformElement_password.css');
    $return = $this -> getLabelInfos();
    $pwd = "";
    if (!$this -> isFreeze()) {
      if (($this -> params['html_options']['generationTool'])&&($this -> params['html_options']['autoGenerate'])&&(empty($this -> values))) {
        $pwd=$this->generatePassword();
      }
      
      $params = array(
        'generate' => ($this -> params['html_options']['generationTool']==True),
        'verify' => (!$this -> attr_html -> attribute -> ldapObject-> isNew())
      );
      $GLOBALS['LSsession'] -> addJSconfigParam($this -> name,$params);
      
      $GLOBALS['LSsession'] -> addJSscript('LSformElement_password_field.js');
      $GLOBALS['LSsession'] -> addJSscript('LSformElement_password.js');
    }
    $return['html'] = $this -> fetchTemplate(NULL,array('pwd' => $pwd));
    return $return;
  }
  
  function generatePassword() {
    return generatePassword($this -> params['html_options']['chars'],$this -> params['html_options']['lenght']);
  }
  
  function verifyPassword($pwd) {
    if ($this -> attr_html -> attribute -> ldapObject -> isNew()) {
      return false;
    }
    return $GLOBALS['LSsession'] -> checkUserPwd($this -> attr_html -> attribute -> ldapObject,$pwd);
  }
}
  
?>
