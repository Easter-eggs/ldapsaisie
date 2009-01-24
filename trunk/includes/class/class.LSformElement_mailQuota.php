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
 * Element mailQuota d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments mailQuota des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_mailQuota extends LSformElement {

  var $fieldTemplate = 'LSformElement_mailQuota_field.tpl';

  var $sizeFacts = array(
    1     => 'o',
    1000   => 'Ko',
    1000000  => 'Mo',
    1000000000 => 'Go'
  );

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();

    $quotas=array();
    
    foreach ($this -> values as $value) {
      if (ereg('([0-9]*)S',$value,$regs)) {
        $infos = array(
          'size' => $regs[1]
        );
        if ($infos['size'] >= 1000000000) {
          $infos['valueSizeFact']=1000000000;
        }
        else if ($infos['size'] >= 1000000) {
          $infos['valueSizeFact']=1000000;
        }
        else if ($infos['size'] >= 1000) {
          $infos['valueSizeFact']=1000;
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
          'unknown' => _('Valeur incorrect')
        );
      }
    }
    
    LSsession :: addCssFile('LSformElement_mailQuota.css');
    
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
  function getEmptyField() {
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
  function getPostData(&$return) {
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
          $return[$this -> name][$key] = ($val*$f).'S';
        }
      }
      return true;
    }
    else {
      $return[$this -> name] = array();
      return true;
    }
  }
}

?>
