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
  ___("SUPANN Support : The attribute %{dependency} is missing. Unable to forge the attribute %{attr}.")
);
LSerror :: defineError('SUPANN_02',
  ___("SUPANN Support : Can't get the basedn of entities. Unable to forge the attribute %{attr}.")
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
      'LS_SUPANN_ETABLISSEMENT_DN'
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
  * @param[in] $label L'étiquette de la valeur
  * @param[in] $value La valeur
  *
  * @retval booleab True si valide, False sinon
  **/
  function supannValidateNomenclatureValue($table,$label,$value) {
	$label=strtoupper($label);
    if (isset($GLOBALS['supannNomenclatures'][$label]) &&
        isset($GLOBALS['supannNomenclatures'][$label][$table]) &&
        isset($GLOBALS['supannNomenclatures'][$label][$table][$value])) {
	  return true;
	}
	return false;
  }

 /**
  * Retourne le label d'une valeur en fonction de la table de nomenclature
  * et de l'étiquette de la valeur.
  *
  * @param[in] $table La table de nomenclature
  * @param[in] $label L'étiquette de la valeur
  * @param[in] $value La valeur
  *
  * @retval array Le label de la valeur. En cas de valeur nor-reconnue, retourne
  *               la valeur en spécifiant qu'elle n'est pas reconnue.
  **/
  function supannGetNomenclatureLabel($table,$label,$value) {
	if (supannValidateNomenclatureValue($table,$label,$value)) {
      $label=strtoupper($label);
	  return $GLOBALS['supannNomenclatures'][$label][$table][$value];
	}
	return getFData(__("%{value} (unrecognized value)"),$value);
  }

 /**
  * Retourne les valeurs possibles d'une table de nomenclature
  *
  * @param[in] $table La table de nomenclature
  *
  * @retval array Tableau contenant les valeurs possibles de la table
  *               de nomenclature
  **/
  function supannGetNomenclatureTable($table) {
	  $retval=array();
	  foreach($GLOBALS['supannNomenclatures'] as $label => $tables) {
		  if (isset($GLOBALS['supannNomenclatures'][$label][$table])) {
			  $retval[$label]=$GLOBALS['supannNomenclatures'][$label][$table];
		  }
	  }
	  return $retval;
  }
