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
 * 'html_options' => array (
 *    'possible_values' => array (
 *      '[LSformat de la valeur clé]' => '[LSformat du nom d'affichage]',
 *      ...
 *      'OTHER_OBJECT' => array (
 *        'object_type' => '[Type d'LSobject]',
 *        'display_name_format' => '[LSformat du nom d'affichage des LSobjects]',
 *        'value_attribute' => '[Nom de l'attribut clé]',
 *        'filter' => '[Filtre de recherche des LSobject]',
 *        'scope' => '[Scope de la recherche]',
 *        'basedn' => '[Basedn de la recherche]'
 *      )
 *    )
 * ),
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
      LSerror :: addErrorCode('LSform_06',$this -> name);
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
    if (is_array($this -> config['html_options']['possible_values'])) {
      foreach($this -> config['html_options']['possible_values'] as $val_name => $val) {
        if($val_name==='OTHER_OBJECT') {
          if ((!isset($val['object_type'])) || (!isset($val['value_attribute']))) {
            LSerror :: addErrorCode('LSattr_html_select_list_01',$this -> name);
            break;
          }
          if (!LSsession :: loadLSclass('LSsearch')) {
            return;
          }
          
          $param=array(
            'filter' => (isset($val['filter'])?$val['filter']:null),
            'basedn' => (isset($val['basedn'])?$val['basedn']:null),
            'scope'  => (isset($val['scope'])?$val['scope']:null),
            'displayFormat' => (isset($val['display_name_format'])?$val['display_name_format']:null),
          );
          
          
          
          if ($val['value_attribute']!='dn') {
            $param['attributes'][] = $val['value_attribute'];
          }
          
          $LSsearch = new LSsearch($val['object_type'],'LSattr_html_select_list',$param,true);
          $LSsearch -> run();
          if(($val['value_attribute']=='dn')||($val['value_attribute']=='%{dn}')) {
            $retInfos = $LSsearch -> listObjectsName();
          }
          else {
            $list = $LSsearch -> getSearchEntries();
            foreach($list as $entry) {
              $key = $entry -> get($val['value_attribute']);
              if(is_array($key)) {
                $key = $key[0];
              }
              $retInfos[$key]=$entry -> displayName;
            }
          }
        }
        else {
          $val_name=$this->attribute->ldapObject->getFData($val_name);
          $val=$this->attribute->ldapObject->getFData(__($val));
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
LSerror :: defineError('LSattr_html_select_list_01',
_("LSattr_html_select_list : Configuration data are missing to generate the select list of the attribute %{attr}.")
);
?>
