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
 * Element quota d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments quota des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_quota extends LSformElement {

  var $fieldTemplate = 'LSformElement_quota_field.tpl';

  var $sizeFacts = array(
    1     => 'o',
    1024   => 'Ko',
    1048576  => 'Mo',
    1073741824 => 'Go'
  );

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  public function getDisplay(){
    $return = $this -> getLabelInfos();

    $quotas=array();
    
    foreach ($this -> values as $value) {
      if (preg_match('/^([0-9]*)$/',$value,$regs)) {
        $infos = array(
          'size' => ceil($regs[1]/$this -> getFactor())
        );
        if ($infos['size'] >= 1073741824) {
          $infos['valueSizeFact']=1073741824;
        }
        else if ($infos['size'] >= 1048576) {
          $infos['valueSizeFact']=1048576;
        }
        else if ($infos['size'] >= 1024) {
          $infos['valueSizeFact']=1024;
        }
        else {
          $infos['valueSizeFact']=1;
        }
        $infos['valueSize'] = $infos['size'] / $infos['valueSizeFact'];
        $infos['valueTxt'] = $infos['valueSize'].$this ->sizeFacts[$infos['valueSizeFact']];
        
        $quotas[$value] = $infos;
      }
      else {
        $quotas[$value] = array(
          'unknown' => _('Incorrect value')
        );
      }
    }
    
    LSsession :: addCssFile('LSformElement_quota.css');
    
    $return['html'] = $this -> fetchTemplate(
      NULL,
      array(
        'quotas' => $quotas,
        'sizeFacts' => $this -> sizeFacts
      )
    );
    return $return;
  }
  
 /**
  * Retourne le code HTML d'un champ vide
  *
  * @retval string Code HTML d'un champ vide.
  */
  public function getEmptyField() {
    return $this -> fetchTemplate(
      $this -> fieldTemplate,
      array(
        'sizeFacts' => $this -> sizeFacts
      )
    );
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
  public function getPostData(&$return) {
    if($this -> isFreeze()) {
      return true;
    }
    if (isset($_POST[$this -> name.'_size'])) {
      $return[$this -> name]=array();
      if(!is_array($_POST[$this -> name.'_size'])) {
        $_POST[$this -> name.'_size'] = array($_POST[$this -> name.'_size']);
      }
      if(isset($_POST[$this -> name.'_sizeFact']) && !is_array($_POST[$this -> name.'_sizeFact'])) {
        $_POST[$this -> name.'_sizeFact'] = array($_POST[$this -> name.'_sizeFact']);
      }
      foreach($_POST[$this -> name.'_size'] as $key => $val) {
        if (!empty($val)) {
          $f = 1;
          if (isset($_POST[$this -> name.'_sizeFact'][$key]) && ($_POST[$this -> name.'_sizeFact'][$key]!=1)) {
            $f = $_POST[$this -> name.'_sizeFact'][$key];
          }
          $val=preg_replace('/,/','.',$val);
          $return[$this -> name][$key] = ceil(ceil(($val*$f)*$this -> getFactor()));
        }
      }
      return true;
    }
    else {
      $return[$this -> name] = array();
      return true;
    }
  }

  private function getFactor() {
    return $this -> getParam('html_options.factor', 1);
  }

}

