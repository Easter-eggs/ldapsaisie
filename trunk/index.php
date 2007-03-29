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

$conf_dir='conf/';
require_once $conf_dir.'config.php';
require_once $conf_dir.'error_code.php';
require_once $conf_dir.'config.LSeepeople.php';
require_once $conf_dir.'config.LSeegroup.php';
require_once $GLOBALS['LSconfig']['NetLDAP'];
require_once $GLOBALS['LSconfig']['QuickForm'];

$include_dir='includes/';
require_once $include_dir.'functions.php';
$class_dir=$include_dir.'class/';
require_once $class_dir.'class.LSerror.php';
require_once $class_dir.'class.LSldap.php';
require_once $class_dir.'class.LSldapObject.php';
require_once $class_dir.'class.LSattribute.php';
require_once $class_dir.'class.LSattr_ldap.php';
require_once $class_dir.'class.LSattr_ldap_ascii.php';
require_once $class_dir.'class.LSattr_ldap_numeric.php';
require_once $class_dir.'class.LSattr_html.php';
require_once $class_dir.'class.LSattr_html_text.php';
require_once $class_dir.'class.LSattr_html_select_list.php';

require_once $class_dir.'class.LSeepeople.php';
require_once $class_dir.'class.LSeegroup.php';

require_once $class_dir.'class.LSform.php';
echo "<pre>";

// "Activation" de la gestion des erreurs
$LSerror = new LSerror();

// Connexion à l'annuaire
$LSldap = new LSldap($GLOBALS['LSconfig']['ldap_config']);

// ---- les objets LDAP
// Création d'un LSeepeople
$eepeople = new LSeepeople($GLOBALS['LSobjects']['LSeepeople']);
$eegroup = new LSeegroup($GLOBALS['LSobjects']['LSeegroup']);
// Chargement des données de l'objet depuis l'annuaire et à partir de son DN
$eepeople-> loadData('uid=eeggs,ou=people,o=ost');
$eegroup-> loadData('cn=adminldap,ou=groups,o=ost');

// Création d'un formulaire à partir pour notre objet LDAP
$form=$eepeople -> getForm('test');

// Gestion de sa validation
if ($form->validate()) {
  // MàJ des données de l'objet LDAP
  $eepeople -> updateData('test');
}
// Affichage du formulaire
$form -> display();


// Affichage des retours d'erreurs
$LSerror -> display();
echo "</pre>";
?>