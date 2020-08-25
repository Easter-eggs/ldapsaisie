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
  public function addToForm (&$form,$idForm,$data=NULL) {
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
   * Retourne un tableau des valeurs possibles de la liste de l'attribut courant
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */
  protected function getPossibleValues() {
    return static :: _getPossibleValues(
      $this -> getConfig('html_options'),
      $this -> name,
      $this->attribute->ldapObject
    );
  }

  /**
   * Retourne un tableau des valeurs possibles de la liste
   *
   * @param[in] $options Attribute options (optional)
   * @param[in] $name Attribute name (optional)
   * @param[in] &$ldapObject Related LSldapObject (optional)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */
  public static function _getPossibleValues($options=false,$name=false,&$ldapObject=false) {
    $retInfos = array();
    if (isset($options['possible_values']) && is_array($options['possible_values'])) {
      foreach($options['possible_values'] as $val_key => $val_label) {
        if($val_key==='OTHER_OBJECT') {
          $objInfos=static :: getLSobjectPossibleValues($val_label,$options,$name);
          $retInfos=static :: _array_merge($retInfos,$objInfos);
        }
        elseif($val_key==='OTHER_ATTRIBUTE') {
          $attrInfos=static :: getLSattributePossibleValues($val_label, $options, $name, $ldapObject);
          $retInfos=static :: _array_merge($retInfos,$attrInfos);
        }
	elseif (is_array($val_label)) {
		if (!isset($val_label['possible_values']) || !is_array($val_label['possible_values']) || !isset($val_label['label']))
			continue;
		$subRetInfos=array();
		foreach($val_label['possible_values'] as $vk => $vl) {
			if ($vk==='OTHER_OBJECT') {
				$objInfos=static :: getLSobjectPossibleValues($vl,$options,$name);
				$subRetInfos=static :: _array_merge($subRetInfos,$objInfos);
			}
			else {
				$vk=$ldapObject->getFData($vk);
				if (isset($options['translate_labels']) && !$options['translate_labels']) {
					$vl=$ldapObject->getFData($vl);
				}
				else {
					$vl=$ldapObject->getFData(__($vl));
				}
				$subRetInfos[$vk]=$vl;
			}
		}
		static :: _sort($subRetInfos,$options);
		if (isset($options['translate_labels']) && !$options['translate_labels']) {
			$subRetLabel = $ldapObject->getFData($val_label['label']);
		}
		else {
			$subRetLabel = $ldapObject->getFData(__($val_label['label']));
		}
		$retInfos[] = array (
			'label' => $subRetLabel,
			'possible_values' => $subRetInfos
		);
	}
        else {
          $val_key=$ldapObject->getFData($val_key);
          if (isset($options['translate_labels']) && !$options['translate_labels']) {
            $val_label=$ldapObject->getFData($val_label);
          }
          else {
            $val_label=$ldapObject->getFData(__($val_label));
          }
          $retInfos[$val_key]=$val_label;
        }
      }
    }

    static :: _sort($retInfos,$options);

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
  protected static function _array_merge() {
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
   * @param[in] $options array|false Attribute options
   *
   * @retval void
   **/
  protected static function _sort(&$retInfos, $options) {
    if (!isset($options['sort']) || $options['sort']) {
      if (isset($options['sortDirection']) && $options['sortDirection']=='DESC') {
        uasort($retInfos,array('LSattr_html_select_list','_sortTwoValuesDesc'));
      }
      else {
        uasort($retInfos,array('LSattr_html_select_list','_sortTwoValuesAsc'));
      }
    }
  }

  /**
   * Function use with uasort to sort two values in ASC order
   *
   * @param[in] $va string One value
   * @param[in] $vb string One value
   *
   * @retval int Value for uasort
   **/
  protected static function _sortTwoValuesAsc(&$va,&$vb) {
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

    return strcoll(strtolower($nva), strtolower($nvb));
  }

  /**
   * Function use with uasort to sort two values in DESC order
   *
   * @param[in] $va string One value
   * @param[in] $vb string One value
   *
   * @retval int Value for uasort
   **/
  protected static function _sortTwoValuesDesc(&$va,&$vb) {
    return (-1 * static :: _sortTwoValuesAsc($va,$vb));
  }


  /**
   * Retourne un tableau des valeurs possibles d'un type d'objet
   *
   * @param[in] $conf OTHER_OBJECT configuration array
   * @param[in] $options array|false Attribute options
   * @param[in] $name Attribute name
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */
  protected static function getLSobjectPossibleValues($conf, $options, $name) {
    $retInfos = array();

    if ((!isset($conf['object_type'])) || ((!isset($conf['value_attribute'])) && (!isset($conf['values_attribute'])))) {
      LSerror :: addErrorCode('LSattr_html_select_list_01',$name);
      return;
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

    static :: _sort($retInfos,$options);

    return $retInfos;
  }

  /**
   * Retourne un tableau des valeurs possibles d'un autre attribut
   *
   * @param[in] $attr OTHER_ATTRIBUTE configuration value
   * @param[in] $options array|false Attribute options
   * @param[in] $name Attribute name
   * @param[in] $LSldapObject LSldapObject reference
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */
  protected function getLSattributePossibleValues($attr, $options ,$name ,&$ldapObject) {
    $retInfos=array();
    if (is_string($attr)) {
      if (isset($ldapObject->attrs[$attr]) && $ldapObject->attrs[$attr] instanceof LSattribute) {
        $attr_values = $ldapObject->attrs[$attr]->getValue();
        if (!$attr_values)
          $attr_values = array();
        elseif (!is_array($attr_values))
          $attr_values = array($attr_values);
        if (isset($options['translate_labels']) && !$options['translate_labels']) {
          foreach($attr_values as $attr_value)
            $retInfos[$attr_value] = $attr_value;
        }
        else {
          foreach($attr_values as $attr_value)
            $retInfos[$attr_value] = __($attr_value);
        }
      }
      else
        LSerror :: addErrorCode('LSattr_html_select_list_02',$attr);
    }
    elseif (is_array($attr)) {
      if (isset($attr['attr'])) {
        if (isset($ldapObject->attrs[$attr['attr']]) && $ldapObject->attrs[$attr['attr']] instanceof LSattribute) {
          if (isset($attr['json_component_key'])) {
            if (get_class($ldapObject->attrs[$attr['attr']]->html) == 'LSattr_html_jsonCompositeAttribute') {
              $attr_values = $ldapObject->attrs[$attr['attr']]->getValue();
              if (!$attr_values)
                $attr_values = array();
              elseif (!is_array($attr_values))
                $attr_values = array($attr_values);
              foreach($attr_values as $attr_value) {
                $value_data = @json_decode($attr_value, true);
                if (!isset($value_data[$attr['json_component_key']])) {
                  LSerror :: addErrorCode('LSattr_html_select_list_05', array('attr' => $attr['attr'], 'value' => $attr_value, 'component' => $attr['json_component_key']));
                  return $retInfos;
                }
                $key = $value_data[$attr['json_component_key']];

                if (isset($attr['json_component_label'])) {
                  if (!isset($value_data[$attr['json_component_label']])) {
                    LSerror :: addErrorCode('LSattr_html_select_list_05', array('attr' => $attr['attr'], 'value' => $attr_value, 'component' => $attr['json_component_label']));
                    return $retInfos;
                  }
                  $label = $value_data[$attr['json_component_label']];
                }
                else
                  $label = $key;

                $retInfos[$key] = $label;
              }
            }
            else
              LSerror :: addErrorCode('LSattr_html_select_list_03',$attr['attr']);
          }
          else
            $retInfos = static :: getLSattributePossibleValues($attr['attr'], $options ,$name ,$ldapObject);
        }
        else
          LSerror :: addErrorCode('LSattr_html_select_list_02',$attr['attr']);
      }
      else {
        foreach($attr as $sub_attr => $sub_label) {
          $subRetInfos = static :: getLSattributePossibleValues($sub_attr, $options ,$name ,$ldapObject);
          static :: _sort($subRetInfos,$options);
          $retInfos[] = array (
            'label' => $sub_label,
            'possible_values' => $subRetInfos
          );
        }
      }
    }
    static :: _sort($retInfos,$options);
    return $retInfos;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSattr_html_select_list_01',
___("LSattr_html_select_list : Configuration data are missing to generate the select list of the attribute %{attr}.")
);
LSerror :: defineError('LSattr_html_select_list_02',
___("LSattr_html_select_list : Invalid attribute %{attr} reference as OTHER_ATTRIBUTE possible values.")
);
LSerror :: defineError('LSattr_html_select_list_03',
___("LSattr_html_select_list : Attribute %{attr} referenced as OTHER_ATTRIBUTE possible values is not a jsonCompositeAttribute.")
);
LSerror :: defineError('LSattr_html_select_list_04',
___("LSattr_html_select_list : Fail to decode the following attribute %{attr} value as JSON : %{value}")
);
LSerror :: defineError('LSattr_html_select_list_05',
___("LSattr_html_select_list : No component %{component} found in the following attribute %{attr} JSON value : %{value}")
);
