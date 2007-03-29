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
  function search ($filter,$basedn=NULL,$params = array() ) {
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
}

?>