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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Gestion de l'accès à l'annaire Ldap
 *
 * Cette classe gère l'accès à l'annuaire ldap en s'appuyant sur PEAR :: Net_LDAP2
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSldap extends LSlog_staticLoggerClass {

  private static $config;
  private static $cnx = NULL;

  /**
   * D�fini la configuration
   *
   * Cette methode définis la configuration de l'accès à l'annuaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $config array Tableau de configuration au format Net_LDAP2
   *
   * @retval void
   */
  public static function setConfig ($config) {
    self :: $config = $config;
  }

  /**
   * Connect to LDAP server
   *
   * This method  establish connection to LDAP server
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $config array LDAP configuration array in format of Net_LDAP2
   *
   * @retval boolean true if connected, false instead
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
   * Reconnect (or connect) with other credentials
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string Bind DN
   * @param[in] $pwd array Bind password
   * @param[in] $config array LDAP configuration array in format of Net_LDAP2
   *                          (optional, default: keep current)
   *
   * @retval boolean true if connected, false instead
   */
  public static function reconnectAs($dn, $pwd, $config=null) {
    if ($config) {
      self :: setConfig($config);
    }
    if (self :: $cnx) {
      self :: $cnx -> done();
    }
    $config = self :: $config;
    $config['binddn'] = $dn;
    $config['bindpw'] = $pwd;
    self :: $cnx = Net_LDAP2::connect($config);
    if (Net_LDAP2::isError(self :: $cnx)) {
      LSerror :: addErrorCode('LSldap_01', self :: $cnx -> getMessage());
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
  public static function search($filter, $basedn=NULL, $params=array()) {
    $filterstr = (is_a($filter, 'Net_LDAP2_Filter')?$filter->as_string():$filter);
    if (is_empty($basedn)) {
      $basedn = self :: getConfig('basedn');
      if (is_empty($basedn)) {
        LSerror :: addErrorCode('LSldap_08');
        return;
      }
      self :: log_debug("LSldap::search($filterstr): empty basedn provided, use basedn from configuration: ".varDump($basedn));
    }
    self :: log_trace("LSldap::search($filterstr, $basedn): run search with parameters: ".varDump($params));
    $ret = self :: $cnx -> search($basedn, $filter, $params);
    if (Net_LDAP2::isError($ret)) {
      LSerror :: addErrorCode('LSldap_02', $ret -> getMessage());
      return;
    }
    self :: log_debug("LSldap::search($filterstr, $basedn) : return ".$ret->count()." objet(s)");
    $retInfos = array();
    foreach($ret as $dn => $entry) {
      if (!$entry instanceof Net_LDAP2_Entry) {
        LSerror :: addErrorCode('LSldap_02', "LDAP search return an ".get_class($entry).". object");
        continue;
      }
      $retInfos[] = array(
        'dn' => $dn,
        'attrs' => $entry -> getValues()
      );
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
  public static function getNumberResult($filter, $basedn=NULL, $params=array()) {
    if (empty($filter))
      $filter = NULL;
    $filterstr = (is_a($filter, 'Net_LDAP2_Filter')?$filter->as_string():$filter);
    if (is_empty($basedn)) {
      $basedn = self :: getConfig('basedn');
      if (is_empty($basedn)) {
        LSerror :: addErrorCode('LSldap_08');
        return;
      }
      self :: log_debug("LSldap::getNumberResult($filterstr): empty basedn provided, use basedn from configuration: ".varDump($basedn));
    }
    self :: log_trace("LSldap::getNumberResult($filterstr, $basedn): run search with parameters: ".varDump($params));
    $ret = self :: $cnx -> search($basedn, $filter, $params);
    if (Net_LDAP2::isError($ret)) {
      LSerror :: addErrorCode('LSldap_02',$ret -> getMessage());
      return;
    }
    $count = $ret -> count();
    self :: log_trace("LSldap::getNumberResult($filterstr, $basedn): result=$count");
    return $count;
  }

  /**
   * Load values of an LDAP entry attributes
   *
   * This method retreive attributes values of an LDAP entry and return it
   * as associative array.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string DN de l'entré Ldap
   * @param[in] $filter string LDAP filter string (optional, default: null == '(objectClass=*)')
   * @param[in] $attrs array|null Array of requested attribute (optional, default: null == all attributes, excepted internal)
   * @param[in] $include_internal boolean If true, internal attributes will be included (default: false)
   *
   * @retval array|false Associative array of attributes values (with attribute name as key), or false on error
   */
  public static function getAttrs($dn, $filter=null, $attrs=null, $include_internal=false) {
    $infos = ldap_explode_dn($dn,0);
    if((!$infos)||($infos['count']==0))
      return;
    if (!$filter)
      $filter = '(objectClass=*)';
    $params = array(
      'scope' => 'base',
      'attributes' => (is_array($attrs)?$attrs:array('*')),
    );
    if ($include_internal && !in_array('+', $params['attributes']))
      $params['attributes'][] = '+';
    $return = self :: search($filter, $dn, $params);
    if (is_array($return) && count($return) == 1)
      return $return[0]['attrs'];
    return false;
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
    $obj_classes = LSconfig :: get("LSobjects.$object_type.objectclass");
    if(!is_array($obj_classes)){
      LSerror :: addErrorCode('LSldap_03');
      return;
    }
    $entry = self :: getLdapEntry($dn);
    if ($entry === false) {
      $newentry = self :: getNewEntry($dn, $obj_classes, array());
      if (!$newentry) {
        return;
      }

      // Mark entry as new
      $newentry -> markAsNew();
      return $newentry;
    }
    // Mark entry as NOT new
    $entry -> markAsNew(false);

    return $entry;
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
   * Check if an LDAP object exists
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string DN of the LDAP entry to check
   *
   * @retval boolean  True if entry exists, false otherwise
   */
  public static function exists($dn) {
    return is_a(self :: getLdapEntry($dn), 'Net_LDAP2_Entry');
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
  public static function update($object_type, $dn, $change) {
    self :: log_trace("update($object_type, $dn): change=".varDump($change));

    // Retreive current LDAP entry
    $entry = self :: getEntry($object_type, $dn);
    if(!is_a($entry, 'Net_LDAP2_Entry')) {
      LSerror :: addErrorCode('LSldap_04');
      return;
    }

    // Distinguish drop attributes from change attributes
    $changed_attrs = array();
    $dropped_attrs = array();
    foreach($change as $attrName => $attrVal) {
      $drop = true;
      if (is_array($attrVal)) {
        foreach($attrVal as $val) {
          if (!is_empty($val)) {
            $drop = false;
            $changed_attrs[$attrName][]=$val;
          }
        }
      }
      else {
        if (!is_empty($val)) {
          $drop = false;
          $changed_attrs[$attrName][]=$attrVal;
        }
      }
      if($drop) {
        $dropped_attrs[] = $attrName;
      }
    }
    self :: log_trace("update($object_type, $dn): changed attrs=".varDump($changed_attrs));
    self :: log_trace("update($object_type, $dn): dropped attrs=".varDump($dropped_attrs));

    // Set an error flag to false
    $error = false;

    // Handle attributes changes (if need)
    if ($changed_attrs) {
      $entry -> replace($changed_attrs);
      if ($entry -> isNew()) {
        self :: log_debug("update($object_type, $dn): add new entry");
        $ret = self :: $cnx -> add($entry);
      }
      else {
        self :: log_debug("update($object_type, $dn): update entry (for changed attributes)");
        $ret = $entry -> update();
      }

      if (Net_LDAP2::isError($ret)) {
        LSerror :: addErrorCode('LSldap_05',$dn);
        LSerror :: addErrorCode(0,'NetLdap-Error : '.$ret->getMessage());
        return false;
      }
    }
    elseif ($entry -> isNew()) {
      self :: log_error("update($object_type, $dn): no changed attribute but it's a new entry...");
      return false;
    }
    else {
      self :: log_debug("update($object_type, $dn): no changed attribute");
    }

    // Handle droped attributes (is need and not a new entry)
    if ($dropped_attrs && !$entry -> isNew()) {
      // $entry -> delete() method is buggy (for some attribute like jpegPhoto)
      // Prefer replace attribute by an empty array
      $replace_attrs = array();
      foreach($dropped_attrs as $attr) {
        // Check if attribute is present
        if(!$entry -> exists($attr)) {
          // Attribute not present on LDAP entry
          self :: log_debug("update($object_type, $dn): dropped attr $attr is not present in LDAP entry => ignore it");
          continue;
        }
        $replace_attrs[$attr] = array();
      }
      if (!$replace_attrs) {
        self :: log_debug("update($object_type, $dn): no attribute to drop");
        return true;
      }

      // Replace values in LDAP
      $entry -> replace($replace_attrs);
      self :: log_debug("update($object_type, $dn): update entry (for dropped attributes: ".implode(', ', array_keys($replace_attrs)).")");
      $ret = $entry -> update();

      // Check result
      if (Net_LDAP2::isError($ret)) {
        LSerror :: addErrorCode('LSldap_06');
        LSerror :: addErrorCode(0,'NetLdap-Error : '.$ret->getMessage());
        return false;
      }
    }
    return true;
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
    $ret = self :: $cnx -> move($old, $new);
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
        if ($asStr && $filters[0] instanceof Net_LDAP2_Filter) {
          return $filters[0]->asString();
        }
        else {
          return $filters[0];
        }
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

  /**
   * Return a configuration parameter (or default value)
   *
   * @param[] $param	The configuration parameter
   * @param[] $default	The default value (default : null)
   * @param[] $cast	Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  private static function getConfig($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, self :: $config);
  }
}

/*
 * Error Codes
 */
LSerror :: defineError('LSldap_01',
  ___("LSldap: Error during the LDAP server connection (%{msg}).")
);
LSerror :: defineError('LSldap_02',
  ___("LSldap: Error during the LDAP search (%{msg}).")
);
LSerror :: defineError('LSldap_03',
  ___("LSldap: Object type unknown.")
);
LSerror :: defineError('LSldap_04',
  ___("LSldap: Error while fetching the LDAP entry.")
);
LSerror :: defineError('LSldap_05',
  ___("LSldap: Error while changing the LDAP entry (DN : %{dn}).")
);
LSerror :: defineError('LSldap_06',
  ___("LSldap: Error while deleting empty attributes.")
);
LSerror :: defineError('LSldap_07',
  ___("LSldap: Error while changing the DN of the object.")
);
LSerror :: defineError('LSldap_08',
  ___("LSldap: LDAP server base DN not configured.")
);
