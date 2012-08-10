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
  _("SUPANN Support : The constant %{const} is not defined.")
);
LSerror :: defineError('SUPANN_SUPPORT_02',
  _("SUPANN Support : The LSobject type %{type} does not exist. Can't work with entities..")
);
LSerror :: defineError('SUPANN_SUPPORT_03',
  _("SUPANN Support : The global array %{array} is not defined.")
);

// Autres erreurs
LSerror :: defineError('SUPANN_01',
  _("SUPANN Support : The attribute %{dependency} is missing. Unable to forge the attribute %{attr}.")
);
LSerror :: defineError('SUPANN_02',
  _("SUPANN Support : Can't get the basedn of entities. Unable to forge the attribute %{attr}.")
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
        
    $MUST_DEFINE_CONST= array(
      'LS_SUPANN_LASTNAME_ATTR',
      'LS_SUPANN_FIRSTNAME_ATTR',
      'LS_SUPANN_LSOBJECT_ENTITE_TYPE',
      'LS_SUPANN_LSOBJECT_ENTITE_FORMAT_SHORTNAME',
      'LS_SUPANN_ETABLISSEMENT_UAI',
      'LS_SUPANN_ETABLISSEMENT_DN'
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( (!defined($const)) || (constant($const) == "")) {
        LSerror :: addErrorCode('SUPANN_SUPPORT_01',$const);
        $retval=false;
      }
    }

    $MUST_DEFINE_ARRAY= array(
      'supannRoleGenerique',
      'supannTypeEntite',
      'supannTranslateRoleEntiteValueDirectory',
      'supannTranslateFunctionDirectory',
    );
    foreach($MUST_DEFINE_ARRAY as $array) {
      if ( !isset($GLOBALS[$array]) || !is_array($GLOBALS[$array])) {
        LSerror :: addErrorCode('SUPANN_SUPPORT_01',$array);
        $retval=false;
      }
    }

    if ( defined('LS_SUPANN_LSOBJECT_ENTITE_TYPE') ) {
      if ( ! LSsession :: loadLSobject( LS_SUPANN_LSOBJECT_ENTITE_TYPE ) ) {
        LSerror :: addErrorCode('SUPANN_SUPPORT_02', LS_SUPANN_LSOBJECT_ENTITE_TYPE);
      }
    }
    
    return $retval;
  }

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
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_LASTNAME_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_LASTNAME_ATTR, 'attr' => 'cn'));
      return;
    }
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_FIRSTNAME_ATTR, 'attr' => 'cn'));
      return;
    }

    $noms = $ldapObject -> attrs[ LS_SUPANN_LASTNAME_ATTR ] -> getValue();
    $prenoms = $ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ] -> getValue();

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
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_LASTNAME_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_LASTNAME_ATTR, 'attr' => 'cn'));
      return;
    }
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_FIRSTNAME_ATTR, 'attr' => 'cn'));
      return;
    }

    $noms = $ldapObject -> attrs[ LS_SUPANN_LASTNAME_ATTR ] -> getValue();
    $prenoms = $ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ] -> getValue();

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

    $basedn=LSconfig :: get('LSobjects.'.LS_SUPANN_LSOBJECT_ENTITE_TYPE.'.container_dn').','.LSsession::getTopDn();
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

    $basedn=LSconfig :: get('LSobjects.'.LS_SUPANN_LSOBJECT_ENTITE_TYPE.'.container_dn').','.LSsession::getTopDn();
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
  * La valeur sera LS_SUPANN_ETABLISSEMENT_DN si l'attribut supannEtablissement
  * vaut {UAI}LS_SUPANN_ETABLISSEMENT_UAI.
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
    if ($eta[0] == '{UAI}'.LS_SUPANN_ETABLISSEMENT_UAI) {
    	$retval[] = LS_SUPANN_ETABLISSEMENT_DN;
    }

    return $retval;
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

 /**
  * Retourne une eventuelle fonction de traduction d'une valeur
  * en fonction de son label et de sa cle.
  *
  * Utilise la table $GLOBALS['supannTranslateFunctionDirectory']
  *
  * @param[in] $label Le label de la valeur
  * @param[in] $key La cle de la valeur
  *
  * @retval string|false Le nom de la fonction de traduction ou false
  **/
  function supannTranslateRoleEntiteFunction($label,$key) {
    if (isset($GLOBALS['supannTranslateFunctionDirectory'][$label][$key])) {
      return $GLOBALS['supannTranslateFunctionDirectory'][$label][$key];
    }
    return;
  }


 /**
  * Retourne le nom court d'une entite en fonction de son identifiant
  *
  * Fonction utilise comme fonction de traduction dans la fonction 
  * supannTranslateRoleEntiteValue()
  *
  * @param[in] $label Le label de la valeur
  * @param[in] $key La cle de la valeur
  * @param[in] $value La valeur : l'identifiant de l'entite (supannCodeEntite)
  *
  * @retval string Le nom de l'entite
  **/
  function supanGetEntiteNameById($label,$key,$value) {
    if (LSsession::loadLSobject(LS_SUPANN_LSOBJECT_ENTITE_TYPE)) {
      $type=LS_SUPANN_LSOBJECT_ENTITE_TYPE;
      $e = new $type();
      $list=$e -> listObjectsName("(supannCodeEntite=$value)",NULL,array(),LS_SUPANN_LSOBJECT_ENTITE_FORMAT_SHORTNAME);
      if (count($list)==1) {
        return array(
          'translated' => array_pop($list),
          'label' => $label
        );
      }
    }
    return array(
      'translated' => getFData(__("%{value} (unrecognized value)"),$value),
      'label' => $label
    );
  }

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
  * Traduit une valeur en fonction de sa cle extrait d'un attribut
  * supannRoleEntite.
  *
  * @param[in] $key La cle
  * @param[in] $value La valeur
  *
  * @retval array Un tableau cle->valeur contenant label et translated ou False
  **/
  function supannTranslateRoleEntiteValue($key,$value) {
    $label='no';
    $pl=supannParseLabeledValue($value);
    if ($pl) {
      $label=$pl['label'];
      $value=$pl['value'];
    }

    // Translate by method
    if (supannTranslateRoleEntiteFunction($label,$key)) {
      $func = supannTranslateRoleEntiteFunction($label,$key);
      if (function_exists($func)) {
        try {
          return $func($label,$key,$value);
        }
        catch (Exception $e) {
          return;
        }
      }
      else {
        return;
      }
    }
    // Translate by directory
    elseif (isset($GLOBALS['supannTranslateRoleEntiteValueDirectory'][$label][$key][$value])) {
      return array(
        'translated' => $GLOBALS['supannTranslateRoleEntiteValueDirectory'][$label][$key][$value],
        'label' => $label
     );
    }
    else {
      return array(
        'label' => $label,
        'translated' => $value
      );
    }
  }

?>
