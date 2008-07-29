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

$GLOBALS['LSsession'] -> loadLSclass('LSattribute');

/**
 * Base d'un objet ldap
 *
 * Cette classe définis la base de tout objet ldap géré par LdapSaisie
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSldapObject { 
  
  var $config = array();
  var $type_name;
  var $attrs = array();
  var $forms;
  var $view;
  var $dn=false;
  var $other_values=array();
  var $submitError=true;
  var $_whoami=NULL;
  var $_subDn_value=NULL;
  var $_relationsCache=array();
  
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
      if(isset($GLOBALS['LSobjects'][$type_name])) {
        $this -> config = $GLOBALS['LSobjects'][$type_name];
      }
      else {
        $GLOBALS['LSerror'] -> addErrorCode(21);
        return;
      }
    }
    foreach($this -> config['attrs'] as $attr_name => $attr_config) {
      if(!$this -> attrs[$attr_name]=new LSattribute($attr_name,$attr_config,$this)) {
        return;
      }
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
    if(!empty($data)) {
      foreach($this -> attrs as $attr_name => $attr) {
        if(!$this -> attrs[$attr_name] -> loadData($data[$attr_name]))
          return;
      }
      return true;
    }
    return;
  }
  
  /**
   * Recharge les données de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la rechargement a réussi, false sinon.
   */ 
  function reloadData() {
    $data = $GLOBALS['LSldap'] -> getAttrs($this -> dn);
    foreach($this -> attrs as $attr_name => $attr) {
      if(!$this -> attrs[$attr_name] -> reloadData($data[$attr_name]))
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
   * @param[in] $full [<i>optionnel</i>] boolean True pour afficher en plus le
   *                                             subDnName
   *
   * @retval string Valeur descriptive d'affichage de l'objet
   */ 
  function getDisplayValue($spe='',$full=false) {
    if ($spe=='') {
      $spe = $this -> getDisplayAttributes();
    }
    $val = $this -> getFData($spe,&$this -> attrs,'getDisplayValue');
    if ($GLOBALS['LSsession'] -> haveSubDn() && $full) {
      $val.=' ('.$this -> getSubDnName().')';
    }
    return $val;
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
   * Construit un formulaire de l'objet
   * 
   * Cette méthode construit un formulaire LSform à partir de la configuration de l'objet
   * et de chaque attribut.
   *
   * @param[in] $idForm [<b>required</b>] Identifiant du formulaire a créer
   * @param[in] $load DN d'un objet similaire dont la valeur des attribut doit être chargé dans le formulaire.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval LSform Le formulaire crée
   */ 
  function getForm($idForm,$load=NULL) {
    $GLOBALS['LSsession'] -> loadLSclass('LSform');
    $LSform = new LSform($this,$idForm);
    $this -> forms[$idForm] = array($LSform,$load);
    
    if ($load) {
      $type = $this -> getType();
      $loadObject = new $type();
      if (!$loadObject -> loadData($load)) {
        $load=false;
      }
    }
    
    if ($load) {
      foreach($this -> attrs as $attr_name => $attr) {
        if(!$this -> attrs[$attr_name] -> addToForm($LSform,$idForm,$this,$loadObject -> getValue($attr_name))) {
          $LSform -> can_validate = false;
        }
      }
    }
    else {
      foreach($this -> attrs as $attr_name => $attr) {
        if(!$this -> attrs[$attr_name] -> addToForm($LSform,$idForm,$this)) {
          $LSform -> can_validate = false;
        }
      }      
    }
    return $LSform;
  }
  
  /**
   * Construit un formulaire de l'objet
   * 
   * Cette méthode construit un formulaire LSform à partir de la configuration de l'objet
   * et de chaque attribut.
   *
   * @param[in] $idForm [<b>required</b>] Identifiant du formulaire a créer
   * @param[in] $config Configuration spécifique pour le formulaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval LSform Le formulaire crée
   */ 
  function getView() {
    $GLOBALS['LSsession'] -> loadLSclass('LSform');
    $this -> view = new LSform($this,'view');
    foreach($this -> attrs as $attr_name => $attr) {
      $this -> attrs[$attr_name] -> addToView($this -> view);
    }
    $this -> view -> can_validate = false;
    return $this -> view;
  }  
  
  /**
   * Rafraichis le formulaire de l'objet
   * 
   * Cette méthode recharge les données d'un formulaire LSform.
   *
   * @param[in] $idForm [<b>required</b>] Identifiant du formulaire a créer
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true sile formulaire a été rafraichis, false sinon
   */ 
  function refreshForm($idForm) {
    $LSform = $this -> forms[$idForm][0];
    foreach($this -> attrs as $attr_name => $attr) {
      if(!$this -> attrs[$attr_name] -> refreshForm($LSform,$idForm)) {
        return;
      }
    }
    return true;
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
        $GLOBALS['LSerror'] -> addErrorCode(22,$this -> getType());
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
        $GLOBALS['LSerror'] -> addErrorCode(23,$this -> getType());
        return;
      }
    }
    $new_data = $LSform -> exportValues();
    if(!is_array($new_data)) {
      return;
    }
    foreach($new_data as $attr_name => $attr_val) {
      if(isset($this -> attrs[$attr_name])) {
        $this -> attrs[$attr_name] -> setUpdateData($attr_val);
      }
    }
    if($this -> validateAttrsData($idForm)) {
      debug("les données sont validées");
      if(isset($this -> config['before_save'])) {
        if(function_exists($this -> config['before_save'])) {
          if(!$this -> config['before_save']($this)) {
            $GLOBALS['LSerror'] -> addErrorCode(28,$this -> config['before_save']);
            return;
          }
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(27,$this -> config['before_save']);
          return;
        }
      }
      if ($this -> submitChange($idForm)) {
        debug('Les modifications sont submitées');
        $this -> submitError = false;
        $this -> reloadData();
        $this -> refreshForm($idForm);
      }
      else {
        return;
      }
      if((isset($this -> config['after_save']))&&(!$this -> submitError)) {
        if(function_exists($this -> config['after_save'])) {
          if(!$this -> config['after_save']($this)) {
            $GLOBALS['LSerror'] -> addErrorCode(30,$this -> config['after_save']);
            return;
          }
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(29,$this -> config['after_save']);
          return;
        }
      }
      return true;
    }
    else {
      return;
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
      if (!$attr -> isValidate()) {
        if($attr -> isUpdate()) {
          if (!$this -> validateAttrData($LSform, $attr)) {
            return;
          }
        }
        else if( ($attr -> getValue() == '') && ($attr -> isRequired()) ) { 
          if ( $attr -> canBeGenerated()) {
            if ($attr -> generateValue()) {
              if (!$this -> validateAttrData($LSform, $attr)) {
                $GLOBALS['LSerror'] -> addErrorCode(48,$attr -> getLabel());
                return;
              }
            }
            else {
              $GLOBALS['LSerror'] -> addErrorCode(47,$attr -> getLabel());
              return;
            }
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(46,$attr -> getLabel());
            return;
          }

        }
      }
    }
    return true;
  }

   /**
   * Valide les données d'un attribut
   *
   * @param[in] $LSForm Formulaire d'origine
   * @param[in] &$attr Attribut à valider
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si les données sont valides, false sinon
   */
  function validateAttrData(&$LSform,&$attr) {
    $vconfig=$attr -> getValidateConfig();

    $data=$attr -> getUpdateData();
    if(!is_array($data)) {
      $data=array($data);
    }

    // Validation des valeurs de l'attribut
    if(is_array($vconfig)) {
      foreach($vconfig as $test) {
        // Définition du basedn par défaut
        if (!isset($test['basedn'])) {
          $test['basedn']=$GLOBALS['LSsession']->topDn;
        }

        // Définition du message d'erreur
        if (!empty($test['msg'])) {
          $msg_error=getFData($test['msg'],$this,'getValue');
        }
        else {
          $msg_error=getFData(_("L'attribut %{attr} n'est pas valide."),$attr -> getLabel());
        }
        foreach($data as $val) {
          // validation par check LDAP
          if((isset($test['filter'])||isset($test['basedn']))&&(isset($test['result']))) {
            $sparams=(isset($test['scope']))?array('scope' => $test['scope']):array();
            $this -> other_values['val']=$val;
            $sfilter_user=(isset($test['basedn']))?getFData($test['filter'],$this,'getValue'):NULL;
            if(isset($test['object_type'])) {
              $test_obj = new $test['object_type']();
              $sfilter=$test_obj->getObjectFilter();
              $sfilter='(&'.$sfilter;
              if($sfilter_user[0]=='(') {
                $sfilter=$sfilter.$sfilter_user.')';
              }
              else {
                $sfilter=$sfilter.'('.$sfilter_user.'))';
              }
            }
            else {
              $sfilter=$sfilter_user;
            }
            $sbasedn=(isset($test['basedn']))?getFData($test['basedn'],$this,'getValue'):NULL;
            $ret=$GLOBALS['LSldap'] -> getNumberResult ($sfilter,$sbasedn,$sparams);
            if($test['result']==0) {
              if($ret!=0) {
                $LSform -> setElementError($attr,$msg_error);
                return;
              }
            }
            else {
              if($ret<0) {
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
              $GLOBALS['LSerror'] -> addErrorCode(24,array('attr' => $attr->name,'obj' => $this->getType(),'func' => $test['function']));
              return;
            }
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(25,array('attr' => $attr->name,'obj' => $this->getType()));
            return;
          }
        }
      }
    }
    // Génération des valeurs des attributs dépendants
    $dependsAttrs=$attr->getDependsAttrs();
    if (!empty($dependsAttrs)) {
      foreach($dependsAttrs as $dependAttr) {
        if(!isset($this -> attrs[$dependAttr])){
          $GLOBALS['LSerror'] -> addErrorCode(34,array('attr_depend' => $dependAttr, 'attr' => $attr -> getLabel()));
          continue;
        }
        if($this -> attrs[$dependAttr] -> canBeGenerated()) {
          if (!$this -> attrs[$dependAttr] -> generateValue()) {
            $GLOBALS['LSerror'] -> addErrorCode(47,$this -> attrs[$dependAttr] -> getLabel());
            return;
          }
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(46,$this -> attrs[$dependAttr] -> getLabel());
          return;
        }
      }
    }

    $attr -> validate();
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
    $submitData=array();
    foreach($this -> attrs as $attr) {
      if(($attr -> isUpdate())&&($attr -> isValidate())) {
        if($attr -> name == $this -> config['rdn']) {
          debug('Rename');
          if (!$this -> beforeRename()) {
            $GLOBALS['LSerror'] -> addErrorCode(36);
            return;
          }
          $oldDn = $this -> getDn();
          $this -> dn = false;
          $newDn = $this -> getDn();
          if ($newDn) {
            if (!$GLOBALS['LSldap'] -> move($oldDn,$newDn)) {
              return;
            }
            if (!$this -> afterRename($oldDn,$newDn)) {
              $GLOBALS['LSerror'] -> addErrorCode(37);
              return;
            }
          }
          else {
            return;
          }
        }
        else {
          $submitData[$attr -> name] = $attr -> getUpdateData();
        }
      }
    }
    if(!empty($submitData)) {
      $dn=$this -> getDn();
      if($dn) {
        $this -> dn=$dn;
        debug($submitData);
        return $GLOBALS['LSldap'] -> update($this -> getType(),$dn, $submitData);
      }
      else {
        $GLOBALS['LSerror'] -> addErrorCode(33);
        return;
      }
    }
    else {
      return true;
    }
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
   * @param[in] $params array Paramètres de recherche au format Net_LDAP2::search()
   *
   * @retval array Tableau d'objets correspondant au resultat de la recherche
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
          // Finalisation du filtre
          $sfilter_for.=$sfilter_end;
        
        
          // Execution de la recherche
          $ret=$GLOBALS['LSldap'] -> search ($sfilter_for,$sbasedn,$sparams);
          
          // Si il y un retour
          if(isset($ret[0])) {
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
        
        // Lancement de la recherche
        $ret=$GLOBALS['LSldap'] -> search ($sfilter,$sbasedn,$sparams);
        
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
          if (is_array($ret)) {
            $ret_final=array();
            foreach($ret as $obj)
              $ret_final[]=$obj['dn'];
            $ret=$ret_final;
          }
          else {
            $ret=array();
          }
          break;
        }
      }
    }
    
    // Création d'un tableau d'objet correspondant au valeur retourné
    for($i=0;$i<count($ret);$i++) {
      $retInfos[$i] = new $this -> type_name($this -> config);
      $retInfos[$i] -> loadData($ret[$i]);
    }
    
    return $retInfos;
  }
 
 
  /**
   * Recherche un objet � partir de la valeur exact de son RDN
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @param[in] $name string Valeur de son RDN
   * @param[in] $basedn string Le DN de base de la recherche
   * 
   * @retval array Tableau d'objets correspondant au resultat de la recherche
   */
  function searchObject($name,$basedn=NULL) {
    $filter = $this -> config['rdn'].'='.$name; 
    return $this -> listObjects($filter,$basedn); 
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
   * @param[in] $val string Le nom de la valeur demandée
   *
   * @retval mixed la valeur demandé ou ' ' si celle-ci est inconnue.
   */ 
  function getValue($val) {
    if(($val=='dn')||($val=='%{dn}')) {
      return $this -> dn;
    }
    else if(($val=='rdn')||($val=='%{rdn}')) {
      return $this -> attrs[ $this -> config['rdn'] ] -> getValue();
    }
    else if(($val=='subDn')||($val=='%{subDn}')) {
      return $this -> getSubDnValue();
    }
    else if(($val=='subDnName')||($val=='%{subDnName}')) {
      return $this -> getSubDnName();
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

  /**
   * Retourn un tableau pour un select d'un objet du même type
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array('dn' => 'display')
   */
  function getSelectArray($topDn=NULL) {
    $list = $this -> listObjects(NULL,$topDn);
    $return=array();
    foreach($list as $object) {
      $return[$object -> getDn()] = $object -> getDisplayValue(); 
    }
    return $return;
  }

  /**
   * Retourne le DN de l'objet
   *
   * Cette methode retourne le DN de l'objet. Si celui-ci n'existe pas, il le construit à partir de la 
   * configuration de l'objet et la valeur de son attribut rdn.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Le DN de l'objet
   */   
  function getDn() {
    if($this -> dn) {
      return $this -> dn;
    }
    else {
      $rdn_attr=$this -> config['rdn'];
      if( (isset($this -> config['rdn'])) && (isset($this -> attrs[$rdn_attr])) && (isset($this -> config['container_dn'])) && (isset($GLOBALS['LSsession']->topDn)) ) {
        $rdn_val=$this -> attrs[$rdn_attr] -> getUpdateData();
        if (!empty($rdn_val)) {
          return $rdn_attr.'='.$rdn_val[0].','.$this -> config['container_dn'].','.$GLOBALS['LSsession']->topDn;
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(32,$this -> config['rdn']);
          return;
        }
      }
      else {
        $GLOBALS['LSerror'] -> addErrorCode(31,$this -> getType());
        return;
      }
    }
  }

  /**
   * Retourne le type de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval string Le type de l'objet ($this -> type_name)
   */
  function getType() {
    return $this -> type_name;
  }
  
  /**
   * Retourne qui est l'utilisateur par rapport à cet object
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval string 'admin'/'self'/'user' pour Admin , l'utilisateur lui même ou un simple utilisateur
   */
  function whoami() {
    if (!$this -> _whoami)
      $this -> _whoami = $GLOBALS['LSsession'] -> whoami($this -> dn);
    return $this -> _whoami;
  }
  
  /**
   * Retourne le label de l'objet
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval string Le label de l'objet ($this -> config['label'])
   */
  function getLabel() {
    return $this -> config['label'];
  }
  
  
  /**
   * Supprime l'objet dans l'annuaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si l'objet à été supprimé, false sinon
   */
  function remove() {
    return $GLOBALS['LSldap'] -> remove($this -> getDn());
  }
  
  /**
   * L'objet est-il nouveau
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si l'objet est nouveau, false sinon
   */
  function isNew() {
    return (!$this -> dn);
  }

  /**
   * Retourne la valeur (DN) du subDn de l'objet  
   * 
   * @return string La valeur du subDn de l'object
   */
  function getSubDnValue() {
    if ($this -> _subDn_value) {
      return $this -> _subDn_value;
    }
    $dn = $this -> getValue('dn');
    $subDn_value='';
    $subDnLdapServer = $GLOBALS['LSsession'] -> getSortSubDnLdapServer();
    foreach ($subDnLdapServer as $subDn => $subDn_name) {
      if (isCompatibleDNs($subDn,$dn)&&($subDn!=$dn)) {
        $subDn_value=$subDn;
        break;
      }
    }
    $this -> _subDn_value = $subDn_value;
    return $subDn_value;
  }

  /**
   * Retourne la nom du subDn de l'objet  
   * 
   * @return string Le nom du subDn de l'object
   */
  function getSubDnName() {
    $subDnLdapServer = $GLOBALS['LSsession'] -> getSortSubDnLdapServer();
    return $subDnLdapServer[$this -> getSubDnValue()];
  }
  
  /**
   * Methode executant les actions n�c�ssaires avant le changement du DN de
   * l'objet.
   * 
   * Cette m�thode n'est qu'un exemple et elle doit �tre certainement r��crite
   * pour les objets plus complexe.
   * 
   * @retval True en cas de cas ce succ�s, False sinon.
   */
  function beforeRename() {
    if (is_array($this->config['relations'])) {
      foreach($this->config['relations'] as $relation_name => $relation_conf) {
        if ( isset($relation_conf['list_function']) && isset($relation_conf['rename_function']) ) {
          if ($GLOBALS['LSsession'] -> loadLSobject($relation_conf['LSobject'])) {
            $obj = new $relation_conf['LSobject']();
            if (method_exists($obj,$relation_conf['list_function'])) {
              $list = $obj -> $relation_conf['list_function']($this);
              if (is_array($list)) {
                $this -> _relationsCache[$relation_name] = $list; 
              }
              else {
                return;
              }
            }
            else {
              return;
            }
          }
          else {
            return;
          }
        }
      }
    }
    return true;
  }
  
  /**
   * Methode executant les actions n�c�ssaires apr�s le changement du DN de
   * l'objet.
   * 
   * Cette m�thode n'est qu'un exemple et elle doit �tre certainement r��crite
   * pour les objets plus complexe.
   * 
   * @param[in] $oldDn string L'ancien DN de l'objet
   * @param[in] $newDn string Le nouveau DN de l'objet
   * 
   * @retval True en cas de cas ce succ�s, False sinon.
   */
  function afterRename($oldDn,$newDn) {
    $error = 0;
    if($GLOBALS['LSsession'] -> dn == $oldDn) {
      $GLOBALS['LSsession'] -> changeAuthUser($this);
    }
    
    foreach($this -> _relationsCache as $relation_name => $objList) {
      foreach($objList as $obj) {
        $meth = $this->config['relations'][$relation_name]['rename_function'];
        if (method_exists($obj,$meth)) {
          if (!($obj -> $meth($this,$oldDn))) {
            $error=1;
          }
        }
        else {
          $error=1;
        }
      }
    }
    return !$error;
  }
}

?>
