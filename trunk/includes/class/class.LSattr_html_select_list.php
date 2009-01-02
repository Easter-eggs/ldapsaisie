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

  /**
   * Ajoute l'attribut au formualaire passer en paramètre
   *
   * @param[in] &$form LSform Le formulaire
   * @param[in] $idForm L'identifiant du formulaire
   * @param[in] $data Valeur du champs du formulaire
   *
   * @retval LSformElement L'element du formulaire ajouté
   */
  function addToForm (&$form,$idForm,$data=NULL) {
    $possible_values=$this -> getPossibleValues();
    $this -> config['text_possible_values'] = $possible_values;
    $element=$form -> addElement('select', $this -> name, $this -> config['label'],$this -> config, $this);
    if(!$element) {
      $GLOBALS['LSerror'] -> addErrorCode('LSform_06',$this -> name);
      return;
    }
    if ($data) {
      $element -> setValue($data);
    }
   
    // Mise en place de la regle de verification des donnees
    $regex_check_data='/';
    foreach ($possible_values as $val => $text) {
      if($regex_check_data=='/')
        $regex_check_data.='^'.preg_quote($val,'/').'$';
      else
        $regex_check_data.='|^'.preg_quote($val,'/').'$';
    }
    $regex_check_data.='/';
    $form -> addRule($this -> name, 'regex', array('msg'=> 'Valeur incorrect','params' => array('regex' => $regex_check_data)) );
    // On retourne un pointeur vers l'element ajouter
    return $element;
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
          if ((!isset($val['object_type'])) || (!isset($val['value_attribute']))) {
            $GLOBALS['LSerror'] -> addErrorCode('LSattr_html_select_list_01',$this -> name);
            break;
          }
          if (!$GLOBALS['LSsession'] -> loadLSobject($val['object_type'])) {
            return;
          }
          $obj = new $val['object_type']();
          if($val['scope']) {
            $param=array('scope' => $this -> config['possible_values']['scope']);
          }
          else {
            $param=array();
          }
          
          $param['attributes'] = getFieldInFormat($val['display_attribute']);
          
          if ($val['value_attribute']!='dn') {
            $param['attributes'][] = $val['value_attribute'];
          }
          
          $list = $obj -> search($val['filter'],$this -> config['possible_values']['basedn'],$param);
          if(($val['value_attribute']=='dn')||($val['value_attribute']=='%{dn}')) {
            for($i=0;$i<count($list);$i++) {
              $retInfos[$list[$i]['dn']]=getFData($val['display_attribute'],$list[$i]['attrs']);
            }
          }
          else {
            for($i=0;$i<count($list);$i++) {
              $key = $list[$i]['attrs'][$val['value_attribute']];
              if(is_array($key)) {
                $key = $key[0];
              }
              $retInfos[$key]=getFData($val['display_attribute'],$list[$i]['attrs']);
            }
          }
        }
        else {
          $val_name=$this->attribute->ldapObject->getFData($val_name);
          $val=$this->attribute->ldapObject->getFData($val);
          $retInfos[$val_name]=$val;
        }
      }
    }
    return $retInfos;
  }
  
}

/*
 * Error Codes
 */
$GLOBALS['LSerror_code']['LSattr_html_select_list_01'] = array (
  'msg' => _("LSattr_html_select_list : Configuration data are missing to generate the select list of the attribute %{attr}.")
);
?>
