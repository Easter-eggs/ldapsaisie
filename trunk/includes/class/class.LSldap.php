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

  var $config;
  var $cnx = NULL;

  /**
   * Constructeur
   *
   * Cette methode définis la configuration de l'accès à l'annuaire
   * et établie la connexion.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $config array Tableau de configuration au formar Net_LDAP2
   *
   * @retval void
   *
   * @see Net_LDAP2::connect()
   */
  function LSldap ($config) {
    $this -> config = $config;
    $this -> connect();
  }
  
  /**
   * Connection
   *
   * Cette methode établie la connexion à l'annuaire Ldap
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la connection est établie, false sinon
   */
  function connect() {
    $this -> cnx = Net_LDAP2::connect($this -> config);
    if (Net_LDAP2::isError($this -> cnx)) {
      $GLOBALS['LSerror'] -> addErrorCode(1,$this -> cnx -> getMessage());
      $this -> cnx = NULL;
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
   * @param[in] $params array Paramètres de recherche au format Net_LDAP2::search()
   *
   * @see Net_LDAP2::search()
   *
   * @retval array Retourne un tableau associatif contenant :
   *               - ['dn'] : le DN de l'entré
   *               - ['attrs'] : tableau associatif contenant les attributs (clé)
   *                             et leur valeur (valeur).
   */
  function search ($filter,$basedn=NULL,$params = array()) {
    $ret = $this -> cnx -> search($basedn,$filter,$params);
    if (Net_LDAP2::isError($ret)) {
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
  function getNumberResult ($filter,$basedn=NULL,$params = array() ) {
    if (empty($filter))
      $filter=NULL;
    $ret = $this -> cnx -> search($basedn,$filter,$params);
    if (Net_LDAP2::isError($ret)) {
      $GLOBALS['LSerror'] -> addErrorCode(2,$ret -> getMessage());
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
   * Retourne une entrée existante ou nouvelle
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $object_type string Type de l'objet Ldap
   * @param[in] $dn string DN de l'entré Ldap
   *
   * @retval ldapentry|array Un objet ldapentry (PEAR::Net_LDAP2)
	 * 												 ou un tableau (si c'est une nouvelle entr�e):
	 *													Array (
	 *														'entry' => ldapentry,
	 *														'new' => true
	 *													)
   */
  function getEntry($object_type,$dn) {
    if(isset($GLOBALS['LSobjects'][$object_type])){
      $obj_conf=$GLOBALS['LSobjects'][$object_type];
      $entry = $this -> cnx -> getEntry($dn);
      if (Net_LDAP2::isError($entry)) {
        //$newentry = new Net_LDAP2_Entry(&$this -> cnx);
        //$newentry -> dn($dn);
				//$newentry -> add(array('objectclass' => $obj_conf['objectclass']));
        //foreach($obj_conf['attrs'] as $attr_name => $attr_conf) {
        //  $newentry->add(array($attr_name => $attr_conf['default_value']));
        //}
				$attributes = array(
					'objectclass' => $obj_conf['objectclass']
				);
				foreach($obj_conf['attrs'] as $attr_name => $attr_conf) {
					if( isset($attr_conf['default_value']) ) {
						$attributes[$attr_name]=$attr_conf['default_value'];
					}
        }
        $newentry = Net_LDAP2_Entry::createFresh($dn,$attributes);

        return array('entry' => $newentry,'new' => true);
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
  function update($object_type,$dn,$change) {
    debug($change);
    $dropAttr=array();
		$entry=$this -> getEntry($object_type,$dn);
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
      $entry -> replace($changeData);
      debug('change : <pre>'.print_r($changeData,true).'</pre>');
      debug('drop : <pre>'.print_r($dropAttr,true).'</pre>');

			if ($new) {
				$ret = $this -> cnx -> add($entry);
			}
			else {
	      $ret = $entry -> update();
			}
			
      if (Net_LDAP2::isError($ret)) {
        $GLOBALS['LSerror'] -> addErrorCode(5,$dn);
        debug('NetLdap-Error : '.$ret->getMessage());
      }
      else {
				if (!empty($dropAttr)) {
					foreach($dropAttr as $attr) {
  	     		$entry -> delete($attr);
    	 		}
				}
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
   * Cette methode établie une connexion à l'annuaire Ldap et test un bind
   * avec un login et un mot de passe passé en paramètre
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la connection à réussi, false sinon
   */
  function checkBind($dn,$pwd) {
    $config = $this -> config;
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
  function isConnected() {
    return ($this -> cnx == NULL)?false:true;
  }
  
  /**
   * Supprime un objet de l'annuaire
   *
   * @param[in] string DN de l'objet à supprimer
   * 
   * @retval boolean True si l'objet à été supprimé, false sinon
   */
  function remove($dn) {
    return $this -> cnx -> delete($dn,array('recursive' => true));
  }

}

?>
