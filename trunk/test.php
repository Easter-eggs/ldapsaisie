<pre>
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

define('LS_CONF_DIR','conf/');
define('LS_INCLUDE_DIR','includes/');
define('LS_CLASS_DIR', LS_INCLUDE_DIR .'class/');
define('LS_LIB_DIR', LS_INCLUDE_DIR .'libs/');
define('LS_ADDONS_DIR', LS_INCLUDE_DIR .'addons/');

require_once  LS_CONF_DIR .'config.php';
require_once  LS_CONF_DIR .'error_code.php';
require_once  LS_CONF_DIR .'config.LSeepeople.php';
require_once  LS_CONF_DIR .'config.LSeegroup.php';
require_once $GLOBALS['LSconfig']['NetLDAP'];

require_once  LS_INCLUDE_DIR .'functions.php';

require_once  LS_CLASS_DIR .'class.LSerror.php';
require_once  LS_CLASS_DIR .'class.LSldap.php';
require_once  LS_CLASS_DIR .'class.LSldapObject.php';
require_once  LS_CLASS_DIR .'class.LSattribute.php';
require_once  LS_CLASS_DIR .'class.LSattr_ldap.php';
require_once  LS_CLASS_DIR .'class.LSattr_ldap_ascii.php';
require_once  LS_CLASS_DIR .'class.LSattr_ldap_password.php';
require_once  LS_CLASS_DIR .'class.LSattr_ldap_numeric.php';
require_once  LS_CLASS_DIR .'class.LSattr_html.php';
require_once  LS_CLASS_DIR .'class.LSattr_html_text.php';
require_once  LS_CLASS_DIR .'class.LSattr_html_textarea.php';
require_once  LS_CLASS_DIR .'class.LSattr_html_password.php';
require_once  LS_CLASS_DIR .'class.LSattr_html_select_list.php';

require_once  LS_CLASS_DIR .'class.LSeepeople.php';
require_once  LS_CLASS_DIR .'class.LSeegroup.php';

require_once  LS_CLASS_DIR .'class.LSform.php';
require_once  LS_CLASS_DIR .'class.LSformElement.php';
require_once  LS_CLASS_DIR .'class.LSformElement_text.php';
require_once  LS_CLASS_DIR .'class.LSformElement_textarea.php';
require_once  LS_CLASS_DIR .'class.LSformElement_select.php';
require_once  LS_CLASS_DIR .'class.LSformElement_password.php';

require_once  LS_CLASS_DIR .'class.LSformRule.php';
require_once  LS_CLASS_DIR .'class.LSformRule_regex.php';
require_once  LS_CLASS_DIR .'class.LSformRule_alphanumeric.php';
require_once  LS_CLASS_DIR .'class.LSformRule_compare.php';
require_once  LS_CLASS_DIR .'class.LSformRule_email.php';
require_once  LS_CLASS_DIR .'class.LSformRule_lettersonly.php';
require_once  LS_CLASS_DIR .'class.LSformRule_maxlength.php';
require_once  LS_CLASS_DIR .'class.LSformRule_minlength.php';
require_once  LS_CLASS_DIR .'class.LSformRule_nonzero.php';
require_once  LS_CLASS_DIR .'class.LSformRule_nopunctuation.php';
require_once  LS_CLASS_DIR .'class.LSformRule_numeric.php';
require_once  LS_CLASS_DIR .'class.LSformRule_rangelength.php';

require_once  LS_ADDONS_DIR .'LSaddons.samba.php';


// Simulation d'une LSsession
$GLOBALS['LSsession']['topDn']='o=lsexample';


// "Activation" de la gestion des erreurs
$LSerror = new LSerror();

// Connexion à l'annuaire
$LSldap = new LSldap($GLOBALS['LSconfig']['ldap_config']);




// =========================================================

// ---- les objets LDAP
// Création d'un LSeepeople
$eepeople = new LSeepeople($GLOBALS['LSobjects']['LSeepeople']);
// Chargement des données de l'objet depuis l'annuaire et à partir de son DN
$eepeople-> loadData('uid=eeggs,ou=people,o=lsexemple');

if (LSaddon_samba_support()) {
	echo "SambaSID : ".generate_sambaSID($eepeople)."<br/>";
	echo "SambaPrimaryGroupSID : ".generate_sambaPrimaryGroupSID($eepeople)."<br/>";
	echo "sambaNTPassword : ".generate_sambaNTPassword($eepeople)."<br/>";
	echo "sambaLMPassword : ".generate_sambaLMPassword($eepeople)."<br/>";
}
else {
	echo "Bug !!!";
}


// =========================================================

// Affichage des retours d'erreurs
$LSerror -> display();
?>
</pre>
<?php debug_print(); ?>
