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

require_once 'includes/functions.php';
require_once 'includes/class/class.LSsession.php';

$GLOBALS['LSsession'] = new LSsession();

if($LSsession -> startLSsession()) {

	// Définition du Titre de la page
	$GLOBALS['Smarty'] -> assign('pagetitle',_('Mon compte'));

	// ---- les objets LDAP
	// Création d'un LSeepeople
	$eepeople = new LSeepeople();
	
	// Chargement des données de l'objet depuis l'annuaire et à partir de son DN
	$eepeople-> loadData($GLOBALS['LSsession']->dn);
	
	// Création d'un formulaire à partir pour notre objet LDAP
	$form=$eepeople -> getForm('test');
	
	// Gestion de sa validation
	if ($form->validate()) {
	  // MàJ des données de l'objet LDAP
	  $eepeople -> updateData('test');
	}
	// Affichage du formulaire
	$form -> display();

	// Template
	$GLOBALS['LSsession'] -> setTemplate('base.tpl');
}
else {
	$GLOBALS['LSsession'] -> setTemplate('login.tpl');
}

// Affichage des retours d'erreurs
$GLOBALS['LSsession'] -> displayTemplate();
?>
