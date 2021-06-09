<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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

// Messages d'erreur

// Support
LSerror :: defineError('SUPANN_SUPPORT_01',
  ___("SUPANN Support : The constant %{const} is not defined.")
);
LSerror :: defineError('SUPANN_SUPPORT_02',
  ___("SUPANN Support : The LSobject type %{type} does not exist. Can't work with entities..")
);
LSerror :: defineError('SUPANN_SUPPORT_03',
  ___("SUPANN Support : The global array %{array} is not defined.")
);

// Autres erreurs
LSerror :: defineError('SUPANN_01',
  ___("SUPANN: The attribute %{dependency} is missing. Unable to forge the attribute %{attr}.")
);
LSerror :: defineError('SUPANN_02',
  ___("SUPANN: Can't get the basedn of entities. Unable to forge the attribute %{attr}.")
);
LSerror :: defineError('SUPANN_03',
  ___("SUPANN: This entity have children entities and could be deleted.")
);

 /**
  * Verification du support SUPANN par ldapSaisie
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si SUPANN est pleinement supporté, false sinon
  */
  function LSaddon_supann_support() {
    $retval = true;

    $MUST_DEFINE_STRING= array(
      'LS_SUPANN_LASTNAME_ATTR',
      'LS_SUPANN_FIRSTNAME_ATTR',
      'LS_SUPANN_LSOBJECT_ENTITE_TYPE',
      'LS_SUPANN_LSOBJECT_ENTITE_FORMAT_SHORTNAME',
      'LS_SUPANN_ETABLISSEMENT_UAI',
      'LS_SUPANN_ETABLISSEMENT_DN',
      'LS_SUPANN_EPPN_FORMAT',
      'LS_SUPANN_EPUI_FORMAT',
    );

    foreach($MUST_DEFINE_STRING as $string) {
      if ( isset($GLOBALS[$string]) && is_string($GLOBALS[$string])) {
        continue;
      }
      foreach(LSconfig :: get('ldap_servers') as $id => $infos) {
        if ( !isset($infos['globals'][$string]) || !is_string($infos['globals'][$string])) {
          LSerror :: addErrorCode('SUPANN_SUPPORT_01',$string);
          $retval=false;
          continue 2;
        }
      }
    }

    $MUST_DEFINE_ARRAY= array(
      'supannNomenclatures',
    );
    foreach($MUST_DEFINE_ARRAY as $array) {
      if ( !isset($GLOBALS[$array]) || !is_array($GLOBALS[$array])) {
        LSerror :: addErrorCode('SUPANN_SUPPORT_03',$array);
        $retval=false;
      }
    }

    if (isset($GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'])) {
      if ( ! LSsession :: loadLSobject( $GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'] ) ) {
        LSerror :: addErrorCode('SUPANN_SUPPORT_02', $GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE']);
      }
    }

    return $retval;
  }

/***********************************************************************
 * Fonctions de génération de valeurs d'attributs
 **********************************************************************/

 /**
  * Generation du displayName
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string Le displayName ou false si il y a un problème durant la génération
  */
  function generate_displayName($ldapObject) {
    if ( get_class($ldapObject -> attrs[ $GLOBALS['LS_SUPANN_LASTNAME_ATTR'] ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => $GLOBALS['LS_SUPANN_LASTNAME_ATTR'], 'attr' => 'cn'));
      return;
    }
    if ( get_class($ldapObject -> attrs[ $GLOBALS['LS_SUPANN_FIRSTNAME_ATTR'] ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => $GLOBALS['LS_SUPANN_FIRSTNAME_ATTR'], 'attr' => 'cn'));
      return;
    }

    $noms = $ldapObject -> attrs[ $GLOBALS['LS_SUPANN_LASTNAME_ATTR'] ] -> getValue();
    $prenoms = $ldapObject -> attrs[ $GLOBALS['LS_SUPANN_FIRSTNAME_ATTR'] ] -> getValue();

    return ($prenoms[0].' '.$noms[0]);
  }

 /**
  * Generation du CN
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string Le CN ou false si il y a un problème durant la génération
  */
  function generate_cn($ldapObject) {
    if ( get_class($ldapObject -> attrs[ $GLOBALS['LS_SUPANN_LASTNAME_ATTR'] ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => $GLOBALS['LS_SUPANN_LASTNAME_ATTR'], 'attr' => 'cn'));
      return;
    }
    if ( get_class($ldapObject -> attrs[ $GLOBALS['LS_SUPANN_FIRSTNAME_ATTR'] ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => $GLOBALS['LS_SUPANN_FIRSTNAME_ATTR'], 'attr' => 'cn'));
      return;
    }

    $noms = $ldapObject -> attrs[ $GLOBALS['LS_SUPANN_LASTNAME_ATTR'] ] -> getValue();
    $prenoms = $ldapObject -> attrs[ $GLOBALS['LS_SUPANN_FIRSTNAME_ATTR'] ] -> getValue();

    return (withoutAccents($noms[0]).' '.withoutAccents($prenoms[0]));
  }

 /**
  * Generation des valeurs de l'attribut eduPersonOrgUnitDN à partir des
  * valeurs de l'attribut supannEntiteAffectation.
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval array Les valeurs de l'attribut eduPersonOrgUnitDN ou false
  *               si il y a un problème durant la génération
  */
  function generate_eduPersonOrgUnitDN($ldapObject) {
    if ( get_class($ldapObject -> attrs[ 'supannEntiteAffectation' ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => 'supannEntiteAffectation', 'attr' => 'eduPersonOrgUnitDN'));
      return;
    }

    $affectations = $ldapObject -> attrs[ 'supannEntiteAffectation' ] -> getUpdateData();

    $basedn=LSconfig :: get('LSobjects.'.$GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'].'.container_dn').','.LSsession::getTopDn();
    if ($basedn=="") {
      LSerror :: addErrorCode('SUPANN_02','eduPersonOrgUnitDN');
      return;
    }

    $retval=array();
    foreach ($affectations as $aff) {
      $retval[]="supannCodeEntite=".$aff.",$basedn";
    }

    return $retval;
  }

 /**
  * Generation de la valeur de l'attribut eduPersonPrimaryOrgUnitDN
  * à partir de la valeur de l'attribut supannEntiteAffectationPrincipale.
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval array La valeur de l'attribut eduPersonPrimaryOrgUnitDN
  *               ou false si il y a un problème durant la génération
  */
  function generate_eduPersonPrimaryOrgUnitDN($ldapObject) {
    if ( get_class($ldapObject -> attrs[ 'supannEntiteAffectationPrincipale' ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => 'supannEntiteAffectationPrincipale', 'attr' => 'eduPersonPrimaryOrgUnitDN'));
      return;
    }

    $affectations = $ldapObject -> attrs[ 'supannEntiteAffectationPrincipale' ] -> getUpdateData();

    $basedn=LSconfig :: get('LSobjects.'.$GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'].'.container_dn').','.LSsession::getTopDn();
    if ($basedn=="") {
      LSerror :: addErrorCode('SUPANN_02','eduPersonPrimaryOrgUnitDN');
      return;
    }

    $retval=array();
    foreach ($affectations as $aff) {
      $retval[]="supannCodeEntite=".$aff.",$basedn";
    }

    return $retval;
  }

 /**
  * Generation de la valeur de l'attribut eduPersonOrgDN
  * à partir de la valeur de l'attribut supannEtablissement.
  *
  * La valeur sera $GLOBALS['LS_SUPANN_ETABLISSEMENT_DN'] si l'attribut supannEtablissement
  * vaut {UAI}$GLOBALS['LS_SUPANN_ETABLISSEMENT_UAI'].
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval array La valeur de l'attribut eduPersonOrgDN ou false
  *               si il y a un problème durant la génération
  */
  function generate_eduPersonOrgDN($ldapObject) {
    if ( get_class($ldapObject -> attrs[ 'supannEtablissement' ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => 'supannEtablissement', 'attr' => 'eduPersonOrgDN'));
      return;
    }

    $eta = $ldapObject -> attrs[ 'supannEtablissement' ] -> getUpdateData();

    $retval=array();
    if ($eta[0] == '{UAI}'.$GLOBALS['LS_SUPANN_ETABLISSEMENT_UAI']) {
      $retval[] = $GLOBALS['LS_SUPANN_ETABLISSEMENT_DN'];
    }

    return $retval;
  }

/***********************************************************************
 * Fonction de parsing des valeurs spécifiques SUPANN
 **********************************************************************/

 /**
  * Parse une valeur a etiquette SUPANN
  *
  * Exemple de valeur :
  *
  *    {SUPANN}S410
  *
  * @param[in] $val La valeur
  *
  * @retval array Un tableau cle->valeur contenant label et value ou False
  **/
  function supannParseLabeledValue($value) {
    if (preg_match('/^\{([^\}]*)\}(.*)$/',$value,$m)) {
      return array(
        'label'=>$m[1],
        'value'=>$m[2]
      );
    }
    return;
  }

 /**
  * Parse une valeur composite SUPANN
  *
  * Exemple de valeur :
  *
  *    [key1=value][key2=value][key3=value]
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $val La valeur composite
  *
  * @retval array Un tableau contenant key->value ou false en cas d'erreur
  **/
  function supannParseCompositeValue($val) {
    if (preg_match_all('/\[([^=]*)=([^\]]*)\]/',$val,$matches)) {
      $parseValue=array();
      for($i=0;$i<count($matches[0]);$i++) {
        $parseValue[$matches[1][$i]]=$matches[2][$i];
      }
      return $parseValue;
    }
    return;
  }

/***********************************************************************
 * Fonctions relatives aux entités
 **********************************************************************/

 /**
  * Retourne le nom court d'une entite en fonction de son identifiant
  *
  * @param[in] $id L'identifiant de l'entite (supannCodeEntite)
  *
  * @retval string Le nom de l'entite
  **/
  function supanGetEntiteNameById($id) {
    if (LSsession::loadLSobject($GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'])) {
      $e = new $GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE']();
      $list=$e -> listObjectsName("(supannCodeEntite=$id)",NULL,array('onlyAccessible' => false),$GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_FORMAT_SHORTNAME']);
      if (count($list)==1) {
        return array_pop($list);
      }
    }
    return getFData(__("Entity %{id} (unrecognized)"),$id);
  }

 /**
  * Valide l'ID d'une entite
  *
  * @param[in] $id L'identifiant de l'entite (supannCodeEntite)
  *
  * @retval boolean True si une entité avec cet ID existe, False sinon
  **/
  function supannValidateEntityId($id) {
    if (LSsession::loadLSobject($GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'])) {
      $e = new $GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE']();
      $list=$e -> listObjectsName("(supannCodeEntite=$id)",NULL,array('onlyAccessible' => False));
      if (count($list)==1) {
        return true;
      }
    }
    return false;
  }

 /**
  * Cherche des entités répond au pattern de recherche passé en paramètres
  * et retourne un tableau mettant en relation leur identifiant et leur nom
  * d'affichage.
  *
  * @param[in] $pattern string Le pattern de recherche
  *
  * @retval array Tableau du résultat de la recherche mettant en relation
  *               l'identifiant des entités trouvés avec leur nom d'affichage.
  **/
  function supannSearchEntityByPattern($pattern) {
		$retval=array();
		if (LSsession::loadLSclass('LSsearch')) {
			$search=new LSsearch(
				$GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'],
				'SUPANN:supannSearchEntityByPattern',
				array(
					'pattern' => $pattern,
					'attributes' => array('supannCodeEntite'),
					'sizelimit' => 10,
					'onlyAccessible' => false
				)
			);
			$search -> run();

			foreach($search -> getSearchEntries() as $e) {
				$code=$e->get('supannCodeEntite');
				if (is_array($code)) $code=$code[0];
				$retval[$code]=$e->displayName;
			}
		}
		return $retval;
  }


/***********************************************************************
 * Fonctions relatives aux nomenclatures
 **********************************************************************/

 /**
  * Vérifie si une valeur et son étiquette sont valide pour une table donnée
  *
  * @param[in] $table La table de nomenclature
  * @param[in] $label L'étiquette de la valeur (optionnel)
  * @param[in] $value La valeur
  *
  * @retval booleab True si valide, False sinon
  **/
  function supannValidateNomenclatureValue($table, $label, $value) {
    if ($label) {
	    $label = strtoupper($label);
      if (
          isset($GLOBALS['supannNomenclatures'][$label]) &&
          isset($GLOBALS['supannNomenclatures'][$label][$table]) &&
          isset($GLOBALS['supannNomenclatures'][$label][$table][$value])
      ) {
	      return array(
          'table' => $table,
          'label' => $label,
          'value' => $value,
          'translated' => $GLOBALS['supannNomenclatures'][$label][$table][$value],
        );
	    }
    }
    else {
      foreach($GLOBALS['supannNomenclatures'] as $label => $tables) {
        if (!array_key_exists($table, $tables) || !array_key_exists($value, $tables[$table]))
          continue;
        return array(
          'table' => $table,
          'label' => $label,
          'value' => $value,
          'translated' => $tables[$table][$value],
        );
      }
    }
  	return false;
  }

 /**
  * Retourne le label d'une valeur en fonction de la table de nomenclature
  * et de l'étiquette de la valeur.
  *
  * @param[in] $table La table de nomenclature
  * @param[in] $label L'étiquette de la valeur (optionnel)
  * @param[in] $value La valeur
  *
  * @retval array Le label de la valeur. En cas de valeur nor-reconnue, retourne
  *               la valeur en spécifiant qu'elle n'est pas reconnue.
  **/
  function supannGetNomenclatureLabel($table, $label, $value) {
	  $translated = supannValidateNomenclatureValue($table, $label, $value);
    if ($translated)
	    return $translated['translated'];
	  return getFData(__("%{value} (unrecognized value)"), $value);
  }

 /**
  * Retourne les valeurs possibles d'une table de nomenclature pour chaque fournisseur
  *
  * @param[in] $table La table de nomenclature
  *
  * @retval array Tableau contenant pour chaque fournisseur, les valeurs possibles de
  *               la table de nomenclature
  **/
  function supannGetNomenclatureTable($table) {
	  $retval=array();
	  foreach(array_keys($GLOBALS['supannNomenclatures']) as $provider) {
		  if (isset($GLOBALS['supannNomenclatures'][$provider][$table])) {
			  $retval[$provider] = $GLOBALS['supannNomenclatures'][$provider][$table];
		  }
	  }
	  return $retval;
  }

 /**
  * Retourne les valeurs possibles d'une table de nomenclature
  *
  * @param[in] $table La table de nomenclature
  * @param[in] $add_provider_label Booléen définissant si le fournisseur de la valeur
  *                                doit être ajouté en tant qu'étiquette de la valeur
  *                                (optinel, par défaut: vrai)
  *
  * @retval array Tableau contenant les valeurs possibles de la table
  *               de nomenclature
  **/
  function supannGetNomenclaturePossibleValues($table, $add_provider_label=True) {
	  $retval = array();
	  foreach(array_keys($GLOBALS['supannNomenclatures']) as $provider) {
		  if (isset($GLOBALS['supannNomenclatures'][$provider][$table])) {
        foreach($GLOBALS['supannNomenclatures'][$provider][$table] as $value => $label) {
          if ($add_provider_label)
            $value = "{$provider}$value";
          $retval[$value] = __($label);
        }
		  }
	  }
	  return $retval;
  }

/**
 * Retourne les valeurs possibles de l'attribut supannCivilite.
 *
 * Cette fonction est prévue pour pouvoir être utilisé comme paramètre
 * get_possible_values de la configuration HTML de l'attribut
 * supannCivilite avec un type d'attribut HTML select_list (ou select_box).
 *
 * @param[in] $options La configuration HTML de l'attribut
 * @param[in] $name Le nom de l'attribut
 * @param[in] &$ldapObject Une référence à l'object LSldapObject
 *
 * @retval array Tableau contenant les valeurs possibles de l'attribut
 *               (avec les labels traduits).
 **/
function supannGetCivilitePossibleValues($options, $name, $ldapObject) {
  return supannGetNomenclaturePossibleValues('civilite', false);
}

/**
 * Retourne les valeurs possibles des affiliations.
 *
 * Cette fonction est prévue pour pouvoir être utilisé comme paramètre
 * get_possible_values de la configuration HTML de l'attribut
 * eduPersonAffiliation (par exemple) avec un type d'attribut HTML select_list.
 *
 * @param[in] $options La configuration HTML de l'attribut
 * @param[in] $name Le nom de l'attribut
 * @param[in] &$ldapObject Une référence à l'object LSldapObject
 *
 * @retval array Tableau contenant les valeurs possibles de l'attribut
 *               (avec les labels traduits).
 **/
function supannGetAffiliationPossibleValues($options, $name, $ldapObject) {
  return supannGetNomenclaturePossibleValues('affiliation', false);
}

/**
 * Vérifie les valeurs de l'attribut eduPersonAffiliation
 *
 * Cette fonction est prévue pour pouvoir être utilisé comme paramètre
 * function de la configuration de validation de l'intégrité des valeurs
 * de l'attribut eduPersonAffiliation (paramètre validation).
 *
 * Elle s'assure que des valeurs affiliate et member n'ont pas été toutes
 * les deux selectionnées, car elles sont incompatibles.
 *
 * @author Benjamin Dauvergne <bdauvergne@entrouvert.com>
 *
 * @param[in] &$ldapObject Une référence à l'object LSldapObject
 * @author Benjamin Dauvergne <bdauvergne@entrouvert.com>
 *
 * @retval boolean True si les valeurs sont valides, False sinon
 **/
global $_supannCheckEduPersonAffiliation_checked;
$_supannCheckEduPersonAffiliation_checked = false;
function supannCheckEduPersonAffiliation(&$ldapObject) {
       global $_supannCheckEduPersonAffiliation_checked;
       $values = $ldapObject->getValue('eduPersonAffiliation');

       if (!$_supannCheckEduPersonAffiliation_checked && in_array('affiliate', $values) && in_array('member', $values)) {
            $_supannCheckEduPersonAffiliation_checked = true;
            return false;
       }
       return true;
}

/**
 * Vérifie la valeur de l'attribut eduPersonPrimaryAffiliation
 *
 * Cette fonction est prévue pour pouvoir être utilisé comme paramètre
 * function de la configuration de validation de l'intégrité des valeurs
 * de l'attribut eduPersonPrimaryAffiliation (paramètre validation).
 *
 * Elle s'assure que la valeur de l'attribut eduPersonPrimaryAffiliation
 * fait bien partie des valeurs de l'attribut eduPersonAffiliation.
 *
 * @author Benjamin Dauvergne <bdauvergne@entrouvert.com>
 *
 * @param[in] &$ldapObject Une référence à l'object LSldapObject
 *
 * @retval boolean True si la valeur est valide, False sinon
 **/
function supannCheckEduPersonPrimaryAffiliation(&$ldapObject) {
       $primary = $ldapObject->getValue('eduPersonPrimaryAffiliation');
       $affiliations = $ldapObject->getValue('eduPersonAffiliation');
       if (!array_intersect($primary, $affiliations))
            return false;
       return true;
}

/**
 * Retourne les valeurs possibles de l'attribut supannOIDCGenre.
 *
 * Cette fonction est prévue pour pouvoir être utilisé comme paramètre
 * get_possible_values de la configuration HTML de l'attribut
 * supannOIDCGenre avec un type d'attribut HTML select_list ou select_box.
 *
 * @param[in] $options La configuration HTML de l'attribut
 * @param[in] $name Le nom de l'attribut
 * @param[in] &$ldapObject Une référence à l'object LSldapObject
 *
 * @retval array Tableau contenant les valeurs possibles de l'attribut
 *               (avec les labels traduits).
 **/
function supannGetOIDCGenrePossibleValues($options, $name, $ldapObject) {
  return supannGetNomenclaturePossibleValues('oidc_genre', false);
}

/**
 * Géneration de la valeur de l'attribut eduPersonPrincipalName
 * à partir du LSformat configuré dans $GLOBALS['LS_SUPANN_EPPN_FORMAT']
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject L'objet ldap
 *
 * @retval array La valeur de l'attribut eduPersonOrgDN ou false
 *               si il y a un problème durant la génération
 */
function generate_eduPersonPrincipalName($ldapObject) {
 return $ldapObject -> getFData($GLOBALS['LS_SUPANN_EPPN_FORMAT']);
}

/**
 * Géneration de la valeur de l'attribut eduPersonUniqueId
 * à partir du LSformat configuré dans $GLOBALS['LS_SUPANN_EPUI_FORMAT']
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $ldapObject L'objet ldap
 *
 * @retval array La valeur de l'attribut eduPersonOrgDN ou false
 *               si il y a un problème durant la génération
 */
function generate_eduPersonUniqueId($ldapObject) {
  $ldapObject -> registerOtherValue('uniqueId', uniqid());
  return $ldapObject -> getFData($GLOBALS['LS_SUPANN_EPUI_FORMAT']);
}

/**
 * Vérifie si une entité SUPANN peux être suprimée.
 *
 * Cette fonction est prévue pour pouvoir être utilisé comme paramètre
 * before_delete de la configuration du type d'objet correspondant aux
 * entités SUPANN. Elle vérifie que l'entité n'a pas d'entité fille
 * avant suppression. Si au moins une entité fille est trouvée, la
 * suppression est bloquée et une message d'erreur est affiché.
 *
 * Note: Cette fonction peut également être utilisé pour le type d'objet
 * correspond aux établissements.
 *
 * @param[in] &$ldapObject Une référence à l'object LSldapObject
 *
 * @retval boolean True si la valeur est valide, False sinon
 **/
function supannCheckEntityCouldBeDeleted($ldapObject) {
  $children = $ldapObject -> listObjectsInRelation(
    $ldapObject,
    'supannCodeEntiteParent',
    $GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'],
    'supannCodeEntite'
  );
  if ($children) {
    LSerror :: addErrorCode('SUPANN_03');
    return false;
  }
  return true;
}

if (php_sapi_name() != 'cli')
  return true;

function cli_generate_supann_codeEtablissement_uai_nomenclature($command_args) {
  $data = file_get_contents('https://data.enseignementsup-recherche.gouv.fr/explore/dataset/fr-esr-principaux-etablissements-enseignement-superieur/download?format=json');
  $items = json_decode($data, true);
  if (!is_array($items))
    LSlog :: fatal('Fail to retreive UAI dataset from data.enseignementsup-recherche.gouv.fr');
  $codes = array();
  foreach($items as $item) {
    if (!isset($item['fields']) || !isset($item['fields']['uai']) || !$item['fields']['uai'])
      continue;
    $codes[$item['fields']['uai']] = $item['fields']['uo_lib'];
  }
  var_export($codes);
}

LScli :: add_command(
  'generate_supann_codeEtablissement_uai_nomenclature',
  'cli_generate_supann_codeEtablissement_uai_nomenclature',
  'Generate Supann codeEtablissement UAI nomenclature',
  false, // usage args
  false, // long desc
  false // need LDAP connection
);
