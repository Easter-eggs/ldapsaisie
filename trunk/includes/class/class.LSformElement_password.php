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
    $return = $this -> getLabelInfos();
    if (!$this -> isFreeze()) {
      $numberId=rand();      
      $value_txt='';
      $input_type='password';
      $autogenerate_html='';      
      $class_txt='';
      
      // AutoGenerate
      if (($this -> params['html_options']['generationTool'])||(!isset($this -> params['html_options']['generationTool']))) {
        if (($this -> params['html_options']['autoGenerate'])&&(empty($this -> values))) {
          $value_txt="value='".$this->generatePassword()."'";
          $input_type='text';
        }
        $class_txt="class='LSformElement_password_generate'";
        $id = "LSformElement_password_generate_btn_".$this -> name."_".$numberId;
        $autogenerate_html = "<img src='templates/images/generate.png' id='$id' class='LSformElement_password_generate_btn'/>\n";
      }

      $id = "LSformElement_password_".$this -> name."_".$numberId;
      $return['html'] = "<input type='$input_type' name='".$this -> name."[]' $value_txt id='$id' $class_txt/>\n";
      $return['html'] .= $autogenerate_html;
      $id = "LSformElement_password_view_btn_".$this -> name."_".$numberId;
      $return['html'] .= "<img src='templates/images/view.png' id='$id' class='LSformElement_password_view_btn'/>\n";
      if (!$this -> attr_html -> attribute -> ldapObject-> isNew()) {
        $id = "LSformElement_password_verify_btn_".$this -> name."_".$numberId;
        $return['html'] .= "<img src='templates/images/verify.png' id='$id' class='LSformElement_password_verify_btn' alt=\"".('Vérifier le mot de passe')."\" title=\"".('Vérifier le mot de passe')."\" />\n";
      }
      
      if (!empty($this -> values)) {
        $return['html'] .= "* "._('Modification uniquement').".";
      }
    }
    else {
      if (empty($this -> values)) {
        $return['html'] = _('Aucune valeur definie');
      }
      else {
        $return['html'] = "********";
      }

    }
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
