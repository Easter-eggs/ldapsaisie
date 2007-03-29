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
 * Type d'attribut HTML select_list
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_html_select_list extends LSattr_html{
  
  function addToForm (&$form,$idForm) {
    return $form -> addElement('select', $this -> name, $this -> config['label'],$this -> getPossibleValues());
  }
  
  /**
   * Retourne un tableau des valeurs possibles de la liste
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */	
  function getPossibleValues() {
    $retInfos = array();
    if (isset($this -> config['possible_values'])) {
      foreach($this -> config['possible_values'] as $val_name => $val) {
        if($val_name=='OTHER_OBJECT') {
          //~ print_r($val);
          if ((!isset($val['object_type'])) || (!isset($val['value_attribute']))) {
            $GLOBALS['LSerror'] -> addErrorCode(102,$this -> name);
            break;
          }
          $obj = new $val['object_type']();
          if($val['scope']) {
            $param=array('scope' => $this -> config['possible_values']['scope']);
          }
          else {
            $param=array();
          }
          $list = $obj -> listObjects($val['filter'],$this -> config['possible_values']['basedn'],$param);
          if(($val['value_attribute']=='dn')||($val['value_attribute']=='%{dn}')) {
            for($i=0;$i<count($list);$i++) {
              $retInfos[$list[$i] -> dn]=$list[$i] -> getDisplayValue($val['display_attribute']);
            }
          }
          else {
            for($i=0;$i<count($list);$i++) {
              $retInfos[$list[$i] -> attrs[$val['value_attribute']] -> getValue()]=$list[$i] -> getDisplayValue($val['display_attribute']);
            }
          }
        }
        else {
          $retInfos[$val_name]=$val;
        }
      }
    }
    return $retInfos;
  }
  
}

?>