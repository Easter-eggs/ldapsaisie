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
      $values=$this -> getFormValues($data);
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
    return $this -> getFormValues($data,true);
  }

  /**
   * Retourne un tableau des valeurs de l'attribut à partir des valeurs du formulaire
   *
   * @param[in] mixed Tableau des valeurs du formulaire
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array  Tableau des valeurs de l'attribut
   */ 
  function getValuesFromFormValues($values=NULL) {
    $retValues = array();
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
        $obj=new $conf['object_type']();
        foreach($values as $dn => $name) {
          if ($obj -> loadData($dn)) {
            if(($conf['value_attribute']=='dn')||($conf['value_attribute']=='%{dn}')) {
              $val = $dn;
            }
            else {
              $val = $obj -> getValue($conf['value_attribute']);
              $val = $val[0];
            }
            if (empty($val)) {
              continue;
            }
            $retValues[]=$val;
          }
        }
        return $retValues;
      }
    }
    return;
  }

  /**
   * Retourne un tableau des objects selectionnés
   *
   * @param[in] mixed $values Tableau des valeurs de l'attribut
   * @param[in] boolean $fromDNs True si les valeurs passées en paramètre sont des DNs
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des objects selectionés avec en clé
   *               le DN et en valeur ce qui sera affiché.
   */ 
  function getFormValues($values=NULL,$fromDNs=false) {
    $retInfos = array();
    $DNs=array();
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
        if(($conf['value_attribute']=='dn')||($conf['value_attribute']=='%{dn}')||$fromDNs) {
          $DNs=$values;
          $obj = new $conf['object_type']();
          foreach($DNs as $dn) {
            if($obj -> loadData($dn)) {
              $retInfos[$dn] = $obj -> getDisplayValue($conf['display_attribute']);
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
            $listobj = $obj -> listObjects($filter);
            foreach($listobj as $one) {
              $DNs[]=$one -> getDn();
              $retInfos[$one -> getDn()] = $one -> getDisplayValue($conf['display_attribute']);
            }
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
   * @retval array Tableau associatif des objects selectionnés avec en clé
   *               le DN et en valeur ce qui sera affiché.
   */
  function getValuesFromSession() {
    if(is_array($_SESSION['LSselect'][$this -> config['selectable_object']['object_type']])) {
      return $this -> getFormValues($_SESSION['LSselect'][$this -> config['selectable_object']['object_type']],true);
    }
    return false;
  }

}

?>
