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
 * Objet Ldap group
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSgroup extends LSldapObject {

  var $userObjectType = 'LSpeople';
  var $memberAttr = 'uniqueMember';

  /* ========== Members ========== */
  /**
   * Retourne la valeur clef d'un membre
   * 
   * @param[in] $object Un object utilisateur
   * 
   * @retval Mixed La valeur clef d'un membre
   **/
  function getMemberKeyValue($object) {
    return $this -> getObjectKeyValueInRelation($object,$this -> memberAttr,$this -> userObjectType);
  }
  
  /**
   * Retourne la liste des groupes pour utilisateur
   * 
   * Retourne un tableau de LSgroup correspondant aux groupes
   * auxquels appartient un utilisateur
   * 
   * @param[in] $userObject Un object user (type : $this -> userObjectType)
   * 
   * @retval Array of LSgroup Les groupes de l'utilisateur
   **/
  function listUserGroups($userObject) {
    return $this -> listObjectsInRelation($userObject,$this -> memberAttr,$this -> userObjectType);
  }

  /**
   * Ajoute un utilisateur au groupe
   * 
   * @param[in] $object Un object user ($this -> userObjectType) : l'utilisateur à ajouter
   * 
   * @retval boolean true si l'utilisateur à été ajouté, False sinon
   **/  
  function addOneMember($object) {
    return $this -> addOneObjectInRelation($object,$this -> memberAttr, $this -> userObjectType);
  }
  
  /**
   * Supprime un utilisateur du groupe
   * 
   * @param[in] $object Un object (type : $this -> userObjectType) : l'utilisateur à supprimer
   * 
   * @retval boolean true si l'utilisateur à été supprimé, False sinon
   **/  
  function deleteOneMember($object) {
    return $this -> deleteOneObjectInRelation($object,$this -> memberAttr,$this -> userObjectType);
  }
  
 /**
  * Renome un utilisateur du groupe
  * 
  * @param[in] $object Un object (type : $this -> userObjectType) : l'utilisateur à renomer
  * @param[in] $oldDn string L'ancien DN de l'utilisateur
  * 
  * @retval boolean True en cas de succès, False sinon
  */
  function renameOneMember($object,$oldDn) {
    return $this -> renameOneObjectInRelation($object,$oldDn,$this -> memberAttr,$this -> userObjectType);
  }
  
  /**
   * Met à jour les groupes d'un utilisateur
   * 
   * @param[in] $object Mixed Un object (type : $this -> userObjectType) : l'utilisateur
   * @param[in] $listDns Array(string) Un tableau des DNs des groupes de l'utilisateur
   * 
   * @retval boolean true si tout c'est bien passé, False sinon
   **/  
  function updateUserGroups($object,$listDns) {
    return $this -> updateObjectsInRelation($object,$listDns,$this -> memberAttr,$this -> userObjectType);
  }
}

?>