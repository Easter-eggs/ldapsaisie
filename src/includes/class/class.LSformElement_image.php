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
  var $fieldTemplate = 'LSformElement_image_field.tpl';

 /**
  * Retourne les infos d'affichage de l'élément
  *
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  public function getDisplay(){
    LStemplate :: addCssFile('LSformElement_image.css');
    $return = true;
    $id=$this -> name.'_'.rand();
    if (!$this -> isFreeze()) {
      LStemplate :: addHelpInfo(
        'LSformElement_date',
        array(
          'zoom' => _('Click to enlarge.'),
          'delete' => _('Click to delete the picture.')
        )
      );
      $return = $this -> getLabelInfos();
      $return['html'] = $this -> fetchTemplate(NULL,array('id' => 'LSformElement_image_input_'.$id));
      $this -> form -> setMaxFileSize(MAX_SEND_FILE_SIZE);
    }

    if (!empty($this -> values[0])) {
      $img_url = LSsession :: getTmpFileURL($this -> values[0]);
      LStemplate :: assign('LSformElement_image',array(
        'img' => $img_url,
        'id'  => $id,
      ));
      if (!$this -> isFreeze()) {
        LStemplate :: assign('LSformElement_image_actions','delete');
      }

      if ($this -> form -> definedError($this -> name)) {
        LStemplate :: assign('LSformElement_image_errors',true);
      }
      if (LSsession :: loadLSclass('LSsmoothbox')) {
        LSsmoothbox :: loadDependenciesDisplay();
      }
      LStemplate :: addJSscript('LSformElement_image.js');
    }
    return $return;
  }

  /**
   * Recupère la valeur de l'élement passée en POST
   *
   * Cette méthode vérifie la présence en POST de la valeur de l'élément et la récupère
   * pour la mettre dans le tableau passer en paramètre avec en clef le nom de l'élément
   *
   * @param[in] &$return array Reference of the array for retreived values
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  public function getPostData(&$return, $onlyIfPresent=false) {
    if($this -> isFreeze()) {
      return true;
    }

    if ($this -> checkIsInPostData()) {
      if (isset($_FILES[$this -> name]['tmp_name']) && is_uploaded_file($_FILES[$this -> name]['tmp_name'])) {
        $fp = fopen($_FILES[$this -> name]['tmp_name'], "r");
        $return[$this -> name][0] = fread($fp, filesize($_FILES[$this -> name]['tmp_name']));
        fclose($fp);
      }
      else {
        self :: log_debug('LSformElement_image('.$this->name.')->getPostData(): uploaded tmp file not found => '.varDump($_FILES[$this -> name]));
        $php_debug_params = array();
        foreach (array('file_uploads', 'upload_tmp_dir', 'upload_max_filesize', 'max_file_uploads', 'post_max_size', 'memory_limit') as $param)
          $php_debug_params[] = "$param = '".ini_get($param)."'";
        $php_debug_params[] = "HTML form MAX_FILE_SIZE = '".MAX_SEND_FILE_SIZE."'";
        self :: log_debug('LSformElement_image('.$this->name.')->getPostData(): '.implode(', ', $php_debug_params));
        $this -> form -> setElementError($this -> attr_html, $this -> getFileUploadErrorMessage($_FILES[$this -> name]));
        return false;
      }
    }
    else {
      if (isset($_POST[$this -> name.'_delete'])) {
        $return[$this -> name][0]='';
      }
    }
    return true;
  }

  /**
   * Check if file is present in POST data
   *
   * @return boolean True if file is in POST data, false otherwise
   */
  public function checkIsInPostData() {
    // Check if present in $_FILES
    if (!isset($_FILES[$this -> name]) || !is_array($_FILES[$this -> name]))
      return false;
    // Check if a file is submited
    if ($_FILES[$this -> name]['error'] == UPLOAD_ERR_NO_FILE)
      return false;
    return true;
  }

  /**
   * Get file upload error message
   *
   * @retval string The translated file upload error message
   */
  private function getFileUploadErrorMessage() {
    if (isset($_FILES) && isset($_FILES[$this -> name]) && isset($_FILES[$this -> name]['error'])) {
      switch($_FILES[$this -> name]['error']) {
        case UPLOAD_ERR_INI_SIZE:
          return _('The uploaded file size exceeds the limit accepted by the server.');
        case UPLOAD_ERR_FORM_SIZE:
          return _('The uploaded file size exceeds the limit accepted by the HTML form.');
        case UPLOAD_ERR_PARTIAL:
          return _('The file was only partially uploaded.');
        case UPLOAD_ERR_NO_FILE:
          return _('No file was uploaded.');
        case UPLOAD_ERR_NO_TMP_DIR:
          return _('No temporary folder found to store this uploaded file.');
        case UPLOAD_ERR_CANT_WRITE:
          return _('Failed to write file on server disk.');
        case UPLOAD_ERR_EXTENSION:
          return _('A PHP extension stopped the file upload.');
      }
    }
    return _("An unknown error occured sending this file.");
  }

  /**
   * Retreive value as return in API response
   *
   * @retval mixed API value(s) or null/empty array if no value
   */
  public function getApiValue() {
    if ($this -> isMultiple()) {
      $values = array();
      for ($i=0; $i < count($this -> values); $i++)
        $values[] = base64_encode($this -> values[0]);
      return $values;
    }
    if (!$this -> values)
      return null;
    return base64_encode($this -> values[0]);
  }
}
