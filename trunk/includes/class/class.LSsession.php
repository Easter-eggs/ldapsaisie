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

define('LS_DEFAULT_CONF_DIR','conf');

/**
 * Gestion des sessions
 *
 * Cette classe gère les sessions d'utilisateurs.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSsession {

	var $confDir = NULL;
	var $ldapServer = NULL;
	var $topDn = NULL;
	var $LSuserObject = NULL;
	var $dn = NULL;
	var $rdn = NULL;
	var $JSscripts = array();
	var $CssFiles = array();
	var $template = NULL;

  /**
   * Constructeur
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  function LSsession ($configDir=LS_DEFAULT_CONF_DIR) {
		$this -> confDir = $configDir;
		if ($this -> loadConfig()) {
			$this -> startLSerror();
		}
		else {
			return;
		}
  }

 /*
 	* Chargement de la configuration
	*
	* Chargement des fichiers de configuration et création de l'objet Smarty.
	*
	* @author Benjamin Renard <brenard@easter-eggs.com>
	*
	* @retval true si tout c'est bien passé, false sinon
	*/
	function loadConfig() {
		if (loadDir($this -> confDir, '^config\..*\.php$')) {
			if ( @include_once $GLOBALS['LSconfig']['Smarty'] ) {
				$GLOBALS['Smarty'] = new Smarty();
				return true;
			}
			else {
				$GLOBALS['LSerror'] -> addErrorCode(1008);
				return;
			}
			return true;
		}
		else {
			return;
		}
	}

 /*
 	* Initialisation de la gestion des erreurs
	*
	* Création de l'objet LSerror
	*
	* @author Benjamin Renard <brenard@easter-eggs.com
	*
	* @retval boolean true si l'initialisation a réussi, false sinon.
	*/
	function startLSerror() {
		if(!$this -> loadLSclass('LSerror'))
			return;
		$GLOBALS['LSerror'] = new LSerror();
		return true;
	}

 /*
 	* Chargement d'une classe d'LdapSaisie
	*
	* @param[in] $class Nom de la classe à charger (Exemple : LSeepeople)
	* @param[in] $type (Optionnel) Type de classe à charger (Exemple : LSobjects)
	*
	* @author Benjamin Renard <brenard@easter-eggs.com
	* 
	* @retval boolean true si le chargement a réussi, false sinon.
	*/
	function loadLSclass($class,$type='') {
		if (class_exists($class))
			return true;
		if($type!='')
			$type=$type.'.';
		return @include_once LS_CLASS_DIR .'class.'.$type.$class.'.php';
	}

 /*
 	* Chargement d'un object LdapSaisie
	*
	* @param[in] $object Nom de l'objet à charger
	*
	* @retval boolean true si le chargement a réussi, false sinon.
	*/
	function loadLSobject($object) {
		if (!$this -> loadLSclass($object,'LSobjects'))
			return;
		if (!require_once( LS_OBJECTS_DIR . 'config.LSobjects.'.$object.'.php' ))
			return;
		return true;
	}

 /*
 	* Chargement des objects LdapSaisie
	*
	* Chargement des LSobjects contenue dans la variable
	* $GLOBALS['LSobjects']['loads']
	*
	* @retval boolean true si le chargement a réussi, false sinon.
	*/
	function loadLSobjects() {

		$this -> loadLSclass('LSldapObject');

		if(!is_array($GLOBALS['LSobjects']['loads'])) {
			$GLOBALS['LSerror'] -> addErrorCode(1001,"LSobjects['loads']");
			return;
		}

		foreach ($GLOBALS['LSobjects']['loads'] as $object) {
			if ( !$this -> loadLSobject($object) )
				return;
		}
		return true;
	}

 /*
 	* Chargement d'un addons d'LdapSaisie
	*
	* @param[in] $addon Nom de l'addon à charger (Exemple : samba)
	*
	* @author Benjamin Renard <brenard@easter-eggs.com
	* 
	* @retval boolean true si le chargement a réussi, false sinon.
	*/
	function loadLSaddon($addon) {
		return require_once LS_ADDONS_DIR .'LSaddons.'.$addon.'.php';
	}

 /*
 	* Chargement des addons LdapSaisie
	*
	* Chargement des LSaddons contenue dans la variable
	* $GLOBALS['LSaddons']['loads']
	*
	* @retval boolean true si le chargement a réussi, false sinon.
	*/
	function loadLSaddons() {
		if(!is_array($GLOBALS['LSaddons']['loads'])) {
			$GLOBALS['LSerror'] -> addErrorCode(1001,"LSaddons['loads']");
			return;
		}

		foreach ($GLOBALS['LSaddons']['loads'] as $addon) {
			$this -> loadLSaddon($addon);
			if (!call_user_func('LSaddon_'. $addon .'_support')) {
				$GLOBALS['LSerror'] -> addErrorCode(1002,$addon);
			}
		}
		return true;
	}

 /*
  * Initialisation de la session LdapSaisie
	*
	* Initialisation d'une LSsession :
	* - Authentification et activation du mécanisme de session de LdapSaisie
	* - ou Chargement des paramètres de la session à partir de la variable 
	*   $_SESSION['LSsession'].
	* - ou Destruction de la session en cas de $_GET['LSsession_logout'].
	*
	* @retval boolean True si l'initialisation à réussi (utilisateur authentifié), false sinon.
	*/
	function startLSsession() {
			$this -> loadLSobjects();
			$this -> loadLSaddons();
			session_start();

			// Déconnexion
			if (isset($_GET['LSsession_logout'])) {
				session_destroy();
				unset($_SESSION['LSsession']);
			}

			if(isset($_SESSION['LSsession'])) {
				// Session existante
				$this -> confDir = $_SESSION['LSsession'] -> confDir;
				$this -> ldapServer = $_SESSION['LSsession'] -> ldapServer;
				$this -> topDn = $_SESSION['LSsession'] -> topDn;
				$this -> LSuserObject = $_SESSION['LSsession'] -> LSuserObject;
				$this -> dn = $_SESSION['LSsession'] -> dn;
				$this -> rdn = $_SESSION['LSsession'] -> rdn;
				$GLOBALS['Smarty'] -> assign('LSsession_username',$this -> LSuserObject -> getDisplayValue());
				return $this -> LSldapConnect();
			}
			else {
				// Session inexistante

				if (isset($_POST['LSsession_user'])) {
					if (isset($_POST['LSsession_ldapserver'])) {
						$this -> setLdapServer($_POST['LSsession_ldapserver']);
					}
					else {
						$this -> setLdapServer(0);
					}

					// Connexion au serveur LDAP
	        if ($this -> LSldapConnect()) {
						// topDn
						if ( $_POST['LSsession_topDn'] != '' ){
							$this -> topDn = $_POST['LSsession_topDn'];
						}
						else {
							$this -> topDn = $this -> ldapServer['ldap_config']['basedn'];
						}

						if ( $this -> loadLSobject($this -> ldapServer['authobject']) ) {
							$authobject = new $this -> ldapServer['authobject']();
							$result = $authobject -> searchObject($_POST['LSsession_user'],$_POST['LSsession_topDn']);
							$nbresult=count($result);
							if ($nbresult==0) {
								// identifiant incorrect
								debug('identifiant incorrect');
								$GLOBALS['LSerror'] -> addErrorCode(1006);
							}
							else if ($nbresult>1) {
								// duplication d'authentité
								$GLOBALS['LSerror'] -> addErrorCode(1007);
							}
							else {
								if ( $this -> checkUserPwd($result[0],$_POST['LSsession_pwd']) ) {
									// Authentification réussi
									$this -> LSuserObject = $result[0];
									$this -> dn = $result[0]->getValue('dn');
									$this -> rdn = $_POST['LSsession_user'];
									$this -> topDn = $_POST['LSsession_topDn'];
									$GLOBALS['Smarty'] -> assign('LSsession_username',$this -> LSuserObject -> getDisplayValue());
									$_SESSION['LSsession']=$this;
									return true;
								}
								else {
									$GLOBALS['LSerror'] -> addErrorCode(1006);
									debug('mdp incorrect');
								}
							}
						}
						else {
							$GLOBALS['LSerror'] -> addErrorCode(1010);
						}
					}
					else {
						$GLOBALS['LSerror'] -> addErrorCode(1009);
					}
				}
				$this -> displayLoginForm();
				return;
			}
	}

 /*
 	* Définition du serveur Ldap de la session
	*
	* Définition du serveur Ldap de la session à partir de son ID dans 
	* le tableau $GLOBALS['LSconfig']['ldap_servers'].
	*
	* @param[in] integer Index du serveur Ldap
	*
	* @retval boolean True sinon false.
	*/
	function setLdapServer($id) {
		if ( isset($GLOBALS['LSconfig']['ldap_servers'][$id]) ) {
			$this -> ldapServer=$GLOBALS['LSconfig']['ldap_servers'][$id];
			return true;
		}
		else {
			return;
		}
	}

 /*
 	* Connexion au serveur Ldap
	*
	* @retval boolean True sinon false.
	*/
	function LSldapConnect() {
		if ($this -> ldapServer) {
			include_once($GLOBALS['LSconfig']['NetLDAP']);
			if (!$this -> loadLSclass('LSldap'))
				return;
				$GLOBALS['LSldap'] = new LSldap($this -> ldapServer['ldap_config']);
				if ($GLOBALS['LSldap'] -> isConnected())
					return true;
				else
					return;
			return $GLOBALS['LSldap'] = new LSldap($this -> ldapServer['ldap_config']);
		}
		else {
			$GLOBALS['LSerror'] -> addErrorCode(1003);
			return;
		}
	}


	function getSubDnLdapServer() {
		if ( isset($this ->ldapServer['subdnobject']) ) {
			if( $this -> loadLSobject($this ->ldapServer['subdnobject']) ) {
				if ($subdnobject = new $this ->ldapServer['subdnobject']()) {
          return $subdnobject -> getSelectArray();
        }
        else {
        	return;
				}
			}
      else {
     		$GLOBALS['LSerror'] -> addErrorCode(1004,$this ->ldapServer['subdnobject']);
				return;
     	}
		}
		else {
			return;
		}
	}

 /*
 	* Retourne les options d'une liste déroulante pour le choix du topDn
	* de connexion au serveur Ldap
	*
	* Liste les subdnobject ($this ->ldapServer['subdnobject'])
	*
	* @retval string Les options (<option>) pour la sélection du topDn.
	*/
	function getSubDnLdapServerOptions() {
		if ( isset($this ->ldapServer['subdnobject']) ) {
			
			if( $this -> loadLSobject($this ->ldapServer['subdnobject']) ) {
				if ($subdnobject = new $this ->ldapServer['subdnobject']()) {
          return $subdnobject -> getSelectOptions();
        }
        else {
        	return;
				}
			}
      else {
     		$GLOBALS['LSerror'] -> addErrorCode(1004,$this ->ldapServer['subdnobject']);
				return;
     	}
		}
		else {
			return;
		}
	}

 /*
 	* Test un couple LSobject/pwd
	*
	* Test un bind sur le serveur avec le dn de l'objet et le mot de passe fourni.
	*
	* @param[in] LSobject L'object "user" pour l'authentification
	* @param[in] string Le mot de passe à tester
	*
	* @retval boolean True si l'authentification à réussi, false sinon.
	*/
	function checkUserPwd($object,$pwd) {
		return $GLOBALS['LSldap'] -> checkBind($object -> getValue('dn'),$pwd);
	}

 /*
 	* Affiche le formulaire de login
	*
	* Défini les informations pour le template Smarty du formulaire de login.
	*
	* @retval void
	*/
	function displayLoginForm() {
		$GLOBALS['Smarty'] -> assign('pagetitle',_('Connexion'));
		$GLOBALS['Smarty'] -> assign('loginform_action',$_SERVER['PHP_SELF']);
		if (count($GLOBALS['LSconfig']['ldap_servers'])==1) {
			$GLOBALS['Smarty'] -> assign('loginform_ldapserver_style','style="display: none"');
		}
		$GLOBALS['Smarty'] -> assign('loginform_label_ldapserver',_('Serveur LDAP'));
		$ldapservers_name=array();
		$ldapservers_index=array();
		foreach($GLOBALS['LSconfig']['ldap_servers'] as $id => $infos) {
			$ldapservers_index[]=$id;
			$ldapservers_name[]=$infos['name'];
		}
		$GLOBALS['Smarty'] -> assign('loginform_ldapservers_name',$ldapservers_name);
		$GLOBALS['Smarty'] -> assign('loginform_ldapservers_index',$ldapservers_index);

		$this -> setLdapServer(0);
		if ( $this -> LSldapConnect() ) {
			$topDn_array = $this -> getSubDnLdapServer();
			if ( $topDn_array ) {
				$GLOBALS['Smarty'] -> assign('loginform_topdn_name',$topDn_array['display']);
				$GLOBALS['Smarty'] -> assign('loginform_topdn_index',$topDn_array['dn']);
			}
		}

		$GLOBALS['Smarty'] -> assign('loginform_label_level',_('Niveau'));
		$GLOBALS['Smarty'] -> assign('loginform_label_user',_('Identifiant'));
		$GLOBALS['Smarty'] -> assign('loginform_label_pwd',_('Mot de passe'));
		$GLOBALS['Smarty'] -> assign('loginform_label_submit',_('Connexion'));

		$this -> addJSscript('LSsession_login.js');
	}

 /*
 	* Défini le template Smarty à utiliser
	*
	* Remarque : les fichiers de templates doivent se trouver dans le dossier 
	* templates/.
	*
	* @param[in] string Le nom du fichier de template
	*
	* @retval void
	*/
	function setTemplate($template) {
		$this -> template = $template;
	}

 /*
 	* Ajoute un script JS au chargement de la page
	*
	* Remarque : les scripts doivents être dans le dossier LS_JS_DIR.
	*
	* @param[in] $script Le nom du fichier de script à charger.
	*
	* @retval void
	*/
	function addJSscript($script) {
		$this -> JSscripts[]=$script;
	}

 /*
 	* Ajoute une feuille de style au chargement de la page
	*
	* Remarque : les scripts doivents être dans le dossiers templates/css/.
	*
	* @param[in] $script Le nom du fichier css à charger.
	*
	* @retval void
	*/
	function addCssFile($file) {
		$this -> CssFiles[]=$file;
	}

 /*
 	* Affiche le template Smarty
	*
	* Charge les dépendances et affiche le template Smarty
	*
	* @retval void
	*/
	function displayTemplate() {
		// JS
		$JSscript_txt='';
		foreach ($GLOBALS['defaultJSscipts'] as $script) {
			$JSscript_txt.="<script src='".LS_JS_DIR.$script."' type='text/javascript'></script>\n";
		}
		foreach ($this -> JSscripts as $script) {
			$JSscript_txt.="<script src='".LS_JS_DIR.$script."' type='text/javascript'></script>\n";
		}
		$GLOBALS['Smarty'] -> assign('LSsession_js',$JSscript_txt);

		// Css
		$Css_txt="<link rel='stylesheet' type='text/css' href='templates/css/LSdefault.css' media='screen' />\n";
		foreach ($this -> CssFiles as $file) {
			$Css_txt.="<link rel='stylesheet' type='text/css' href='templates/css/$file' media='screen' />\n";
		}
		$GLOBALS['Smarty'] -> assign('LSsession_css',$Css_txt);

		$GLOBALS['LSerror'] -> display();
		debug_print();
		$GLOBALS['Smarty'] -> display($this -> template);
	}
}

?>
