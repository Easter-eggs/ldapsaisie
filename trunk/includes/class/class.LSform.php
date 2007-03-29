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
 * Formulaire pour LdapSaisie
 *
 * Cette classe gère les formulaires en se basant sur PEAR::HTML_QuickForm
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSform {
  
  var $quickform;
  var $idForm;
  var $can_validate=true;
  
  /**
   * Constructeur
   *
   * Cette methode construit l'objet et définis la configuration.
   * Elle lance la construction de l'objet HTML_QuickForm et définis les élements
   * de base à communiquer de page en page par le formulaire.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $idForm [<b>required</b>] string L'identifiant du formulaire
   * @param[in] $submit string La valeur du bouton submit
   *
   * @retval void
   */	
  function LSform ($idForm,$submit="Envoyer"){
    $this -> idForm = $idForm;
    $this -> submit = $submit;
    $this -> quickform = new HTML_QuickForm($idForm);
    $this -> quickform -> addElement('hidden',"LSdata['idForm']",$idForm);
  }
  
  /**
   * Affiche le formualaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */	
  function display(){
    if($this -> can_validate) {
      $this -> quickform -> addElement('submit', null, $this -> submit);
    }
    $this -> quickform -> display();
  }
  
  /**
   * Défini l'erreur sur un champ
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $attr [<b>required</b>] string Le nom du champ
   * @param[in] $msg Le format du message d'erreur à afficher (pouvant comporter
   *                 des valeurs %{[n'importe quoi]} qui seront remplacé par le label
   *                 du champs concerné.
   *
   * @retval void
   */	
  function setElementError($attr,$msg=NULL) {
    //~ print 'erreur<br />';
    if($msg!='') {
      $msg_error=getFData($msg,$attr->getLabel());
    }
    else {
      $msg_error="Les données pour l'attribut ".$attr->getLabel()." ne sont pas valides.";
    }
    $this -> quickform -> setElementError($attr->name,$msg_error);
  }
  
  /**
   * Verifie si le formulaire a été validé et que les données sont valides.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si le formulaire a été validé et que les données ont été validées, false sinon
   */	
  function validate(){
    return (($this -> can_validate)&&($this -> quickform -> validate()));
  }
  
}

?>