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
 * Element supannCompositeAttribute d'un formulaire pour LdapSaisie
 *
 * Cette classe permet de gérer les attributs composite supann en la déclinant.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannCompositeAttribute extends LSformElement {

  var $template = 'LSformElement_supannCompositeAttribute.tpl';
  var $fieldTemplate = 'LSformElement_supannCompositeAttribute_field.tpl';

  /*
   * Composants des valeurs composites :
   *
   * Format :
   *   array (
   *     '[clé composant1]' => array (
   *       'label' => '[label composant]',
   *       'type' => '[type de composant]',
   *       'table' => '[table de nomenclature correspondante]',
   *       'required' => '[booléen obligatoire]'
   *     ),
   *     '[clé composant 2]' => array (
   *       [...]
   *     ),
   *     [...]
   *   )
   * Types :
   *   - 'table' => Composant alimenté à partir d'une table issu de la
   *                nomenclature SUPANN. Le paramètre 'table' permet alors
   *                de spécifier quel table SUPANN intéroger.
   *   - 'codeEntite' => Composant stockant le code d'une entite SUPANN de
   *                     l'annuaire.
   *   - 'text' => saisie manuelle
   *
   */
  var $components = array ();

  var $_postParsedData=null;

 /**
  * Retourne les infos d'affichage de l'élément
  *
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  public function getDisplay(){
    $return = $this -> getLabelInfos();

    $parseValues=array();
    $invalidValues=array();
    foreach($this -> values as $val) {
      $keyValue=supannParseCompositeValue($val);
      if ($keyValue) {
        $parseValue=array('value' => $val);
        foreach($keyValue as $key => $value) {
          $parseValue[$key]=$this -> translateComponentValue($key,$value);
        }
        $parseValues[]=$parseValue;
      }
      else {
	    $invalidValues[]=$val;
	  }
    }

    $return['html'] = $this -> fetchTemplate(NULL,
		array(
			'parseValues' => $parseValues,
			'components' => $this -> components
		)
	);
	LSsession :: addCssFile('LSformElement_supannCompositeAttribute.css');
	if (!$this -> isFreeze()) {
		LSsession :: addJSconfigParam(
			$this -> name,
			array(
				'searchBtn' => _('Modify'),
				'noValueLabel' => _('No set value'),
				'noResultLabel' => _('No result'),
				'components' => $this->components
			)
		);
		LSsession :: addJSscript('LSformElement_supannCompositeAttribute_field_value_component.js');
		LSsession :: addJSscript('LSformElement_supannCompositeAttribute_field_value.js');
		LSsession :: addJSscript('LSformElement_supannCompositeAttribute_field.js');
		LSsession :: addJSscript('LSformElement_supannCompositeAttribute.js');
	}
    return $return;
  }


 /**
  * Retourne le code HTML d'un champ vide
  *
  * @retval string Code HTML d'un champ vide.
  */
  public function getEmptyField() {
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
			'label' => 'no',
			'value' => $val,
		);
		if (isset($this -> components[$c])) {
			if ($this -> components[$c]['type']=='table') {
				$pv=supannParseLabeledValue($val);
				if ($pv) {
					$retval['label'] = $pv['label'];
					$retval['translated'] = supannGetNomenclatureLabel($this -> components[$c]['table'],$pv['label'],$pv['value']);
				}
			}
			elseif ($this -> components[$c]['type']=='codeEntite') {
				$retval['translated']=supanGetEntiteNameById($val);
			}
			//elseif type == 'text' => aucune transformation
		}
		return $retval;
	}

  /**
   * Recupère la valeur de l'élement passée en POST
   *
   * Cette méthode vérifie la présence en POST de la valeur de l'élément et la récupère
   * pour la mettre dans le tableau passer en paramètre avec en clef le nom de l'élément
   *
   * @param[in] &$return array Reference of the array for retreived values
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  public function getPostData(&$return, $onlyIfPresent=false) {
    if ($onlyIfPresent) {
      self :: log_warning("getPostData : does not support \$onlyIfPresent mode => Post data ignored");
      return true;
    }

    if($this -> isFreeze()) {
      return true;
    }

    $count=0;
    $end=false;
    $parseValues=array();
    $return[$this -> name]=array();
    while ($end==false) {
		$value="";
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
				if ($cconf['type']=='table') {
					$pv=supannParseLabeledValue($parseValue[$c]);
					if ($pv) {
						if (!supannValidateNomenclatureValue($cconf['table'],$pv['label'],$pv['value'])) {
							$errors[]=getFData(__('Invalid value for component %{c}.'),__($cconf['label']));
						}
					}
					else {
						$errors[]=getFData(__('Unparsable value for component %{c}.'),__($cconf['label']));
					}
				}
				elseif ($cconf['type']=='codeEntite') {
					if (!supannValidateEntityId($parseValue[$c])) {
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
				$value.="[".$c."=".$parseValue[$c].']';
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
				$return[$this -> name][]=$value;
				$parseValues[]=$parseValue;
			}
			$count++;
		}
	}
	$this -> _postParsedData=$parseValues;
    return true;
  }

  /**
   * This ajax method is used by the searchComponentPossibleValues function of the form element.
   *
   * @param[in] $data The address to the array of data witch will be return by the ajax request
   *
   * @retval void
   **/
  public static function ajax_searchComponentPossibleValues(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['component'])) && (isset($_REQUEST['pattern'])) && (isset($_REQUEST['idform'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $form = $object -> getForm($_REQUEST['idform']);
        $field=$form -> getElement($_REQUEST['attribute']);
        if (isset($field->components[$_REQUEST['component']])) {
			$data['possibleValues'] = $field -> searchComponentPossibleValues($_REQUEST['component'],$_REQUEST['pattern']);
		}
      }
    }
  }

  private function searchComponentPossibleValues($c,$pattern) {
	  $pattern=strtolower($pattern);
	  $retval=array();
	  if (isset($this -> components[$c])) {
		  if ($this -> components[$c]['type'] == 'table') {
			  $table=supannGetNomenclatureTable($this -> components[$c]['table']);
			  foreach($table as $label => $values) {
				  foreach($values as $v => $txt) {
					if (strpos(strtolower($txt),$pattern)!==false) {
						$retval[]=array(
							'label' => $label,
							'value' => "{".$label."}".$v,
							'translated' => $txt
						);
					}
				  }
			  }
		  }
		  elseif ($this -> components[$c]['type'] == 'codeEntite') {
			  foreach (supannSearchEntityByPattern($pattern) as $code => $displayName) {
				  $retval[]=array(
					'label' => 'no',
					'value' => $code,
					'translated' => $displayName
				  );
			  }
		  }
	  }
	  return $retval;
  }

}
