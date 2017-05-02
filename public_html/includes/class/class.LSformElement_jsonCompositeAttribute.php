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

/**
 * Element jsonCompositeAttribute d'un formulaire pour LdapSaisie
 *
 * Cette classe permet de gérer les attributs composite encodé en JSON.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_jsonCompositeAttribute extends LSformElement {

  var $template = 'LSformElement_jsonCompositeAttribute.tpl';
  var $fieldTemplate = 'LSformElement_jsonCompositeAttribute_field.tpl';

  function LSformElement_jsonCompositeAttribute (&$form, $name, $label, $params,&$attr_html){
    parent :: LSformElement($form, $name, $label, $params,$attr_html);
    if (is_array($this -> params['html_options']['components'])) {
      $this -> components = $this -> params['html_options']['components'];
    }
  }
  
  /*
   * Composants des valeurs composites : 
   * 
   * Format :
   *   array (
   *     '[clé composant1]' => array (
   *       'label' => '[label composant]',
   *       'type' => '[type de composant]',
   *       'required' => '[booléen obligatoire]'
   *     ),
   *     '[clé composant 2]' => array (
   *       'label' => 'label2',
   *       'type'  => 'select_list',
   *       'options' => array([config as LSattr_html_select_list html_options]),
   *     ),
   *     [...]
   *   )
   * Types :
   *   - 'select_list' => Composant alimenté à partir d'une liste de valeur configurable
   *                      de la même manière qu'un LSattr_html :: select_list.
   *   - 'text'        => saisie manuelle
   * 
   */
  var $components = array();
  
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
    $invalidValues=array();
    foreach($this -> values as $val) {
      $decodedValue=json_decode($val, true);
      if (is_array($decodedValue)) {
        $parseValue=array('value' => $val);
        foreach($decodedValue as $c => $cvalue) {
          $parseValue[$c]=$this -> translateComponentValue($c,$cvalue);
        }
        $parseValues[]=$parseValue;
      }
      else {
        $invalidValues[]=$val;
      }
    }

    $components = $this -> components;
    foreach($components as $c => $cconf) {
      if ($cconf['type']=='select_list') {
        $components[$c]['possible_values']=$this -> getSelectListComponentPossibleValues($c);
      }
    }

    $return['html'] = $this -> fetchTemplate(NULL,
      array(
        'parseValues' => $parseValues,
        'components' => $components
      )
    );
    LSsession :: addCssFile('LSformElement_jsonCompositeAttribute.css');
    return $return;
  }
  
    
 /**
  * Retourne le code HTML d'un champ vide
  *
  * @retval string Code HTML d'un champ vide.
  */
  function getEmptyField() {
    return $this -> fetchTemplate($this -> fieldTemplate,array('components' => $this -> components));
  }
  
  /**
   * Traduit la valeur d'un composant
   * 
   * Retourne un array contenant :
   *  - label : l'étiquette de la valeur ou 'no' sinon
   *  - value : la valeur brute
   *  - translated : la valeur traduite ou la valeur elle même
   * 
   * @param[in] $c string Le nom du composant 
   * @param[in] $val string La valeur
   * 
   * @retval array
   **/
  function translateComponentValue($c,$val) {
    $retval = array (
      'translated' => $val,
      'value' => $val,
    );
    if (isset($this -> components[$c])) {
      if ($this -> components[$c]['type']=='select_list') {
        $retval['translated'] = $this -> getSelectListComponentValueLabel($c,$val);
      }
      //elseif type == 'text' => aucune transformation
    }
    return $retval;
  }

  protected $_cache_getSelectListComponentPossibleValues=array();
  protected function getSelectListComponentPossibleValues($c) {
    if (!isset($this -> _cache_getSelectListComponentPossibleValues[$c])) {
      if (!LSsession :: loadLSclass('LSattr_html_select_list')) return;
      $this -> _cache_getSelectListComponentPossibleValues[$c]=LSattr_html_select_list :: getPossibleValues($this -> components[$c]['options'], $this -> name, $this->attr_html->attribute->ldapObject);
    }
    return $this -> _cache_getSelectListComponentPossibleValues[$c];
  }

  protected function getSelectListComponentValueLabel($c,$value) {
    if ($this -> getSelectListComponentPossibleValues($c)) {
      foreach ($this -> _cache_getSelectListComponentPossibleValues[$c] as $v => $label) {
        if (is_array($label)) {
          if (!isset($label['possible_values'])) continue;
          foreach ($label['possible_values'] as $vk => $vl)
            if ($vk == $$value) return $vl;
        }
        if ($v == $value) return $label;
      }
    }
    return;
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
   
    $count=0;
    $end=false;
    $return[$this -> name]=array();
    while ($end==false) {
      $value=array();
      $parseValue=array();
      $errors=array();
      $unemptyComponents=array();
      foreach ($this -> components as $c => $cconf) {
        if (isset($_POST[$this -> name.'__'.$c][$count])) {
          $parseValue[$c]=$_POST[$this -> name.'__'.$c][$count];
          if ($cconf['required'] && empty($parseValue[$c])) {
            $errors[]=getFData(__('Component %{c} must be defined'),__($cconf['label']));
            continue;
          }
          if (empty($parseValue[$c])) {
            continue;
          }
          $unemptyComponents[]=$c;
          if ($cconf['type']=='select_list') {
            if (!$this -> getSelectListComponentValueLabel($c, $parseValue[$c])) {
              $errors[]=getFData(__('Invalid value for component %{c}.'),__($cconf['label']));
            }
          }
          if (is_array($cconf['check_data'])) {
            foreach($cconf['check_data'] as $ruleType => $rconf) {
              $className='LSformRule_'.$ruleType;
              if (LSsession::loadLSclass($className)) {
                $r=new $className();
                if (!$r -> validate($parseValue[$c],$rconf,$this)) {
                  if (isset($rconf['msg'])) {
                    $errors[]=getFData(__($rconf['msg']),__($cconf['label']));
                  }
                  else {
                    $errors[]=getFData(__('Invalid value for component %{c}.'),__($cconf['label']));
                  }
                }
              }
              else {
                $errors[]=getFData(__("Can't validate value of component %{c}."),__($cconf['label']));
              }
            }
          }
          $value[$c]=$parseValue[$c];
        }
        else {
          // end of value break
          $end=true;
          break;
        }
        
      }
      if (!$end) {
        if (!empty($unemptyComponents)) {
          foreach($errors as $e) {
            $this -> form -> setElementError($this -> attr_html,$e);
          }
          $return[$this -> name][]=json_encode($value);
        }
        $count++;
      }
    }
    return true;
  }
    
}
