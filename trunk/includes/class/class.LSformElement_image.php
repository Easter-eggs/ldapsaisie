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
 * Element texte d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments textes des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_image extends LSformElement {

  var $postImage = NULL;
  var $tmp_file = array();

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $GLOBALS['LSsession'] -> addCssFile('LSformElement_image.css');
    $return = true;
    if (!$this -> isFreeze()) {
      $id=$this -> name.'_'.rand();
      $return = $this -> getLabelInfos();
      $return['html'] = "<input type='file' name='".$this -> name."' class='LSform' id='LSformElement_image_input_$id' />\n";
      $this -> form -> setMaxFileSize(MAX_SEND_FILE_SIZE);
    }

    if (!empty($this -> values[0])) {
      $img_path = $GLOBALS['LSsession'] -> getTmpFile($this -> values[0]);
      $GLOBALS['Smarty'] -> assign('LSformElement_image',array(
        'img' => $img_path,
        'id'  => $id,
      ));
      if (!$this -> isFreeze()) {
        $GLOBALS['Smarty'] -> assign('LSformElement_image_actions','delete');
      }
      
      if ($this -> form -> definedError($this -> name)) {
        $GLOBALS['Smarty'] -> assign('LSformElement_image_errors',true);
      }
      $GLOBALS['LSsession'] -> addJSscript('LSformElement_image.js');
    }
    return $return;
  }
  
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
    if($this -> isFreeze()) {
      return true;
    }
   
    if (is_uploaded_file($_FILES[$this -> name]['tmp_name'])) {
      $fp = fopen($_FILES[$this -> name]['tmp_name'], "r");
      $buf = fread($fp, filesize($_FILES[$this -> name]['tmp_name']));
      fclose($fp);
      $tmp_file = LS_TMP_DIR.$this -> name.'_'.rand().'.tmp';
      if (move_uploaded_file($_FILES[$this -> name]['tmp_name'],$tmp_file)) {
        $GLOBALS['LSsession'] -> addTmpFile($buf,$tmp_file);
      }
      $return[$this -> name][0] = $buf;
    }
    else {
      if (isset($_POST[$this -> name.'_delete'])) {
        $return[$this -> name][0]='';
      }
    }
    return true;
  }
}

?>
