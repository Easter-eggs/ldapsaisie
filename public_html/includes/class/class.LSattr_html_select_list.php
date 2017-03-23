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

  var $LSformElement_type = 'select';

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
    $element=parent::addToForm($form,$idForm,$data);

    if ($element) {
      // Mise en place de la regle de verification des donnees
      $form -> addRule($this -> name, 'LSformElement_select_validValue', array('msg'=> _('Invalid value'),'params' => array('possible_values' => $possible_values)) );
    }
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
      foreach($this -> config['html_options']['possible_values'] as $val_key => $val_label) {
        if($val_key==='OTHER_OBJECT') {
          $objInfos=$this -> getLSobjectPossibleValues($val_label);
          $retInfos=self :: _array_merge($retInfos,$objInfos);
        }
	elseif (is_array($val_label)) {
		if (!isset($val_label['possible_values']) || !is_array($val_label['possible_values']) || !isset($val_label['label']))
			continue;
		$subRetInfos=array();
		foreach($val_label['possible_values'] as $vk => $vl) {
			if ($vk==='OTHER_OBJECT') {
				$objInfos=$this -> getLSobjectPossibleValues($vl);
				$subRetInfos=self :: _array_merge($subRetInfos,$objInfos);
			}
			else {
				$vk=$this->attribute->ldapObject->getFData($vk);
				$vl=$this->attribute->ldapObject->getFData(__($vl));
				$subRetInfos[$vk]=$vl;
			}
		}
		$this -> _sort($subRetInfos);
		$retInfos[] = array (
			'label' => $this->attribute->ldapObject->getFData(__($val_label['label'])),
			'possible_values' => $subRetInfos
		);
	}
        else {
          $val_key=$this->attribute->ldapObject->getFData($val_key);
          $val_label=$this->attribute->ldapObject->getFData(__($val_label));
          $retInfos[$val_key]=$val_label;
        }
      }
    }

    $this -> _sort($retInfos);

    return $retInfos;
  }

  /**
   * Merge arrays preserving keys (string or numeric)
   *
   * As array_merge PHP function, this function merge arrays but
   * this method permit to preverve key even if it's numeric key.
   *
   * @retval array Merged array
   **/
  protected function _array_merge() {
    $ret=array();
    foreach(func_get_args() as $a) {
      foreach($a as $k => $v) {
        $ret[$k]=$v;
      }
    }
    return $ret;
  }

  /**
   * Apply sort feature on possible values if this feature is enabled
   *
   * @param[in] &$retInfos array Possible values array reference to sort
   *
   * @retval void
   **/
  protected function _sort(&$retInfos) {
    if (!isset($this -> config['html_options']['sort']) || $this -> config['html_options']['sort']) {
      uasort($retInfos,array($this,'_sortTwoValues'));
    }
  }

  /**
   * Retourne un tableau des valeurs possibles d'un type d'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */
  protected function getLSobjectPossibleValues($conf) {
    $retInfos = array();

    if ((!isset($conf['object_type'])) || ((!isset($conf['value_attribute'])) && (!isset($conf['values_attribute'])))) {
      LSerror :: addErrorCode('LSattr_html_select_list_01',$this -> name);
      break;
    }
    if (!LSsession :: loadLSclass('LSsearch')) {
      return;
    }

    $param=array(
      'filter' => (isset($conf['filter'])?$conf['filter']:null),
      'basedn' => (isset($conf['basedn'])?$conf['basedn']:null),
      'scope'  => (isset($conf['scope'])?$conf['scope']:null),
      'displayFormat' => (isset($conf['display_name_format'])?$conf['display_name_format']:null),
      'onlyAccessible' => (isset($conf['onlyAccessible'])?$conf['onlyAccessible']:False),
    );

    if (isset($conf['value_attribute']) && $conf['value_attribute']!='dn') {
      $param['attributes'][] = $conf['value_attribute'];
    }
    if (isset($conf['values_attribute'])) {
      $param['attributes'][] = $conf['values_attribute'];
    }

    $LSsearch = new LSsearch($conf['object_type'],'LSattr_html_select_list',$param,true);
    $LSsearch -> run();
    if (isset($conf['value_attribute'])) {
      if(($conf['value_attribute']=='dn')||($conf['value_attribute']=='%{dn}')) {
        $retInfos = $LSsearch -> listObjectsName();
      }
      else {
        $list = $LSsearch -> getSearchEntries();
        foreach($list as $entry) {
          $key = $entry -> get($conf['value_attribute']);
          if(is_array($key)) {
            $key = $key[0];
          }
          $retInfos[$key]=$entry -> displayName;
        }
      }
    }
    if (isset($conf['values_attribute'])) {
      $list = $LSsearch -> getSearchEntries();
      foreach($list as $entry) {
        $keys = $entry -> get($conf['values_attribute']);
        if (!is_array($keys)) $keys=array($keys);
        foreach ($keys as $key) {
          $retInfos[$key]=$key;
        }
      }
    }

    $this -> _sort($retInfos);

    return $retInfos;
  }

  /**
   * Function use with uasort to sort two values
   *
   * @param[in] $va string One value
   * @param[in] $vb string One value
   *
   * @retval int Value for uasort
   **/
  protected function _sortTwoValues(&$va,&$vb) {
    if (isset($this -> config['html_options']['sortDirection']) && $this -> config['html_options']['sortDirection']=='DESC') {
      $dir=-1;
    }
    else {
      $dir=1;
    }

    if (is_array($va)) {
      $nva=$va['label'];
    }
    else {
      $nva=$va;
    }

    if (is_array($vb)) {
      $nvb=$vb['label'];
    }
    else {
      $nvb=$vb;
    }

    if ($nva == $nvb) return 0;

    $val = strcoll(strtolower($nva), strtolower($nvb));

    return $val*$dir;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSattr_html_select_list_01',
_("LSattr_html_select_list : Configuration data are missing to generate the select list of the attribute %{attr}.")
);
