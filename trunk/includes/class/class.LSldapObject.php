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
 * Base d'un objet ldap
 *
 * Cette classe définis la base de tout objet ldap géré par LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSldapObject { 
	
	var $config;
	var $type_name;
	var $attrs;
  var $forms;
  var $dn=false;
  var $other_values=array();
  
  /**
   * Constructeur
   *
   * Cette methode construit l'objet et définis la configuration.
   * Elle lance la construction du tableau d'attributs représentés par un objet LSattribute.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $type_name [<b>required</b>] string Le nom du type de l'objet
   * @param[in] $config array La configuration de l'objet
   *
   * @retval boolean true si l'objet a été construit, false sinon.
   */	
	function LSldapObject($type_name,$config='auto') {
		$this -> type_name = $type_name;
    $this -> config = $config;
    if($config=='auto') {
      if(isset($GLOBALS['LSobjects'][$type_name]))
        $this -> config = $GLOBALS['LSobjects'][$type_name];
      else {
        $GLOBALS['LSerror'] -> addErrorCode(21);
        return;
      }
    }
		foreach($this -> config['attrs'] as $attr_name => $attr_config) {
			if(!$this -> attrs[$attr_name]=new LSattribute($attr_name,$attr_config))
        return;
		}
    return true;
	}
	
  /**
   * Charge les données de l'objet
   *
   * Cette methode définis le DN de l'objet et charge les valeurs de attributs de l'objet
   * à partir de l'annuaire.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string Le DN de l'objet.
   *
   * @retval boolean true si la chargement a réussi, false sinon.
   */	
  function loadData($dn) {
    $this -> dn = $dn;
    $data = $GLOBALS['LSldap'] -> getAttrs($dn);
    foreach($this -> attrs as $attr_name => $attr) {
      if(!$this -> attrs[$attr_name] -> loadData($data[$attr_name]))
        return;
    }
    return true;
  }
  
  /**
   * Retourne le format d'affichage de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Format d'affichage de l'objet.
   */	
	function getDisplayAttributes() {
		return $this -> config['select_display_attrs'];
	}
  
  /**
   * Retourne la valeur descriptive d'affichage de l'objet
   * 
   * Cette fonction retourne la valeur descriptive d'affichage de l'objet en fonction
   * du format défini dans la configuration de l'objet ou spécifié en paramètre.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $spe [<i>optionnel</i>] string Format d'affichage de l'objet
   *
   * @retval string Valeur descriptive d'affichage de l'objet
   */	
  function getDisplayValue($spe) {
    if ($spe=='') {
      $spe = $this -> getDisplayAttributes();
    }
    return $this -> getFData($spe,&$this -> attrs,'getDisplayValue');
  }
  
  /**
   * Chaine formatée
   * 
   * Cette fonction retourne la valeur d'une chaine formatée en prennant les valeurs
   * de l'objet.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $format string Format de la chaine
   *
   * @retval string Valeur d'une chaine formatée
   */	
  function getFData($format) {
    $format=getFData($format,$this,'getValue');
    return $format;
  }
  
  /**
   * DEBUG : Affiche le nom et la valeur de chaque attribut
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */	
  function debug_printAttrsValues() {
    foreach($this -> attrs as $attr_name => $attr) {
      print $attr_name.' : ';
      $attr -> debug_printValue();
      print "\n";
    }
  }

  /**
   * Construit un formulaire de l'objet
   * 
   * Cette méthode construit un formulaire LSform à partir de la configuration de l'objet
   * et de chaque attribut.
   *
   * @param[in] $idForm [<b>required</b>] Identifiant du formulaire a créer
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval LSform Le formulaire crée
   */	
  function getForm($idForm,$config=array()) {
    $LSform = new LSform($idForm);
    $this -> forms[$idForm] = array($LSform,$config);
    foreach($this -> attrs as $attr_name => $attr) {
      if(!$this -> attrs[$attr_name] -> addToForm($LSform -> quickform,$idForm)) {
        $LSform -> can_validate = false;
      }
    }
    return $LSform;
  }
  
  /**
   * Met à jour les données de l'objet et de l'entré de l'annuaire
   * 
   * Met à jour les données de l'objet à partir d'un retour d'un formulaire.
   *
   * @param[in] $idForm Identifiant du formulaire d'origine
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la mise à jour a réussi, false sinon
   *
   * @see validateAttrsData()
   * @see submitChange()
   */	
  function updateData($idForm=NULL) {
    if($idForm!=NULL) {
      if(isset($this -> forms[$idForm]))
        $LSform = $this -> forms[$idForm][0];
      else {
        $GLOBALS['LSerror'] -> addErrorCode(22,$this -> type_name);
        return;
      }
    }
    else {
      if(count($this -> forms) > 0) {
        reset($this -> forms);
        $idForm = key($this -> forms);
        $LSform = current($this -> forms);
        $config = $LSform[1];
        $LSform = $LSform[0];
      }
      else {
        $GLOBALS['LSerror'] -> addErrorCode(23,$this -> type_name);
        $GLOBALS['LSerror'] -> stop();
      }
    }
    $new_data = $LSform -> quickform -> exportValues();
    foreach($new_data as $attr_name => $attr_val) {
      if(isset($this -> attrs[$attr_name])) {
        $this -> attrs[$attr_name] -> setUpdateData($attr_val);
      }
    }
    if($this -> validateAttrsData($idForm)) {
      if(isset($this -> config['before_save'])) {
        if(function_exists($this -> config['before_save'])) {
          if(!$this -> config['before_save']($this)) {
            $GLOBALS['LSerror'] -> addErrorCode(28,$this -> config['before_save']);
            $GLOBALS['LSerror'] -> stop();
          }
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(27,$this -> config['before_save']);
          $GLOBALS['LSerror'] -> stop();
        }
      }
      $this -> submitChange($idForm);
      if(isset($this -> config['after_save'])) {
        if(function_exists($this -> config['after_save'])) {
          if(!$this -> config['after_save']($this)) {
            $GLOBALS['LSerror'] -> addErrorCode(30,$this -> config['after_save']);
          }
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(29,$this -> config['after_save']);
        }
      }
    }
  }
  
  /**
   * Valide les données retournées par un formulaire
   *
   * @param[in] $idForm Identifiant du formulaire d'origine
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si les données sont valides, false sinon
   */	
  function validateAttrsData($idForm) {
    $LSform=$this -> forms[$idForm][0];
    foreach($this -> attrs as $attr) {
      if(($attr -> isUpdate())&&(!$attr -> isValidate())) {
        //~ echo 'NAME : '.$attr -> name.' Val : '.$attr -> getUpdateData();
        $vconfig=$attr -> getValidateConfig();
        if(is_array($vconfig)) {
          foreach($vconfig as $test) {
            $data=$attr -> getUpdateData();
            if(!is_array($data))
              $data=array($data);
            foreach($data as $val) {
              // validation par check LDAP
              if((isset($test['filter'])||isset($test['basedn']))&&(isset($test['result']))) {
                $sparams=(isset($test['scope']))?array('scope' => $test['scope']):array();
                $this -> other_values['val']=$val;
                $sfilter_user=(isset($test['basedn']))?getFData($test['filter'],$this,'getValue'):NULL;
                //~ echo $sfilter_user;
                if(isset($test['object_type'])) {
                  $test_obj = new $test['object_type']('auto');
                  $sfilter=$test_obj->getObjectFilter();
                  $sfilter='(&'.$sfilter;
                  if($sfilter_user[0]=='(')
                    $sfilter=$sfilter.$sfilter_user.')';
                  else
                    $sfilter=$sfilter.'('.$sfilter_user.'))';
                }
                else {
                  $sfilter=$sfilter_user;
                }
                $sbasedn=(isset($test['basedn']))?getFData($test['basedn'],$this,'getValue'):NULL;
                $ret=$GLOBALS['LSldap'] -> getNumberResult ($sfilter,$sbasedn,$sparams);
                //~ echo 'Basedn : "'.$sbasedn.'" Filter : "'.$sfilter.'" NAME : '.$attr -> name.' Nb : '.$ret."<br />\n";
                if($test['result']==0) {
                  if($ret!=0) {
                    $LSform -> setElementError($attr,$test['msg']);
                    return;
                  }
                }
                else {
                  if($ret<=0) {
                    $LSform -> setElementError($attr,$msg_error);
                    return;
                  }
                }
              }
              // Validation par fonction externe
              else if(isset($test['function'])) {
                if (function_exists($test['function'])) {
                  if(!$test['function']($this)) {
                    $LSform -> setElementError($attr,$msg_error);
                    return;
                  }
                }
                else {
                  $GLOBALS['LSerror'] -> addErrorCode(24,array('attr' => $attr->name,'obj' => $this->type_name,'func' => $test['function']));
                  return;
                }
              }
              else {
                $GLOBALS['LSerror'] -> addErrorCode(25,array('attr' => $attr->name,'obj' => $this->type_name));
                return;
              }
            }
          }
        }
        $attr -> validate();
      }
    }
    unset($this -> other_values['val']);
    return true;
  }
  
  /**
   * Met à jour les données modifiés dans l'annuaire
   *
   * @param[in] $idForm Identifiant du formulaire d'origine
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la mise à jour a réussi, false sinon
   */	
  function submitChange($idForm) {
    $submit_data=array();
    foreach($this -> attrs as $attr) {
      if(($attr -> isUpdate())&&($attr -> isValidate())) {
        $submit_data[$attr -> name] = $attr -> getUpdateData();
      }
    }
    print_r($submit_data);
  }
  
  /**
   * Retourne les informations issus d'un DN
   *
   * @param[in] $dn Un DN.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau : 
   *                  - [0] : le premier paramètre
   *                  - [1] : les paramètres suivants
   */	
  function getDnInfos($dn) {
    $infos=ldap_explode_dn($dn,0);
    if(!$infos)
      return;
    $first=true;
    for($i=1;$i<$infos['count'];$i++)
      if($first) {
        $basedn.=$infos[$i];
        $first=false;
      }
      else
        $basedn.=','.$infos[$i];
    return array($infos[0],$basedn);
  }
  
  /**
   * Fait la somme de DN
   *
   * Retourne un DN qui correspond au point de séparation des DN si les DN 
   * ne sont pas dans la meme dans la meme branche ou le dn le plus long sinon.
   *
   * @param[in] $dn Un premier DN.
   * @param[in] $dn Un deuxième DN.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Un DN (ou false si les DN ne sont pas valide)
   */	
  function sumDn($dn1,$dn2) {
    $infos1=ldap_explode_dn($dn1,0);
    if(!$infos1)
      return;
    $infos2=ldap_explode_dn($dn2,0);
    if(!$infos2)
      return;
    if($infos2['count']>$infos1['count']) {
      $tmp=$infos1;
      $infos1=$infos2;
      $infos2=$tmp;
    }
    $infos1=array_reverse($infos1);
    $infos2=array_reverse($infos2);
    
    $first=true;
    $basedn='';
    for($i=0;$i<$infos1['count'];$i++) {
      if(($infos1[$i]==$infos2[$i])||(!isset($infos2[$i]))) {
        if($first) {
          $basedn=$infos1[$i];
          $first=false;
        }
        else
          $basedn=$infos1[$i].','.$basedn;
      }
      else {
        return $basedn;
      }
    }
    return $basedn;
  }
  
  /**
   * Vérifie la compatibilite des DN
   *
   * Vérifie que les DNs sont dans la même branche de l'annuaire.
   *
   * @param[in] $dn Un premier DN.
   * @param[in] $dn Un deuxième DN.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si les DN sont compatibles, false sinon.
   */	
  function isCompatibleDNs($dn1,$dn2) {
    $infos1=ldap_explode_dn($dn1,0);
    if(!$infos1)
      return;
    $infos2=ldap_explode_dn($dn2,0);
    if(!$infos2)
      return;
    if($infos2['count']>$infos1['count']) {
      $tmp=$infos1;
      $infos1=$infos2;
      $infos2=$tmp;
    }
    $infos1=array_reverse($infos1);
    $infos2=array_reverse($infos2);
    
    for($i=0;$i<$infos1['count'];$i++) {
      if(($infos1[$i]==$infos2[$i])||(!isset($infos2[$i])))
        continue;
      else
        return false;
    }
    return true;
  }
  
  /**
   * Retourne le filtre correpondants aux objetcClass de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string le filtre ldap correspondant au type de l'objet
   */	
  function getObjectFilter() {
    if(!isset($this -> config['objectclass'])) return;
    foreach ($this -> config['objectclass'] as $class)
      $filter.='(objectClass='.$class.')';
    return $filter;
  }
  
  /**
   * Retourne une liste d'objet du même type.
   *
   * Effectue une recherche en fonction des paramètres passé et retourne un
   * tableau d'objet correspond au resultat de la recherche.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $filter array (ou string) Filtre de recherche Ldap / Tableau de filtres de recherche
   * @param[in] $basedn string DN de base pour la recherche
   * @param[in] $params array Paramètres de recherche au format Net_LDAP::search()
   *
   * @retval array Tableau d'objet correspondant au resultat de la recherche
   */	
  function listObjects($filter='',$basedn=NULL,$params=array()) {
    $retInfos=array();
    $attrs=false;
    $check_final_dn=false;

    if(!is_array($filter))
      $filter=array(array('filter' => $filter));
    
    $nbFilter=count($filter);

    for($i=0;$i<$nbFilter;$i++) {
      $new_attrs=array();
      // Défintion des paramètres de base pour la recherche
      $sbasedn=$basedn;
      $sparams=$params;
      $ret=array();
      if (isset($filter[$i]['scope']))
        $sparams["scope"]=$filter[$i]['scope'];
      
      // Definition des critères de recherche correspondant au type d'objet à lister
      if(($nbFilter==1)||(!isset($filter[$i]['attr']))) {
        // Filtre sur l'objet souhaité
        $sfilter='(&';
        $sfilter.=$this -> getObjectFilter();
        $sfilter_end=')';
        $check_final_dn=true;
      }
      // Initialisation des critères d'une recherche intermédiaire
      else {
        if(isset($filter[$i]['object_type'])) {
          $obj_tmp=new $filter[$i]['object_type']();
          $obj_filter=$obj_tmp->getObjectFilter();
          $sfilter='(&'.$obj_filter;
          $sfilter_end=')';
        }
        else {
          $sfilter='';
          $sfilter_end='';
        }
        if(isset($filter[$i]['scope'])) {
          $sparams['scope']=$filter[$i]['scope'];
        }
        if(isset($filter[$i]['basedn'])) {
          $sbasedn=$filter[$i]['basedn'];
        }
      }
      // Dans le cas d'une recherche intermédiaire ou finale
      if($attrs!=false) {
        // Initialisation des variables
        $ret_gen=array();
        $new_attrs=array();
        
        // Pour tout les attributs retournés
        for($ii=0;$ii<count($attrs);$ii++) {
          $sfilter_for='';
          // Définition du filtre de recherche à partir des paramètres utilisateurs et
          // des paramètres de recherche de l'objet à listé (dans le cas d'une recherche finale
          if((isset($filter[$i]['filter']))&&(!empty($filter[$i]['filter']))) {
            $sfilter_user=getFData($filter[$i]['filter'],$attrs[$ii]);
            if($sfilter_user[0]=='(')
              $sfilter_for=$sfilter.$sfilter_user;
            else
              $sfilter_for=$sfilter.'('.$sfilter_user.')';
          }
          else {
            $sfilter_for=$sfilter;
          }
          
          if(isset($filter[$i]['basedn'])) {
            $sbasedn=getFData($filter[$i]['basedn'],$attrs[$ii]);
            if ((!$this -> isCompatibleDNs($sbasedn,$basedn))&&($check_final_dn)) continue;
          }
        
          // Vérification de la compatibilité du basedn de la recherche et du basedn générale
          //~ if ($this -> isCompatibleDNs($filter[$i]['basedn'],$basedn)) {
            //~ $sbasedn=$this -> sumDn($filter[$i]['basedn'],$basedn);
          //~ }
          // Finalisation du filtre
          $sfilter_for.=$sfilter_end;
        
          //~ print 'filter1 : '.$sfilter_for." | basedn : ".$sbasedn."\n";
        
          // Execution de la recherche
          $ret=$GLOBALS['LSldap'] -> search ($sfilter_for,$sbasedn,$sparams);
          
          //~ print('Nb resultat : '.count($ret));
          
          // Si il y un retour
          if(isset($ret[0])) {
            //~ print_r($ret);
            // si il ya une suite (recherche intermédiaire)
            if($filter[$i]['attr']){
              for($iii=0;$iii<count($ret);$iii++) {
                if(isset($ret[$iii]['attrs'][$filter[$i]['attr']])) {
                  // cas de valeur multiple
                  if(is_array($ret[$iii]['attrs'][$filter[$i]['attr']])) {
                    foreach($ret[$iii]['attrs'][$filter[$i]['attr']] as $val_attr) {
                      $new_attrs[]=$val_attr;
                    }
                  }
                  // cas de valeur unique
                  else {
                    $new_attrs[]=$ret[$iii]['attrs'][$filter[$i]['attr']];
                  }
                }
              }
            }
            else {
              // vérification de la compatibilité de la compatibilité du DN resultant
              // et du basedn de recherche 
              if (!$this -> isCompatibleDNs($ret[0]['dn'],$basedn))
                continue;
              // ajout du DN au resultat finale
              $ret_gen[]=$ret[0]['dn'];
            }
          }
        }
        // cas du dernier filtre
        if(!empty($ret_gen)) {
          // on quitte la boucle des filtres de la conf
          $ret=$ret_gen;
          break;
        }
        // dans le cas d'une suite prévu mais d'un retour nul de la précédente recherche
        else if(empty($new_attrs)) {
            // retour vide et arrêt de la recherche
            $ret=array();
            break;
        }
        else {
          $attrs=$new_attrs;
        }
      }
      // Dans le cas de la recherche initiale
      else {
        // Déclaration du filtre de recherche
        if((isset($filter[$i]['filter']))&&(!empty($filter[$i]['filter']))) {
          if($filter[$i]['filter'][0]=='(') {
            $sfilter.=$filter[$i]['filter'];
          }
          else {
            $sfilter.='('.$filter[$i]['filter'].')';
          }
        }
        // fermeture du filtre
        $sfilter.=$sfilter_end;
        
        //~ print 'filter2 : '.$sfilter."\n";
        //~ print_r($sparams);
        
        // Lancement de la recherche
        $ret=$GLOBALS['LSldap'] -> search ($sfilter,$sbasedn,$sparams);
        
        //~ print('Nb resultat : '.count($ret));
        
        //Si filtre multiple => on recupère une liste d'attributs
        if(isset($filter[$i]['attr'])) {
          for($ii=0;$ii<count($ret);$ii++) {
            if(isset($ret[$ii]['attrs'][$filter[$i]['attr']])) {
              // cas de valeur multiple
              if(is_array($ret[$ii]['attrs'][$filter[$i]['attr']])) {
                foreach($ret[$ii]['attrs'][$filter[$i]['attr']] as $val_attr) {
                  $attrs[]=$val_attr;
                }
              }
              // cas de valeur unique
              else {
                $attrs[]=$ret[$ii]['attrs'][$filter[$i]['attr']];
              }
            }
          }
          
          // Si aucunne valeur n'est retournées
          if(empty($attrs)){
            // arrêt et retour à zéro
            $ret=array();
            break;
          }
        }
        // Si recherche unique
        else {
          // préparation du retour finale
          $ret_final=array();
          foreach($ret as $obj)
            $ret_final[]=$obj['dn'];
          $ret=$ret_final;
          break;
        }
      }
      //~ print_r($attrs);
    }
    
    // Création d'un tableau d'objet correspondant au valeur retourné
    for($i=0;$i<count($ret);$i++) {
      $retInfos[$i] = new $this -> type_name($this -> config);
      $retInfos[$i] -> loadData($ret[$i]);
      //~ echo $ret[$i]['dn']."\n";
    }
    
    return $retInfos;
    
  }
  
  /**
   * Retourne une valeur de l'objet
   *
   * Retourne une valeur en fonction du paramètre. Si la valeur est inconnue, la valeur retourné est ' '.
   * tableau d'objet correspond au resultat de la recherche.
   *
   * Valeurs possibles :
   * - 'dn' ou '%{dn} : DN de l'objet
   * - [nom d'un attribut] : valeur de l'attribut
   * - [clef de $this -> other_values] : valeur de $this -> other_values
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $val string nom de la valeur demandée
   *
   * @retval mixed la valeur demandé ou ' ' si celle-ci est inconnue.
   */	
  function getValue($val) {
    if(($val=='dn')||($val=='%{dn}')) {
      return $this -> dn;
    }
    else if(isset($this ->  attrs[$val])){
      if (method_exists($this ->  attrs[$val],'getValue'))
        return $this -> attrs[$val] -> getValue();
      else
        return ' ';
    }
    else if(isset($this -> other_values[$val])){
      return $this -> other_values[$val];
    }
    else {
      return ' ';
    }
  }
  
}

?>