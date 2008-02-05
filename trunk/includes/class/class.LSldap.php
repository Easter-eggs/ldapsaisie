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
 * Gestion de l'acc�s � l'annaire Ldap
 *
 * Cette classe g�re l'acc�s � l'annuaire ldap en s'appuyant sur PEAR :: Net_LDAP
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSldap {

  var $config;
  var $cnx = NULL;

  /**
   * Constructeur
   *
   * Cette methode d�finis la configuration de l'acc�s � l'annuaire
   * et �tablie la connexion.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $config array Tableau de configuration au formar Net_LDAP
   *
   * @retval void
   *
   * @see Net_LDAP::connect()
   */
  function LSldap ($config) {
    $this -> config = $config;
    $this -> connect();
  }
  
  /**
   * Connection
   *
   * Cette methode �tablie la connexion � l'annuaire Ldap
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la connection est �tablie, false sinon
   */
  function connect() {
    $this -> cnx = Net_LDAP::connect($this -> config);
    if (Net_LDAP::isError($this -> cnx)) {
      $GLOBALS['LSerror'] -> addErrorCode(1,$this -> cnx -> getMessage());
      $this -> cnx = NULL;
      return;
    }
    return true;
  }
  
  /**
   * D�connection
   *
   * Cette methode clos la connexion � l'annuaire Ldap
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  function close() {
    $this -> cnx -> done();
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
   * @param[in] $params array Param�tres de recherche au format Net_LDAP::search()
   *
   * @see Net_LDAP::search()
   *
   * @retval array Retourne un tableau associatif contenant :
   *               - ['dn'] : le DN de l'entr�
   *               - ['attrs'] : tableau associatif contenant les attributs (cl�)
   *                             et leur valeur (valeur).
   */
  function search ($filter,$basedn=NULL,$params = array()) {
    $ret = $this -> cnx -> search($basedn,$filter,$params);
    if (Net_LDAP::isError($ret)) {
      $GLOBALS['LSerror'] -> addErrorCode(2,$ret -> getMessage());
      return;
    }
    $retInfos=array();
    foreach($ret -> entries() as $entry) {
      $retInfos[]=array('dn' => $entry -> dn(), 'attrs' => $entry -> getValues());
    }
    return $retInfos;
  }
  
  /**
   * Compte le nombre de retour d'une recherche dans l'annuaire
   *
   * Cette methode effectue une recherche dans l'annuaire et retourne le nombre
   * d'entr�s trouv�es.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $filter [<b>required</b>] string Filtre de recherche Ldap
   * @param[in] $basedn string DN de base pour la recherche
   * @param[in] $params array Param�tres de recherche au format Net_LDAP::search()
   *
   * @see Net_LDAP::search()
   *
   * @retval numeric Le nombre d'entr� trouv�es
   */
  function getNumberResult ($filter,$basedn=NULL,$params = array() ) {
    if (empty($filter))
      $filter=NULL;
    $ret = $this -> cnx -> search($basedn,$filter,$params);
    if (Net_LDAP::isError($ret)) {
      $GLOBALS['LSerror'] -> addErrorCode(2,$ret -> getMessage());
      return;
    }
    return $ret -> count();
  }
  
  /**
   * Charge les valeurs des attributs d'une entr� de l'annuaire
   *
   * Cette methode recup�re les valeurs des attributs d'une entr�e de l'annaire
   * et les retournes sous la forme d'un tableau.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $dn string DN de l'entr� Ldap
   *
   * @retval array Tableau associatif des valeurs des attributs avec en clef, le nom de l'attribut.
   */
  function getAttrs($dn) {
    $infos = ldap_explode_dn($dn,0);
    if((!$infos)||($infos['count']==0))
      return;
    $basedn='';
    for ($i=1;$i<$infos['count'];$i++) {
      $sep=($basedn=='')?'':',';
      $basedn.=$sep.$infos[$i];
    }
    $return=$this -> search($infos[0],$basedn);
    return $return[0]['attrs'];
  }
  
  /**
   * Retourne une entr�e existante ou nouvelle
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $object_type string Type de l'objet Ldap
   * @param[in] $dn string DN de l'entr� Ldap
   *
   * @retval ldapentry Un objet ldapentry (PEAR::Net_LDAP)
   */
  function getEntry($object_type,$dn) {
    if(isset($GLOBALS['LSobjects'][$object_type])){
      $obj_conf=$GLOBALS['LSobjects'][$object_type];
      $entry = $this -> cnx -> getEntry($dn);
      if (Net_Ldap::isError($entry)) {
        $newentry = new Net_Ldap_Entry(&$this -> cnx);
        $newentry -> dn($dn);
        $newentry -> add(array('objectclass' => $obj_conf['objectclass']));
        foreach($obj_conf['attrs'] as $attr_name => $attr_conf) {
          $newentry->add(array($attr_name => $attr_conf['default_value']));
        }
        return $newentry;
      }
      else {
        return $entry;
      }
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(3);
      return;
    }
  }
  
  /**
   * Met � jour une entr�e dans l'annuaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $object_type string Type de l'objet Ldap
   * @param[in] $dn string DN de l'entr� Ldap
   * @param[in] $change array Tableau des modifications � apporter
   *
   * @retval boolean true si l'objet a bien �t� mis � jour, false sinon
   */
  function update($object_type,$dn,$change) {
		debug($change);
    if($entry=$this -> getEntry($object_type,$dn)) {
      $entry -> replace($change);
      $ret = $entry -> update();
      if (Net_Ldap::isError($ret)) {
        $GLOBALS['LSerror'] -> addErrorCode(5,$dn);
				debug('NetLdap-Error : '.$ret->getMessage());
      }
      else {
        return true;
      }
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(4);
      return;
    }
  }

  /**
   * Test de bind
   *
   * Cette methode �tablie une connexion � l'annuaire Ldap et test un bind
	 * avec un login et un mot de passe pass� en param�tre
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la connection � r�ussi, false sinon
   */
  function checkBind($dn,$pwd) {
		$config = $this -> config;
		$config['binddn'] = $dn;
		$config['bindpw'] = $pwd;
    $cnx = Net_LDAP::connect($config);
    if (Net_LDAP::isError($cnx)) {
      return;
    }
    return true;
  }

	/**
	 * Retourne l'�tat de la connexion Ldap
	 *
	 * @retval boolean True si le serveur est connect�, false sinon.
	 */
	function isConnected() {
		return ($this -> cnx == NULL)?false:true;
	}

}

?>
