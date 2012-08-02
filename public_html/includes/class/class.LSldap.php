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
 * Gestion de l'accès à l'annaire Ldap
 *
 * Cette classe gère l'accès à l'annuaire ldap en s'appuyant sur PEAR :: Net_LDAP2
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSldap {

  private static $config;
  private static $cnx = NULL;
  
  /**
   * D�fini la configuration
   *
   * Cette methode définis la configuration de l'accès à l'annuaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $config array Tableau de configuration au formar Net_LDAP2
   *
   * @retval void
   */
  function setConfig ($config) {
    self :: $config = $config;
  }
  
  /**
   * Connection
   *
   * Cette methode établie la connexion à l'annuaire Ldap
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @param[in] $config array Tableau de configuration au formar Net_LDAP2
   *
   * @retval boolean true si la connection est établie, false sinon
   */
  public static function connect($config = null) {
    if ($config) {
      self :: setConfig($config);
    }
    self :: $cnx = Net_LDAP2::connect(self :: $config);
    if (Net_LDAP2::isError(self :: $cnx)) {
      LSerror :: addErrorCode('LSldap_01',self :: $cnx -> getMessage());
      self :: $cnx = NULL;
      return;
    }
    return true;
  }
  
  /**
   * Déconnection
   *
   * Cette methode clos la connexion à l'annuaire Ldap
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  public static function close() {
    self :: $cnx -> done();
  }
  
  /**
   * Recherche dans l'annuaire
   *
   * Cette methode effectue une recherche dans l'annuaire et retourne le resultat
   * de celle-ci sous la forme d'un tableau.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $filter [<b>required</b>] string Filtre de recherche Ldap
   * @param[in] $basedn string DN de base pour la recherche
   * @param[in] $params array Paramètres de recherche au format Net_LDAP2::search()
   *
   * @see Net_LDAP2::search()
   *
   * @retval array Retourne un tableau associatif contenant :
   *               - ['dn'] : le DN de l'entré
   *               - ['attrs'] : tableau associatif contenant les attributs (clé)
   *                             et leur valeur (valeur).
   */
  public static function search ($filter,$basedn=NULL,$params = array()) {
    $ret = self :: $cnx -> search($basedn,$filter,$params);
    if (Net_LDAP2::isError($ret)) {
      LSerror :: addErrorCode('LSldap_02',$ret -> getMessage());
      return;
    }
    LSdebug("LSldap::search() : return ".$ret->count()." objet(s)");
    $retInfos=array();
    foreach($ret -> entries() as $entry) {
      if (!$entry instanceof Net_LDAP2_Entry) {
        LSerror :: addErrorCode('LSldap_02',"LDAP search return an ".get_class($entry).". object");
        continue;
      }
      $retInfos[]=array('dn' => $entry -> dn(), 'attrs' => $entry -> getValues());
    }
    return $retInfos;
  }
  
  /**
   * Compte le nombre de retour d'une recherche dans l'annuaire
   *
   * Cette methode effectue une recherche dans l'annuaire et retourne le nombre
   * d'entrés trouvées.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $filter [<b>required</b>] string Filtre de recherche Ldap
   * @param[in] $basedn string DN de base pour la recherche
   * @param[in] $params array Paramètres de recherche au format Net_LDAP2::search()
   *
   * @see Net_LDAP2::search()
   *
   * @retval numeric Le nombre d'entré trouvées
   */
  public static function getNumberResult ($filter,$basedn=NULL,$params = array() ) {
    if (empty($filter))
      $filter=NULL;
    $ret = self :: $cnx -> search($basedn,$filter,$params);
    if (Net_LDAP2::isError($ret)) {
      LSerror :: addErrorCode('LSldap_02',$ret -> getMessage());
      return;
    }
    return $ret -> count();
  }
  
  /**
   * Charge les valeurs des attributs d'une entré de l'annuaire
   *
   * Cette methode recupère les valeurs des attributs d'une entrée de l'annaire
   * et les retournes sous la forme d'un tableau.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string DN de l'entré Ldap
   *
   * @retval array Tableau associatif des valeurs des attributs avec en clef, le nom de l'attribut.
   */
  public static function getAttrs($dn) {
    $infos = ldap_explode_dn($dn,0);
    if((!$infos)||($infos['count']==0))
      return;
    $basedn='';
    for ($i=1;$i<$infos['count'];$i++) {
      $sep=($basedn=='')?'':',';
      $basedn.=$sep.$infos[$i];
    }
    $return=self :: search($infos[0],$basedn);
    return $return[0]['attrs'];
  }
  
  /**
   * Retourne une entrée existante ou nouvelle
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $object_type string Type de l'objet Ldap
   * @param[in] $dn string DN de l'entré Ldap
   *
   * @retval ldapentry|array Un objet ldapentry (PEAR::Net_LDAP2)
   *                         ou un tableau (si c'est une nouvelle entr�e):
   *                          Array (
   *                            'entry' => ldapentry,
   *                            'new' => true
   *                          )
   */
  public static function getEntry($object_type,$dn) {
    $obj_conf=LSconfig :: get('LSobjects.'.$object_type);
    if(is_array($obj_conf)){
      $entry = self :: getLdapEntry($dn);
      if ($entry === false) {
        $newentry = self :: getNewEntry($dn,$obj_conf['objectclass'],array());
        
        if (!$newentry) {
          return;
        }
        return array('entry' => $newentry,'new' => true);
      }
      else {
        return $entry;
      }
    }
    else {
      LSerror :: addErrorCode('LSldap_03');
      return;
    }
  }
  
  /**
   * Retourne un object NetLDAP d'une entree existante
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string DN de l'entré Ldap
   *
   * @retval ldapentry|boolean  Un objet ldapentry (PEAR::Net_LDAP2) ou false en
   *                            cas de probl�me
   */
  public static function getLdapEntry($dn) {
    $entry = self :: $cnx -> getEntry($dn);
    if (Net_LDAP2::isError($entry)) {
      return false;
    }
    else {
      return $entry;
    }
  }
  
 /**
  * Retourne une nouvelle entr�e
  * 
  * @param[in] $dn string Le DN de l'objet
  * @param[in] $objectClass array Un tableau contenant les objectClass de l'objet
  * @param[in] $attrs array Un tabeau du type array('attr_name' => attr_value, ...)
  * 
  * @retval mixed Le nouvelle objet en cas de succ�s, false sinon
  */
  public static function getNewEntry($dn,$objectClass,$attrs,$add=false) {
    $newentry = Net_LDAP2_Entry::createFresh($dn,array_merge(array('objectclass' =>$objectClass),(array)$attrs));
    if(Net_LDAP2::isError($newentry)) {
      return false;
    }
    if($add) {
      if(!self :: $cnx -> add($newentry)) {
        return;
      }
    }
    return $newentry;
  }
  
  /**
   * Met à jour une entrée dans l'annuaire
   * 
   * Remarque : Supprime les valeurs vides de attributs et les attributs sans valeur.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $object_type string Type de l'objet Ldap
   * @param[in] $dn string DN de l'entré Ldap
   * @param[in] $change array Tableau des modifications à apporter
   *
   * @retval boolean true si l'objet a bien été mis à jour, false sinon
   */
  public static function update($object_type,$dn,$change) {
    LSdebug($change);
    $dropAttr=array();
    $entry=self :: getEntry($object_type,$dn);
    if (is_array($entry)) {
      $new = $entry['new'];
      $entry = $entry['entry'];
    }
    else {
      $new = false;
    }

    if($entry) {
      foreach($change as $attrName => $attrVal) {
        $drop = true;
        if (is_array($attrVal)) {
          foreach($attrVal as $val) {
            if (!empty($val)) {
              $drop = false;
              $changeData[$attrName][]=$val;
            }
          }
        }
        else {
          if (!empty($attrVal)) {
            $drop = false;
            $changeData[$attrName][]=$attrVal;
          }
        }
        if($drop) {
          $dropAttr[] = $attrName;
        }
      }
      if (isset($changeData)) {
        $entry -> replace($changeData);
        LSdebug('change : <pre>'.print_r($changeData,true).'</pre>');
        LSdebug('drop : <pre>'.print_r($dropAttr,true).'</pre>');
      }
      else {
        LSdebug('No change');
      }

      if ($new) {
        LSdebug('LSldap :: add()');
        $ret = self :: $cnx -> add($entry);
      }
      else {
        LSdebug('LSldap :: update()');
        $ret = $entry -> update();
      }
      
      if (Net_LDAP2::isError($ret)) {
        LSerror :: addErrorCode('LSldap_05',$dn);
        LSerror :: addErrorCode(0,'NetLdap-Error : '.$ret->getMessage());
      }
      else {
        if (!empty($dropAttr)) {
          foreach($dropAttr as $attr) {
            $value = $entry -> getValue($attr);
            if(Net_LDAP2::isError($value) || empty($value)) {
              // Attribut n'existe pas dans l'annuaire
              continue;
            }
            // M�thode bugg� : suppression impossible de certain attribut
            // exemple : jpegPhoto
            // $entry -> delete($attr);
            $entry -> replace(array($attr =>array()));
          }
          $ret = $entry -> update();
          if (Net_LDAP2::isError($ret)) {
            LSerror :: addErrorCode('LSldap_06');
            LSerror :: addErrorCode(0,'NetLdap-Error : '.$ret->getMessage());
          }
        }
        return true;
      }
    }
    else {
      LSerror :: addErrorCode('LSldap_04');
      return;
    }
  }

  /**
   * Test de bind
   *
   * Cette methode établie une connexion à l'annuaire Ldap et test un bind
   * avec un login et un mot de passe passé en paramètre
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la connection à réussi, false sinon
   */
  public static function checkBind($dn,$pwd) {
    $config = self :: $config;
    $config['binddn'] = $dn;
    $config['bindpw'] = $pwd;
    $cnx = Net_LDAP2::connect($config);
    if (Net_LDAP2::isError($cnx)) {
      return;
    }
    return true;
  }

  /**
   * Retourne l'état de la connexion Ldap
   *
   * @retval boolean True si le serveur est connecté, false sinon.
   */
  public static function isConnected() {
    return (self :: $cnx == NULL)?false:true;
  }
  
  /**
   * Supprime un objet de l'annuaire
   *
   * @param[in] string DN de l'objet à supprimer
   * 
   * @retval boolean True si l'objet à été supprimé, false sinon
   */
  public static function remove($dn) {
    $ret = self :: $cnx -> delete($dn,array('recursive' => true));
    if (Net_LDAP2::isError($ret)) {
      LSerror :: addErrorCode(0,'NetLdap-Error : '.$ret->getMessage());
      return;
    }
    return true;
  }

  /**
   * D�place un objet LDAP dans l'annuaire
   * 
   * @param[in] $old string Le DN actuel
   * @param[in] $new string Le futur DN
   * 
   * @retval boolean True si l'objet a �t� d�plac�, false sinon
   */
  public static function move($old,$new) {
    $ret = self :: $cnx -> move($old,$new);
    if (Net_LDAP2::isError($ret)) {
      LSerror :: addErrorCode('LSldap_07');
      LSerror :: addErrorCode(0,'NetLdap-Error : '.$ret->getMessage());
      return;
    }
    return true;
  }
  
  /**
   * Combine LDAP Filters
   * 
   * @params array Array of LDAP filters
   * 
   * @retval Net_LDAP2_Filter | False The combined filter or False
   **/
  public static function combineFilters($op,$filters,$asStr=false) {
    if (is_array($filters) && !empty($filters)) {
      if (count($filters)==1) {
        return $filters[0];
      }
      $filter=Net_LDAP2_Filter::combine($op,$filters);
      if (!Net_LDAP2::isError($filter)) {
        if ($asStr) {
          return $filter->asString();
        }
        else {
          return $filter;
        }
      }
      else {
        LSerror :: addErrorCode(0,$filter -> getMessage());
      }
    }
    return;
  }
  
  /**
   * Check LDAP Filters String
   * 
   * @params string A LDAP filter as string
   * 
   * @retval boolean True only if the filter could be parsed
   **/
  public static function isValidFilter($filter) {
    if (is_string($filter) && !empty($filter)) {
      $filter=Net_LDAP2_Filter::parse($filter);
      if (!Net_LDAP2::isError($filter)) {
        return true;
      }
      else {
        LSerror :: addErrorCode(0,$filter -> getMessage());
      }
    }
    return;
  }
}

/*
 * Error Codes
 */
LSerror :: defineError('LSldap_01',
  _("LSldap : Error during the LDAP server connection (%{msg}).")
);
LSerror :: defineError('LSldap_02',
  _("LSldap : Error during the LDAP search (%{msg}).")
);
LSerror :: defineError('LSldap_03',
  _("LSldap : Object type unknown.")
);
LSerror :: defineError('LSldap_04',
  _("LSldap : Error while fetching the LDAP entry.")
);
LSerror :: defineError('LSldap_05',
  _("LSldap : Error while changing the LDAP entry (DN : %{dn}).")
);
LSerror :: defineError('LSldap_06',
  _("LSldap : Error while deleting empty attributes.")
);
LSerror :: defineError('LSldap_07',
  _("LSldap : Error while changing the DN of the object.")
);
?>
