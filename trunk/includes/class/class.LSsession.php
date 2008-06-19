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
 * Cette classe gÃ¨re les sessions d'utilisateurs.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSsession {

  var $confDir = NULL;
  var $ldapServer = NULL;
  var $ldapServerId = NULL;
  var $topDn = NULL;
  var $LSuserObject = NULL;
  var $dn = NULL;
  var $rdn = NULL;
  var $JSscripts = array();
  var $CssFiles = array();
  var $template = NULL;
  var $LSrights = array (
    'topDn_admin' => array ()
  );
  var $LSaccess = array();
  var $tmp_file = array();
  var $_subDnLdapServer = array();
  var $ajaxDisplay = false;

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

 /**
  * Chargement de la configuration
  *
  * Chargement des fichiers de configuration et crÃ©ation de l'objet Smarty.
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval true si tout c'est bien passÃ©, false sinon
  */
  function loadConfig() {
    if (loadDir($this -> confDir, '^config\..*\.php$')) {
      if ( include_once $GLOBALS['LSconfig']['Smarty'] ) {
        $GLOBALS['Smarty'] = new Smarty();
        return true;
      }
      else {
        die($GLOBALS['LSerror_code'][1008]['msg']);
        return;
      }
      return true;
    }
    else {
      return;
    }
  }

 /**
  * Initialisation de la gestion des erreurs
  *
  * CrÃ©ation de l'objet LSerror
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval boolean true si l'initialisation a rÃ©ussi, false sinon.
  */
  function startLSerror() {
    if(!$this -> loadLSclass('LSerror'))
      return;
    $GLOBALS['LSerror'] = new LSerror();
    return true;
  }

 /**
  * Chargement d'une classe d'LdapSaisie
  *
  * @param[in] $class Nom de la classe Ã  charger (Exemple : LSeepeople)
  * @param[in] $type (Optionnel) Type de classe Ã  charger (Exemple : LSobjects)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  * 
  * @retval boolean true si le chargement a rÃ©ussi, false sinon.
  */
  function loadLSclass($class,$type='') {
    if (class_exists($class))
      return true;
    if($type!='')
      $type=$type.'.';
    return include_once LS_CLASS_DIR .'class.'.$type.$class.'.php';
  }

 /**
  * Chargement d'un object LdapSaisie
  *
  * @param[in] $object Nom de l'objet Ã  charger
  *
  * @retval boolean true si le chargement a rÃ©ussi, false sinon.
  */
  function loadLSobject($object) {
    $this -> loadLSclass('LSldapObject');
    if (!$this -> loadLSclass($object,'LSobjects')) {
      return;
    }
    if (!require_once( LS_OBJECTS_DIR . 'config.LSobjects.'.$object.'.php' )) {
      return;
    }
    return true;
  }

 /**
  * Chargement des objects LdapSaisie
  *
  * Chargement des LSobjects contenue dans la variable
  * $this -> ldapServer['LSobjects']
  *
  * @retval boolean true si le chargement a rÃ©ussi, false sinon.
  */
  function loadLSobjects() {

    $this -> loadLSclass('LSldapObject');

    if(!is_array($this -> ldapServer['LSobjects'])) {
      $GLOBALS['LSerror'] -> addErrorCode(1001,"LSobjects['loads']");
      return;
    }

    foreach ($this -> ldapServer['LSobjects'] as $object) {
      if ( !$this -> loadLSobject($object) ) {
        return;
      }
    }
    return true;
  }

 /**
  * Chargement d'un addons d'LdapSaisie
  *
  * @param[in] $addon Nom de l'addon Ã  charger (Exemple : samba)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  * 
  * @retval boolean true si le chargement a rÃ©ussi, false sinon.
  */
  function loadLSaddon($addon) {
    return require_once LS_ADDONS_DIR .'LSaddons.'.$addon.'.php';
  }

 /**
  * Chargement des addons LdapSaisie
  *
  * Chargement des LSaddons contenue dans la variable
  * $GLOBALS['LSaddons']['loads']
  *
  * @retval boolean true si le chargement a rÃ©ussi, false sinon.
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

 /**
  * Initialisation de la session LdapSaisie
  *
  * Initialisation d'une LSsession :
  * - Authentification et activation du mÃ©canisme de session de LdapSaisie
  * - ou Chargement des paramÃ¨tres de la session Ã  partir de la variable 
  *   $_SESSION['LSsession'].
  * - ou Destruction de la session en cas de $_GET['LSsession_logout'].
  *
  * @retval boolean True si l'initialisation Ã  rÃ©ussi (utilisateur authentifiÃ©), false sinon.
  */
  function startLSsession() {
      $this -> loadLSaddons();
      session_start();

      // DÃ©connexion
      if (isset($_GET['LSsession_logout'])||isset($_GET['LSsession_recoverPassword'])) {
        session_destroy();
        
        if (is_array($_SESSION['LSsession']['tmp_file'])) {
          $this -> tmp_file = $_SESSION['LSsession']['tmp_file'];
        }
        $this -> deleteTmpFile();
        unset($_SESSION['LSsession']);
      }
      
      // Récupération de mot de passe
      if (isset($_GET['recoveryHash'])) {
        $_POST['LSsession_user'] = 'a determiner plus tard';
      }
      
      if(isset($_SESSION['LSsession'])) {
        // Session existante
        $this -> confDir      = $_SESSION['LSsession']['confDir'];
        $this -> topDn        = $_SESSION['LSsession']['topDn'];
        $this -> dn           = $_SESSION['LSsession']['dn'];
        $this -> rdn          = $_SESSION['LSsession']['rdn'];
        $this -> ldapServerId = $_SESSION['LSsession']['ldapServerId'];
        $this -> tmp_file     = $_SESSION['LSsession']['tmp_file'];
        
        if ( $this -> cacheLSrights() ) {
          $this -> ldapServer = $_SESSION['LSsession']['ldapServer'];
          $this -> LSrights   = $_SESSION['LSsession']['LSrights'];
          $this -> LSaccess   = $_SESSION['LSsession']['LSaccess'];
          if (!$this -> LSldapConnect())
            return;
          $this -> loadLSobjects();
        }
        else {
          $this -> setLdapServer($this -> ldapServerId);
          if (!$this -> LSldapConnect())
            return;
          $this -> loadLSobjects();
          $this -> loadLSrights();
        }
        
        if ( $this -> cacheSudDn() && (!isset($_REQUEST['LSsession_topDn_refresh'])) ) {
          $this -> _subDnLdapServer = $_SESSION['LSsession_subDnLdapServer'];
        }
        
        $this -> loadLSobject($this -> ldapServer['authobject']);
        $this -> LSuserObject = new $this -> ldapServer['authobject']();
        $this -> LSuserObject -> loadData($this -> dn);
        $this -> loadLSaccess();
        $GLOBALS['Smarty'] -> assign('LSsession_username',$this -> LSuserObject -> getDisplayValue());
        
        if ($_POST['LSsession_topDn']) {
          if ($this -> validSubDnLdapServer($_POST['LSsession_topDn'])) {
            $this -> topDn = $_POST['LSsession_topDn'];
            $_SESSION['LSsession']['topDn'] = $_POST['LSsession_topDn'];
          } // end if
        } // end if
        
        return true;
        
      }
      else {
        // Session inexistante
        $recoveryPasswordInfos=array();

        if (isset($_POST['LSsession_user'])) {
          if (isset($_POST['LSsession_ldapserver'])) {
            $this -> setLdapServer($_POST['LSsession_ldapserver']);
          }
          else {
            $this -> setLdapServer(0);
          }
          
          $this -> loadLSobjects();

          // Connexion au serveur LDAP
              if ($this -> LSldapConnect()) {

            // topDn
            if ( $_POST['LSsession_topDn'] != '' ){
              $this -> topDn = $_POST['LSsession_topDn'];
            }
            else {
              $this -> topDn = $this -> ldapServer['ldap_config']['basedn'];
            }
            $_SESSION['LSsession_topDn']=$this -> topDn;

            if ( $this -> loadLSobject($this -> ldapServer['authobject']) ) {
              $authobject = new $this -> ldapServer['authobject']();
              $find=true;
              if (isset($_GET['recoveryHash'])) {
                $filter=$this -> ldapServer['recoverPassword']['recoveryHashAttr']."=".$_GET['recoveryHash'];
                $result = $authobject -> listObjects($filter,$this -> topDn);
                $nbresult=count($result);
                if ($nbresult==1) {
                  $_POST['LSsession_user'] = $result[0] -> getValue('rdn');
                  $find=false;
                }
              }
              if ($find) {
                $result = $authobject -> searchObject($_POST['LSsession_user'],$this -> topDn);
                $nbresult=count($result);
              }
              if ($nbresult==0) {
                // identifiant incorrect
                debug('identifiant incorrect');
                $GLOBALS['LSerror'] -> addErrorCode(1006);
              }
              else if ($nbresult>1) {
                // duplication d'authentitÃ©
                $GLOBALS['LSerror'] -> addErrorCode(1007);
              }
              else {
                if (isset($_GET['LSsession_recoverPassword'])) {
                  debug('Recover : Id trouvé');
                  if ($this -> ldapServer['recoverPassword']) {
                    debug('Récupération active');
                    $user=$result[0];
                    $emailAddress = $user -> getValue($this -> ldapServer['recoverPassword']['mailAttr']);
                    
                    // Header des mails
                    $headers="Content-Type: text/plain; charset=UTF-8; format=flowed";
                    if ($this -> ldapServer['recoverPassword']['recoveryEmailSender']) {
                      $headers.="\nFrom: ".$this -> ldapServer['recoverPassword']['recoveryEmailSender'];
                    }
                    else if($this -> ldapServer['emailSender']) {
                      $headers.="\nFrom: ".$this -> ldapServer['emailSender'];
                    }
                    
                    if (checkEmail($emailAddress)) {
                      debug('Email : '.$emailAddress);
                      $this -> dn = $user -> getDn();
                      // 1ère étape : envoie du recoveryHash
                      if (!isset($_GET['recoveryHash'])) {
                        // Generer un hash
                        $recovery_hash = md5($user -> getValue('rdn') . strval(time()) . strval(rand()));
                        
                        $lostPasswdForm = $user -> getForm('lostPassword');
                        $lostPasswdForm -> setPostData(
                          array(
                            $this -> ldapServer['recoverPassword']['recoveryHashAttr'] => $recovery_hash
                          )
                          ,true
                        );
                        
                        if($lostPasswdForm -> validate()) {
                          if ($user -> updateData('lostPassword')) {
                            // recoveryHash de l'utilisateur mis à jour
                            if ($_SERVER['HTTPS']=='on') {
                              $recovery_url='https://';
                            }
                            else {
                              $recovery_url='http://';
                            }
                            $recovery_url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&recoveryHash='.$recovery_hash;

                            if (
                              mail(
                                $emailAddress,
                                $this -> ldapServer['recoverPassword']['recoveryHashMail']['subject'],
                                getFData($this -> ldapServer['recoverPassword']['recoveryHashMail']['msg'],$recovery_url),
                                $headers
                              )
                            ){
                              // Mail a bien été envoyé
                              $recoveryPasswordInfos['recoveryHashMail']=$emailAddress;
                            }
                            else {
                              // Problème durant l'envoie du mail
                              debug("Problème durant l'envoie du mail");
                              $GLOBALS['LSerror'] -> addErrorCode(1020);
                            }
                          }
                          else {
                            // Erreur durant la mise à jour de l'objet
                            debug("Erreur durant la mise à jour de l'objet");
                            $GLOBALS['LSerror'] -> addErrorCode(1020);
                          }
                        }
                        else {
                          // Erreur durant la validation du formulaire de modification de perte de password
                          debug("Erreur durant la validation du formulaire de modification de perte de password");
                          $GLOBALS['LSerror'] -> addErrorCode(1020);
                        }
                      }
                      // 2nd étape : génération du mot de passe + envoie par mail
                      else {
                        $attr=$user -> attrs[$this -> ldapServer['authobject_pwdattr']];
                        if ($attr instanceof LSattribute) {
                          $mdp = generatePassword($attr -> config['html_options']['chars'],$attr -> config['html_options']['lenght']);
                          debug('Nvx mpd : '.$mdp);
                          $lostPasswdForm = $user -> getForm('lostPassword');
                          $lostPasswdForm -> setPostData(
                            array(
                              $this -> ldapServer['recoverPassword']['recoveryHashAttr'] => array(''),
                              $this -> ldapServer['authobject_pwdattr'] => array($mdp)
                            )
                            ,true
                          );
                          if($lostPasswdForm -> validate()) {
                            if ($user -> updateData('lostPassword')) {
                              if (
                                mail(
                                  $emailAddress,
                                  $this -> ldapServer['recoverPassword']['newPasswordMail']['subject'],
                                  getFData($this -> ldapServer['recoverPassword']['newPasswordMail']['msg'],$mdp),
                                  $headers
                                )
                              ){
                                // Mail a bien été envoyé
                                $recoveryPasswordInfos['newPasswordMail']=$emailAddress;
                              }
                              else {
                                // Problème durant l'envoie du mail
                                debug("Problème durant l'envoie du mail");
                                $GLOBALS['LSerror'] -> addErrorCode(1020);
                              }
                            }
                            else {
                              // Erreur durant la mise à jour de l'objet
                              debug("Erreur durant la mise à jour de l'objet");
                              $GLOBALS['LSerror'] -> addErrorCode(1020);
                            }
                          }
                          else {
                            // Erreur durant la validation du formulaire de modification de perte de password
                            debug("Erreur durant la validation du formulaire de modification de perte de password");
                            $GLOBALS['LSerror'] -> addErrorCode(1020);
                          }
                        }
                        else {
                          // l'attribut password n'existe pas
                          debug("L'attribut password n'existe pas");
                          $GLOBALS['LSerror'] -> addErrorCode(1020);
                        }
                      }
                    }
                    else {
                      $GLOBALS['LSerror'] -> addErrorCode(1019);
                    }
                  }
                  else {
                    $GLOBALS['LSerror'] -> addErrorCode(1018);
                  }
                }
                else {
                  if ( $this -> checkUserPwd($result[0],$_POST['LSsession_pwd']) ) {
                    // Authentification rÃ©ussi
                    $this -> LSuserObject = $result[0];
                    $this -> dn = $result[0]->getValue('dn');
                    $this -> rdn = $_POST['LSsession_user'];
                    $this -> loadLSrights();
                    $this -> loadLSaccess();
                    $GLOBALS['Smarty'] -> assign('LSsession_username',$this -> LSuserObject -> getValue('rdn'));
                    $_SESSION['LSsession']=get_object_vars($this);
                    return true;
                  }
                  else {
                    $GLOBALS['LSerror'] -> addErrorCode(1006);
                    debug('mdp incorrect');
                  }
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
        if ($this -> ldapServerId) {
          $GLOBALS['Smarty'] -> assign('ldapServerId',$this -> ldapServerId);
        }
        $GLOBALS['Smarty'] -> assign('topDn',$this -> topDn);
        if (isset($_GET['LSsession_recoverPassword'])) {
          $this -> displayRecoverPasswordForm($recoveryPasswordInfos);
        }
        else {
          $this -> displayLoginForm();
        }
        return;
      }
  }

 /**
  * DÃ©finition du serveur Ldap de la session
  *
  * DÃ©finition du serveur Ldap de la session Ã  partir de son ID dans 
  * le tableau $GLOBALS['LSconfig']['ldap_servers'].
  *
  * @param[in] integer Index du serveur Ldap
  *
  * @retval boolean True sinon false.
  */
  function setLdapServer($id) {
    if ( isset($GLOBALS['LSconfig']['ldap_servers'][$id]) ) {
      $this -> ldapServerId = $id;
      $this -> ldapServer=$GLOBALS['LSconfig']['ldap_servers'][$id];
      return true;
    }
    else {
      return;
    }
  }

 /**
  * Connexion au serveur Ldap
  *
  * @retval boolean True sinon false.
  */
  function LSldapConnect() {
    if ($this -> ldapServer) {
      include_once($GLOBALS['LSconfig']['NetLDAP2']);
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

 /**
  * Retourne les sous-dns du serveur Ldap courant
  *
  * @retval mixed Tableau des subDn, false si une erreur est survenue.
  */
  function getSubDnLdapServer() {
    if ($this -> cacheSudDn() && isset($this -> _subDnLdapServer[$this -> ldapServerId])) {
      return $this -> _subDnLdapServer[$this -> ldapServerId];
    }
    if ( is_array($this ->ldapServer['subDn']) ) {
      $return=array();
      foreach($this ->ldapServer['subDn'] as $subDn_name => $subDn_config) {
        if ($subDn_name == 'LSobject') {
          if (is_array($subDn_config)) {
            foreach($subDn_config as $LSobject_name => $LSoject_topDn) {
              if ($LSoject_topDn) {
                $topDn = $LSoject_topDn;
              }
              else {
                $topDn = NULL;
              }
              if( $this -> loadLSobject($LSobject_name) ) {
                if ($subdnobject = new $LSobject_name()) {
                  $tbl_return = $subdnobject -> getSelectArray($topDn);
                  if (is_array($tbl_return)) {
                    $return=array_merge($return,$tbl_return);
                  }
                  else {
                    $GLOBALS['LSerror'] -> addErrorCode(1017);
                  }
                }
                else {
                  $GLOBALS['LSerror'] -> addErrorCode(1017);
                }
              }
              else {
                $GLOBALS['LSerror'] -> addErrorCode(1004,$LSobject_name);
              }
            }
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(1017);
          }
        }
        else {
          $return[$subDn_config] = $subDn_name;
        }
      }
      if ($this -> cacheSudDn()) {
        $this -> _subDnLdapServer[$this -> ldapServerId]=$return;
        $_SESSION['LSsession_subDnLdapServer'] = $this -> _subDnLdapServer;
      }
      return $return;
    }
    else {
      return;
    }
  }
  
  /**
   * Retourne la liste de subDn du serveur Ldap utilise
   * trié par la profondeur dans l'arboressence (ordre décroissant)
   * 
   * @return array() Tableau des subDn trié
   */  
  function getSortSubDnLdapServer() {
    if(isset($_SESSION['LSsession']['LSview_subDnLdapServer']) && $this -> cacheSudDn()) {
      return $_SESSION['LSsession']['LSview_subDnLdapServer'];
    }
    $subDnLdapServer = $this  -> getSubDnLdapServer();
    uksort($subDnLdapServer,"compareDn");
    $_SESSION['LSsession']['LSview_subDnLdapServer']=$subDnLdapServer;
    return $subDnLdapServer;
  }

 /**
  * Retourne les options d'une liste dÃ©roulante pour le choix du topDn
  * de connexion au serveur Ldap
  *
  * Liste les subdn ($this ->ldapServer['subDn'])
  *
  * @retval string Les options (<option>) pour la sÃ©lection du topDn.
  */
  function getSubDnLdapServerOptions($selected=NULL) {
    $list = $this -> getSubDnLdapServer();
    if ($list) {
      $display='';
      foreach($list as $dn => $txt) {
        if ($selected && ($selected==$dn)) {
          $selected_txt = ' selected';
        }
        else {
          $selected_txt = '';
        }
        $display.="<option value=\"".$dn."\"$selected_txt>".$txt."</option>\n"; 
      }
      return $display;
    }
    return;
  }

  function validSubDnLdapServer($subDn) {
    $listTopDn = $this -> getSubDnLdapServer();
    if(is_array($listTopDn)) {
      foreach($listTopDn as $dn => $txt) {
        if ($subDn==$dn) {
          return true;
        } // end if
      } // end foreach
    } // end if
    return;
  }

 /**
  * Test un couple LSobject/pwd
  *
  * Test un bind sur le serveur avec le dn de l'objet et le mot de passe fourni.
  *
  * @param[in] LSobject L'object "user" pour l'authentification
  * @param[in] string Le mot de passe Ã  tester
  *
  * @retval boolean True si l'authentification Ã  rÃ©ussi, false sinon.
  */
  function checkUserPwd($object,$pwd) {
    return $GLOBALS['LSldap'] -> checkBind($object -> getValue('dn'),$pwd);
  }

 /**
  * Affiche le formulaire de login
  *
  * DÃ©fini les informations pour le template Smarty du formulaire de login.
  *
  * @retval void
  */
  function displayLoginForm() {
    $GLOBALS['Smarty'] -> assign('pagetitle',_('Connexion'));
    if (isset($_GET['LSsession_logout'])) {
      $GLOBALS['Smarty'] -> assign('loginform_action','index.php');
    }
    else {
      $GLOBALS['Smarty'] -> assign('loginform_action',$_SERVER['REQUEST_URI']);
    }
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

    $GLOBALS['Smarty'] -> assign('loginform_label_level',_('Niveau'));
    $GLOBALS['Smarty'] -> assign('loginform_label_user',_('Identifiant'));
    $GLOBALS['Smarty'] -> assign('loginform_label_pwd',_('Mot de passe'));
    $GLOBALS['Smarty'] -> assign('loginform_label_submit',_('Connexion'));
    $GLOBALS['Smarty'] -> assign('loginform_label_lostpassword',_('Mot de passe oublié ?'));
    
    $this -> setTemplate('login.tpl');
    $this -> addJSscript('LSsession_login.js');
  }

 /**
  * Affiche le formulaire de récupération de mot de passe
  *
  * Défini les informations pour le template Smarty du formulaire de 
  * récupération de mot de passe
  * 
  * @param[in] $infos array() Information sur le status du processus de 
  *                           recouvrement de mot de passe
  *
  * @retval void
  */
  function displayRecoverPasswordForm($recoveryPasswordInfos) {
    $GLOBALS['Smarty'] -> assign('pagetitle',_('Récupération de votre mot de passe'));
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_action','index.php?LSsession_recoverPassword');
    
    if (count($GLOBALS['LSconfig']['ldap_servers'])==1) {
      $GLOBALS['Smarty'] -> assign('recoverpasswordform_ldapserver_style','style="display: none"');
    }
    
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_label_ldapserver',_('Serveur LDAP'));
    $ldapservers_name=array();
    $ldapservers_index=array();
    foreach($GLOBALS['LSconfig']['ldap_servers'] as $id => $infos) {
      $ldapservers_index[]=$id;
      $ldapservers_name[]=$infos['name'];
    }
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_ldapservers_name',$ldapservers_name);
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_ldapservers_index',$ldapservers_index);

    $GLOBALS['Smarty'] -> assign('recoverpasswordform_label_user',_('Identifiant'));
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_label_submit',_('Valider'));
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_label_back',_('Retour'));
    
    $recoverpassword_msg = _('Veuillez saisir votre identifiant pour poursuivre le processus de récupération de votre mot de passe');
    
    if (isset($recoveryPasswordInfos['recoveryHashMail'])) {
      $recoverpassword_msg = getFData(
        _("Un mail vient de vous être envoyé à l'adresse %{mail}. " .
        "Merci de suivre les indications contenus dans ce mail."),
        $recoveryPasswordInfos['recoveryHashMail']
      );
    }
    
    if (isset($recoveryPasswordInfos['newPasswordMail'])) {
      $recoverpassword_msg = getFData(
        _("Votre nouveau mot de passe vient de vous être envoyé à l'adresse %{mail}. "),
        $recoveryPasswordInfos['newPasswordMail']
      );
    }
    
    $GLOBALS['Smarty'] -> assign('recoverpassword_msg',$recoverpassword_msg);
    
    $this -> setTemplate('recoverpassword.tpl');
    $this -> addJSscript('LSsession_recoverpassword.js');
  }

 /**
  * DÃ©fini le template Smarty Ã  utiliser
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

 /**
  * Ajoute un script JS au chargement de la page
  *
  * Remarque : les scripts doivents Ãªtre dans le dossier LS_JS_DIR.
  *
  * @param[in] $script Le nom du fichier de script Ã  charger.
  *
  * @retval void
  */
  function addJSscript($script) {
    if (in_array($script, $this -> JSscripts))
      return;
    $this -> JSscripts[]=$script;
  }

 /**
  * Ajoute une feuille de style au chargement de la page
  *
  * Remarque : les scripts doivents Ãªtre dans le dossiers templates/css/.
  *
  * @param[in] $script Le nom du fichier css Ã  charger.
  *
  * @retval void
  */
  function addCssFile($file) {
    $this -> CssFiles[]=$file;
  }

 /**
  * Affiche le template Smarty
  *
  * Charge les dÃ©pendances et affiche le template Smarty
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
    
    if ($GLOBALS['LSdebug']['active']) {
      $JSscript_txt.="<script type='text/javascript'>LSdebug_active = 1;</script>\n";
    }
    else {
      $JSscript_txt.="<script type='text/javascript'>LSdebug_active = 0;</script>\n";
    }
    
    $GLOBALS['Smarty'] -> assign('LSsession_js',$JSscript_txt);

    // Css
    $Css_txt="<link rel='stylesheet' type='text/css' href='templates/css/LSdefault.css' />\n";
    $Css_txt="<link rel='stylesheet' type='text/css' href='templates/css/LSdefault.css' />\n";
    foreach ($this -> CssFiles as $file) {
      $Css_txt.="<link rel='stylesheet' type='text/css' href='templates/css/$file' />\n";
    }
    $GLOBALS['Smarty'] -> assign('LSsession_css',$Css_txt);

    $GLOBALS['Smarty'] -> assign('LSaccess',$this -> LSaccess);
    
    // Niveau
    $listTopDn = $this -> getSubDnLdapServer();
    if (is_array($listTopDn)) {
      $GLOBALS['Smarty'] -> assign('label_level',$this -> getLevelLabel());
      $GLOBALS['Smarty'] -> assign('_refresh',_('Rafraîchir'));
      $LSsession_topDn_index = array();
      $LSsession_topDn_name = array();
      foreach($listTopDn as $index => $name) {
        $LSsession_topDn_index[]  = $index;
        $LSsession_topDn_name[]   = $name;
      }
      $GLOBALS['Smarty'] -> assign('LSsession_subDn_indexes',$LSsession_topDn_index);
      $GLOBALS['Smarty'] -> assign('LSsession_subDn_names',$LSsession_topDn_name);
      $GLOBALS['Smarty'] -> assign('LSsession_subDn',$this -> topDn);
      $GLOBALS['Smarty'] -> assign('LSsession_subDnName',$this -> getSubDnName());
    }
    
    if ($this -> ajaxDisplay) {
      $GLOBALS['Smarty'] -> assign('error_txt',json_encode($GLOBALS['LSerror']->getErrors()));
      $GLOBALS['Smarty'] -> assign('debug_txt',json_encode(debug_print(true)));
    }
    else {
      $GLOBALS['LSerror'] -> display();
      debug_print();
    }
    if (!$this -> template)
      $this -> setTemplate('empty.tpl');
    $GLOBALS['Smarty'] -> display($this -> template);
  }
  
  /**
   * Charge les droits LS de l'utilisateur
   * 
   * @retval boolean True si le chargement Ã  rÃ©ussi, false sinon.
   **/
  function loadLSrights() {
    if (is_array($this -> ldapServer['LSadmins'])) {
      foreach ($this -> ldapServer['LSadmins'] as $topDn => $adminsInfos) {
        if (is_array($adminsInfos)) {
          foreach($adminsInfos as $dn => $conf) {
            if ((isset($conf['attr'])) && (isset($conf['LSobject']))) {
              if( $this -> loadLSobject($conf['LSobject']) ) {
                if ($object = new $conf['LSobject']()) {
                  if ($object -> loadData($dn)) {
                    $listDns=$object -> getValue($conf['attr']);
                    if (is_array($listDns)) {
                      if (in_array($this -> dn,$listDns)) {
                        $this -> LSrights['topDn_admin'][] = $topDn;
                      }
                    }
                  }
                  else {
                    debug('Impossible de chargÃ© le dn : '.$dn);
                  }
                }
                else {
                  debug('Impossible de crÃ©er l\'objet de type : '.$conf['LSobject']);
                }
              }
              else {
                $GLOBALS['LSerror'] -> addErrorCode(1004,$conf['LSobject']);
              }
            }
            else {
              if ($this -> dn == $dn) {
                $this -> LSrights['topDn_admin'][] = $topDn;
              }
            }
          }
        }
        else {
          if ( $this -> dn == $adminsInfos ) {
            $this -> LSrights['topDn_admin'][] = $topDn;
          }
        }
      }
      return true;
    }
    else {
      return;
    }
  }
  
  /**
   * Charge les droits d'accÃ¨s de l'utilisateur pour construire le menu de l'interface
   *
   * @retval void
   */
  function loadLSaccess() {
    if ($this -> canAccess($this -> LSuserObject -> getType(),$this -> dn)) {
      $LSaccess = array(
        'SELF' => array(
          'label' => _('Mon compte'),
          'DNs' => $this -> dn
        )
      );
    }
    else {
      $LSaccess = array();
    }
    foreach ($GLOBALS['LSobjects'] as $objecttype => $objectconf) {
      if ($this -> canAccess($objecttype) ) {
        $LSaccess[$objecttype] = array (
          'label' => $objectconf['label'],
          'Dns' => 'All'
        );
      }
    }
    $this -> LSaccess = $LSaccess;
  }
  
  /**
   * Dit si l'utilisateur est admin de le DN spÃ©cifiÃ©
   *
   * @param[in] string DN de l'objet
   * 
   * @retval boolean True si l'utilisateur est admin sur l'objet, false sinon.
   */
  function isAdmin($dn) {
    foreach($this -> LSrights['topDn_admin'] as $topDn_admin) {
      if($dn == $topDn_admin) {
        return true;
      }
      else if ( isCompatibleDNs($dn,$topDn_admin) ) {
        return true;
      }
    }
    return;
  }
  
  /**
   * Retourne qui est l'utilisateur par rapport Ã  l'object
   *
   * @param[in] string Le DN de l'objet
   * 
   * @retval string 'admin'/'self'/'user' pour Admin , l'utilisateur lui mÃªme ou un simple utilisateur
   */
  function whoami($dn) {
    if ($this -> isAdmin($dn)) {
      return 'admin';
    }
    
    if ($this -> dn == $dn) {
      return 'self';
    }
    
    return 'user';
  }
  
  /**
   * Retourne le droit de l'utilisateur Ã  accÃ¨der Ã  un objet
   * 
   * @param[in] string $LSobject Le type de l'objet
   * @param[in] string $dn Le DN de l'objet (le container_dn du type de l'objet par dÃ©faut)
   * @param[in] string $right Le type de droit d'accÃ¨s Ã  tester ('r'/'w')
   * @param[in] string $attr Le nom de l'attribut auquel on test l'accÃ¨s
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */
  function canAccess($LSobject,$dn=NULL,$right=NULL,$attr=NULL) {
    if (!$this -> loadLSobject($LSobject))
      return;
    if ($dn) {
      $whoami = $this -> whoami($dn);
    }
    else {
      $objectdn=$GLOBALS['LSobjects'][$LSobject]['container_dn'].','.$this -> topDn;
      $whoami = $this -> whoami($objectdn);
    }
    
    // Pour un attribut particulier
    if ($attr) {
      if ($attr=='rdn') {
        $attr=$GLOBALS['LSobjects'][$LSobject]['rdn'];
      }
      if (!isset($GLOBALS['LSobjects'][$LSobject]['attrs'][$attr])) {
        return;
      }
      
      if (($right=='r')||($right=='w')) {
        if ($GLOBALS['LSobjects'][$LSobject]['attrs'][$attr]['rights'][$whoami]==$right) {
          return true;
        }
        return;
      }
      else {
        if ( ($GLOBALS['LSobjects'][$LSobject]['attrs'][$attr]['rights'][$whoami]=='r') || ($GLOBALS['LSobjects'][$LSobject]['attrs'][$attr]['rights'][$whoami]=='w') ) {
          return true;
        }
        return;
      }
    }
    
    // Pour un attribut quelconque
    if (is_array($GLOBALS['LSobjects'][$LSobject]['attrs'])) {
      if (($right=='r')||($right=='w')) {
        foreach ($GLOBALS['LSobjects'][$LSobject]['attrs'] as $attr_name => $attr_config) {
          if ($attr_config['rights'][$whoami]==$right) {
            return true;
          }
        }
      }
      else {
        foreach ($GLOBALS['LSobjects'][$LSobject]['attrs'] as $attr_name => $attr_config) {
          if ( ($attr_config['rights'][$whoami]=='r') || ($attr_config['rights'][$whoami]=='w') ) {
            return true;
          }
        }
      }
    }
    return;
  }
  
  /**
   * Retourne le droit de l'utilisateur Ã  editer Ã  un objet
   * 
   * @param[in] string $LSobject Le type de l'objet
   * @param[in] string $dn Le DN de l'objet (le container_dn du type de l'objet par dÃ©faut)
   * @param[in] string $attr Le nom de l'attribut auquel on test l'accÃ¨s
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */
  function canEdit($LSobject,$dn=NULL,$attr=NULL) {
    return $this -> canAccess($LSobject,$dn,'w',$attr);
  }

  /**
   * Retourne le droit de l'utilisateur Ã  supprimer un objet
   * 
   * @param[in] string $LSobject Le type de l'objet
   * @param[in] string $dn Le DN de l'objet (le container_dn du type de l'objet par dÃ©faut)
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */  
  function canRemove($LSobject,$dn) {
    return $this -> canAccess($LSobject,$dn,'w','rdn');
  }
  
  /**
   * Retourne le droit de l'utilisateur Ã  crÃ©er un objet
   * 
   * @param[in] string $LSobject Le type de l'objet
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */    
  function canCreate($LSobject) {
    return $this -> canAccess($LSobject,NULL,'w','rdn');
  }
  
  /**
   * Retourne le droit de l'utilisateur Ã  gÃ©rer la relation d'objet
   * 
   * @param[in] string $dn Le DN de l'objet (le container_dn du type de l'objet par dÃ©faut)
   * @param[in] string $relationName Le nom de la relation avec l'objet
   * @param[in] string $right Le type de droit a vÃ©rifier ('r' ou 'w')
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */
  function relationCanAccess($dn,$relationName,$right=NULL) {
    $LSobject=$this -> LSuserObject -> getType();
    if (!isset($GLOBALS['LSobjects'][$LSobject]['relations'][$relationName]))
      return;
    $whoami = $this -> whoami($dn);

    if (($right=='w') || ($right=='r')) {
      if ($GLOBALS['LSobjects'][$LSobject]['relations'][$relationName]['rights'][$whoami] == $right) {
        return true;
      }
    }
    else {
      if (($GLOBALS['LSobjects'][$LSobject]['relations'][$relationName]['rights'][$whoami] == 'w') || ($GLOBALS['LSobjects'][$LSobject]['relations'][$relationName]['rights'][$whoami] == 'r')) {
        return true;
      }
    }
    return;
  }

  /**
   * Retourne le droit de l'utilisateur Ã  modifier la relation d'objet
   * 
   * @param[in] string $dn Le DN de l'objet (le container_dn du type de l'objet par dÃ©faut)
   * @param[in] string $relationName Le nom de la relation avec l'objet
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */  
  function relationCanEdit($dn,$relationName) {
    return $this -> relationCanAccess($dn,$relationName,'w');
  }

  /**
   * Ajoute un fichier temporaire
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval void
   **/
  function addTmpFile($value,$filePath) {
    $hash = mhash(MHASH_MD5,$value);
    $this -> tmp_file[$filePath] = $hash;
    $_SESSION['LSsession']['tmp_file'][$filePath] = $hash;
  }
  
  /**
   * Retourne le chemin du fichier temporaire si l'existe
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @param[in] $value La valeur du fichier
   * 
   * @retval mixed 
   **/
  function tmpFileExist($value) {
    $hash = mhash(MHASH_MD5,$value);
    foreach($this -> tmp_file as $filePath => $contentHash) {
      if ($hash == $contentHash) {
        return $filePath;
      }
    }
    return false;
  }
  
  /**
   * Retourne le chemin du fichier temporaire
   * 
   * Retourne le chemin du fichier temporaire qu'il crÃ©era Ã  partir de la valeur
   * s'il n'existe pas dÃ©jÃ .
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @param[in] $value La valeur du fichier
   * 
   * @retval mixed 
   **/
  function getTmpFile($value) {
    $exist = $this -> tmpFileExist($value);
    if (!$exist) {
      $img_path = LS_TMP_DIR .rand().'.tmp';
      $fp = fopen($img_path, "w");
      fwrite($fp, $value);
      fclose($fp);
      $this -> addTmpFile($value,$img_path);
      return $img_path;
    }
    else {
      return $exist;
    }
  }
  
  /**
   * Supprime les fichiers temporaires
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval void
   **/
  function deleteTmpFile($filePath=NULL) {
    if ($filePath) {
        @unlink($filePath);
        unset($this -> tmp_file[$filePath]);
        unset($_SESSION['LSsession']['tmp_file'][$filePath]);
    }
    else {
      foreach($this -> tmp_file as $file => $content) {
        @unlink($file);
      }
      $this -> tmp_file = array();
      $_SESSION['LSsession']['tmp_file'] = array();
    }
  }

  /**
   * Retourne true si le cache des droits est activé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si le cache des droits est activé, false sinon.
   */
  function cacheLSrights() {
    return ( ($GLOBALS['LSconfig']['cacheLSrights']) || ($this -> ldapServer['cacheLSrights']) );
  }

  /**
   * Retourne true si le cache des subDn est activé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si le cache des subDn est activé, false sinon.
   */
  function cacheSudDn() {
    return (($GLOBALS['LSconfig']['cacheSubDn']) || ($this -> ldapServer['cacheSubDn']));
  }
  
  /**
   * Retourne true si le cache des recherches est activé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si le cache des recherches est activé, false sinon.
   */
  function cacheSearch() {
    return (($GLOBALS['LSconfig']['cacheSearch']) || ($this -> ldapServer['cacheSearch']));
  }
  
  /**
   * Retourne le label des niveaux pour le serveur ldap courant
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval string Le label des niveaux pour le serveur ldap dourant
   */
  function getLevelLabel() {
    return ($this -> ldapServer['levelLabel']!='')?$this -> ldapServer['levelLabel']:_('Niveau');
  }
  
  /**
   * Retourne le nom du subDn
   * 
   * @param[in] $subDn string subDn
   * 
   * @return string Le nom du subDn ou '' sinon
   */
  function getSubDnName($subDn=false) {
    if (!$subDn) {
      $subDn = $this -> topDn;
    }
    if ($this -> getSubDnLdapServer()) {
      if (isset($this -> _subDnLdapServer[$this -> ldapServerId][$subDn])) {
        return $this -> _subDnLdapServer[$this -> ldapServerId][$subDn];
      }
    }
    return '';
  }

}

?>
