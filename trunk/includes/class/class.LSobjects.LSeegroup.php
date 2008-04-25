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
 * Objet Ldap eegroup
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSeegroup extends LSldapObject {

  /**
   * Constructeur
   *
   * Cette methode construit l'objet et définis la configuration.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $config array La configuration de l'objet
   *
   * @retval boolean true si l'objet a été construit, false sinon.
   *
   * @see LSldapObject::LSldapObject()
   */
  function LSeegroup ($config='auto') {
    $this -> LSldapObject('LSeegroup',$config);
  }

  /**
   * Retourne la liste des groupes d'un utilisateur
   * 
   * Retourne un tableau de LSeegroup correspondant aux groupes
   * auxquels appartient un utilisateur
   * 
   * @param[in] $userObject Un object LSeepeople
   * 
   * @retval Array of LSeegroup Les groupes de l'utilisateur
   **/
  function listUserGroups($userObject) {
    $dn = $userObject -> getDn();
    $filter = $this -> getObjectFilter();
    $filter = '(&'.$filter.'(uniqueMember='.$dn.'))';
    return $this -> listObjects($filter);
  }

  /**
   * Ajoute un utilisateur au groupe
   * 
   * @param[in] $object Un object LSeepeople : l'utilisateur à ajouter
   * 
   * @retval boolean true si l'utilisateur à été ajouté, False sinon
   **/  
  function addOneMember($object) {
    if ($object instanceof LSeepeople) {
      if ($this -> attrs['uniqueMember'] instanceof LSattribute) {
        $dn = $object -> getDn();
        $values = $this -> attrs['uniqueMember'] -> getValue();
        if (!is_array($values)) {
          $updateData = array($dn);
        }
        else if (!in_array($dn,$values)) {
          $values[]=$dn;
          $updateData = $values;
        }
        if (isset($updateData)) {
          return $GLOBALS['LSldap'] -> update($this -> getType(),$this -> getDn(), array('uniqueMember' => $updateData));
        }
        return true;
      }
    }
    return;
  }
  
  /**
   * Supprime un utilisateur du groupe
   * 
   * @param[in] $object Un object LSeepeople : l'utilisateur à supprimer
   * 
   * @retval boolean true si l'utilisateur à été supprimé, False sinon
   **/  
  function deleteOneMember($object) {
    if ($object instanceof LSeepeople) {
      if ($this -> attrs['uniqueMember'] instanceof LSattribute) {
        $dn = $object -> getDn();
        $values = $this -> attrs['uniqueMember'] -> getValue();
        if ((!is_array($values)) && (!empty($values))) {
          $values = array($values);
        }
        if (is_array($values)) {
          $updateData=array();
          foreach($values as $value) {
            if ($value!=$dn) {
              $updateData[]=$value;
            }
          }
          return $GLOBALS['LSldap'] -> update($this -> getType(),$this -> getDn(), array('uniqueMember' => $updateData));
        }
        return;
      }
    }
    return;
  }
  
  /**
   * Met à jour les groupes d'un utilisateur
   * 
   * @param[in] $userObject LSeepeople Un object LSeepeople : l'utilisateur
   * @param[in] $listDns Array(string) Un tableau des DNs des groupes de l'utilisateur
   * 
   * @retval boolean true si tout c'est bien passé, False sinon
   **/  
  function updateUserGroups($userObject,$listDns) {
    $currentGroups = $this -> listUserGroups($userObject);
    $type=$this -> getType();
    if(is_array($currentGroups)) {
      if (is_array($listDns)) {
        $dontDelete=array();
        $dontAdd=array();
        for ($i=0;$i<count($currentGroups);$i++) {
          $dn = $currentGroups[$i] -> getDn();
          if (in_array($dn, $listDns)) {
            $dontDelete[$i]=true;
            $dontAdd[]=$dn;
          }
        }
        
        for($i=0;$i<count($currentGroups);$i++) {
          if ($dontDelete[$i]) {
            continue;
          }
          else {
            if (!$currentGroups[$i] -> deleteOneMember($userObject)) {
              return;
            }
          }
        }
        
        foreach($listDns as $dn) {
          if (in_array($dn,$dontAdd)) {
            continue;
          }
          else {
            $object = new $type();
            if ($object -> loadData($dn)) {
              if (!$object -> addOneMember($userObject)) {
                return;
              }
            }
            else {
              return;
            }
          }
        }
        return true;
      }
    }
    else {
      if(!is_array($listDns)) {
        return true;
      }
      foreach($listDns as $dn) {
        $object = new $type();
        if ($object -> loadData($dn)) {
          if (!$object -> addOneMember($userObject)) {
            return;
          }
        }
        else {
          return;
        }
      }
    }
  }
}

?>
