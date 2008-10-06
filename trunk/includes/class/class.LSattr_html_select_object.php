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
 * Type d'attribut HTML select_object
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_html_select_object extends LSattr_html{

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
    $this -> config['attrObject'] = $this;
    $element=$form -> addElement('select_object', $this -> name, $this -> config['label'],$this -> config,$this);
    if(!$element) {
      $GLOBALS['LSerror'] -> addErrorCode(206,$this -> name);
      return;
    }
    if ($data) {
      $values=$this -> getValues($data);
      if ($values) {
        $element -> setValue($values);
      }
    }
    $element -> setSelectableObject($this -> config['selectable_object']['object_type']);
    return $element;
  }

  /**
   * Effectue les tâches nécéssaires au moment du rafraichissement du formulaire
   * 
   * Récupère un array du type array('DNs' => 'displayValue') à partir d'une
   * liste de DNs.
   * 
   * @param[in] $data mixed La valeur de l'attribut (liste de DNs)
   * 
   * @retval mixed La valeur formatée de l'attribut (array('DNs' => 'displayValue'))
   **/
  function refreshForm($data) {
    return $this -> getValues($data);
  }

  /**
   * Retourne un tableau des valeurs possibles de la liste
   *
   * @param[in] mixed Tableau des valeurs de l'attribut
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */ 
  function getValues($values=NULL) {
    $retInfos = array();
    if (isset($this -> config['selectable_object'])) {
      $conf=$this -> config['selectable_object'];
      if (!isset($conf['object_type'])) {
        $GLOBALS['LSerror'] -> addErrorCode(102,$this -> name);
        return;
      }
      
      if (!$GLOBALS['LSsession'] -> loadLSobject($conf['object_type'])) {
        $GLOBALS['LSerror'] -> addErrorCode(1004,$conf['object_type']);
        return;
      }
      
      if ((is_array($values))&&(!empty($values))) {
        if(($conf['value_attribute']=='dn')||($conf['value_attribute']=='%{dn}')) {
          $list=array();
          foreach($values as $dn) {
            $obj=new $conf['object_type']();
            if ($obj -> loadData($dn)) {
              $list[]=$obj;
            }
          }
        }
        else {
          $filter='';
          foreach($values as $val) {
            if (!empty($val)) {
              $filter.='('.$conf['value_attribute'].'='.$val.')';
            }
          }
          if ($filter!='') {
            $filter='(|'.$filter.')';
            $obj = new $conf['object_type']();
            $list = $obj -> listObjects($filter);
          }
          else {
            $list=array();
          }
        }
        if(($conf['value_attribute']=='dn')||($conf['value_attribute']=='%{dn}')) {
          for($i=0;$i<count($list);$i++) {
            $retInfos[$list[$i] -> dn]=$list[$i] -> getDisplayValue($conf['display_attribute']);
            $DNs[]=$list[$i] -> dn;
          }
        }
        else {
          for($i=0;$i<count($list);$i++) {
            $key = $val['value_attribute'] -> getValue();
            $key = $key[0];
            $retInfos[$list[$i] -> attrs[$key]]=$list[$i] -> getDisplayValue($conf['display_attribute']);
            $DNs[]=$list[$i] -> dn;
          }
        }
      }
      else {
        return false;
      }
      $_SESSION['LSselect'][$conf['object_type']]=$DNs;
      return $retInfos;
    }
    return false;
    
  }


  /**
   * Retourne un tableau des valeurs de l'attribut à partir de la variable session
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */
  function getValuesFromSession() {
    $retInfos = array();
    if (isset($this -> config['selectable_object'])) {
      $conf=$this -> config['selectable_object'];
      if (!isset($conf['object_type'])) {
        $GLOBALS['LSerror'] -> addErrorCode(102,$this -> name);
        break;
      }
      
      if(is_array($_SESSION['LSselect'][$conf['object_type']])) {
        foreach($_SESSION['LSselect'][$conf['object_type']] as $dn) {
          $obj = new $conf['object_type']();
          if ($obj->loadData($dn)) {
            if(($conf['value_attribute']=='dn')||($conf['value_attribute']=='%{dn}')) {
              $retInfos[$obj -> dn]=$obj -> getDisplayValue($conf['display_attribute']);
            }
            else {
              $key = $val['value_attribute'] -> getValue();
              $key = $key[0];
              $retInfos[$obj -> attrs[$key]]=$obj -> getDisplayValue($conf['display_attribute']);
            }
            $DNs[]=$dn;
          }
        }
      }
      else {
        return false;
      }
      $_SESSION['LSselect'][$conf['object_type']]=$DNs;
      return $retInfos;
    }
    return false;
  }

}

?>
