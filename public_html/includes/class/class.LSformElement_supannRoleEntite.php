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
LSsession :: loadLSaddon('supann');

/**
 * Element supannRoleEntite d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannRoleEntite des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannRoleEntite extends LSformElement {

  var $template = 'LSformElement_supannRoleEntite.tpl';
  var $fieldTemplate = 'LSformElement_supannRoleEntite_field.tpl';

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();

    $parseValues=array();
    foreach($this -> values as $val) {
      $keyValue=supannParseCompositeValue($val);
      if ($keyValue) {
        $parseValue=array('value' => $val);
        foreach($keyValue as $key => $value) {
          $parseValue[$key]=array(
            'value' => $value
          );
          $tr=supannTranslateRoleEntiteValue($key,$value);
          if ($tr) {
            if($tr['translated'])
              $parseValue[$key]['translated']=$tr['translated'];
            if($tr['label'])
              $parseValue[$key]['label']=$tr['label'];
          }
        }
        $parseValues[]=$parseValue;
      }
    }

    $return['html'] = $this -> fetchTemplate(NULL,array('parseValues' => $parseValues));
    return $return;
  }

}

?>
