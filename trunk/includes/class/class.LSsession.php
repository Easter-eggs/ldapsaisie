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
require_once 'includes/functions.php';

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
  var $_JSconfigParams = array();
  var $CssFiles = array();
  var $template = NULL;
  var $LSprofiles = array();
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
  * Include un fichier PHP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval true si tout c'est bien passé, false sinon
  */
  function includeFile($file) {
    if (!file_exists($file)) {
      return;
    }
    if ($GLOBALS['LSdebug']['active']) {
      return include_once($file);
    }
    else {
      return @include_once($file);
    }
    return;
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
      if ( self::includeFile($GLOBALS['LSconfig']['Smarty']) ) {
        $GLOBALS['Smarty'] = new Smarty();
        $GLOBALS['Smarty'] -> template_dir = LS_TEMPLATES_DIR;
        $GLOBALS['Smarty'] -> compile_dir = LS_TMP_DIR;
        
        $GLOBALS['Smarty'] -> assign('LS_CSS_DIR',LS_CSS_DIR);
        $GLOBALS['Smarty'] -> assign('LS_IMAGES_DIR',LS_IMAGES_DIR);
        
        $this -> addJSconfigParam('LS_IMAGES_DIR',LS_IMAGES_DIR);
        return true;
      }
      else {
        die($GLOBALS['LSerror_code']['LSsession_08']['msg']);
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
    if(!$this -> loadLSclass('LSerror')) {
      return;
    }
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
    return self::includeFile(LS_CLASS_DIR .'class.'.$type.$class.'.php');
  }

 /**
  * Chargement d'un object LdapSaisie
  *
  * @param[in] $object Nom de l'objet Ã  charger
  *
  * @retval boolean true si le chargement a rÃ©ussi, false sinon.
  */
  function loadLSobject($object) {
    $error = 0;
    $this -> loadLSclass('LSldapObject');
    if (!$this -> loadLSclass($object,'LSobjects')) {
      $error = 1;
    }
    if (!self::includeFile( LS_OBJECTS_DIR . 'config.LSobjects.'.$object.'.php' )) {
      $error = 1;
    }
    if ($error) {
      $GLOBALS['LSerror'] -> addErrorCode('LSsession_04',$object);
      return;
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
    if(self::includeFile(LS_ADDONS_DIR .'LSaddons.'.$addon.'.php')) {
      self::includeFile(LS_CONF_DIR."LSaddons/config.LSaddons.".$addon.".php");
      if (!call_user_func('LSaddon_'. $addon .'_support')) {
        $GLOBALS['LSerror'] -> addErrorCode('LSsession_02',$addon);
        return;
      }
      return true;
    }
    return;
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
      $GLOBALS['LSerror'] -> addErrorCode('LSsession_01',"LSaddons['loads']");
      return;
    }

    foreach ($GLOBALS['LSaddons']['loads'] as $addon) {
      $this -> loadLSaddon($addon);
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
        
        if ( $this -> cacheLSprofiles() && !isset($_REQUEST['LSsession_refresh']) ) {
          $this -> ldapServer = $_SESSION['LSsession']['ldapServer'];
          $this -> LSprofiles   = $_SESSION['LSsession']['LSprofiles'];
          $this -> LSaccess   = $_SESSION['LSsession']['LSaccess'];
          if (!$this -> LSldapConnect())
            return;
        }
        else {
          $this -> setLdapServer($this -> ldapServerId);
          if (!$this -> LSldapConnect())
            return;
          $this -> loadLSprofiles();
        }
        
        if ( $this -> cacheSudDn() && (!isset($_REQUEST['LSsession_refresh'])) ) {
          $this -> _subDnLdapServer = $_SESSION['LSsession_subDnLdapServer'];
        }
        
        if (!$this -> loadLSobject($this -> ldapServer['authObjectType'])) {
          return;
        }
        
        $this -> LSuserObject = new $this -> ldapServer['authObjectType']();
        $this -> LSuserObject -> loadData($this -> dn);
        
        if ( !$this -> cacheLSprofiles() || isset($_REQUEST['LSsession_refresh']) ) {
          $this -> loadLSaccess();
        }
        
        $GLOBALS['Smarty'] -> assign('LSsession_username',$this -> LSuserObject -> getDisplayName());
        
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

            if ( $this -> loadLSobject($this -> ldapServer['authObjectType']) ) {
              $authobject = new $this -> ldapServer['authObjectType']();
              $find=true;
              if (isset($_GET['recoveryHash'])) {
                $filter=$this -> ldapServer['recoverPassword']['recoveryHashAttr']."=".$_GET['recoveryHash'];
                $result = $authobject -> listObjects($filter,$this -> topDn);
                $nbresult=count($result);
                if ($nbresult==1) {
                  $rdn = $result[0] -> getValue('rdn');
                  $rdn = $rdn[0];
                  $_POST['LSsession_user'] = $rdn;
                  $find=false;
                }
              }
              if ($find) {
                $result = $authobject -> searchObject($_POST['LSsession_user'],$this -> topDn);
                $nbresult=count($result);
              }
              if ($nbresult==0) {
                // identifiant incorrect
                LSdebug('identifiant incorrect');
                $GLOBALS['LSerror'] -> addErrorCode('LSsession_06');
              }
              else if ($nbresult>1) {
                // duplication d'authentitÃ©
                $GLOBALS['LSerror'] -> addErrorCode('LSsession_07');
              }
              else {
                if (isset($_GET['LSsession_recoverPassword'])) {
                  LSdebug('Recover : Id trouvé');
                  if ($this -> ldapServer['recoverPassword']) {
                    if ($this -> loadLSaddon('mail')) {
                      LSdebug('Récupération active');
                      $user=$result[0];
                      $emailAddress = $user -> getValue($this -> ldapServer['recoverPassword']['mailAttr']);
                      $emailAddress = $emailAddress[0];
                      
                      // Header des mails
                      $sendParams=array();
                      if ($this -> ldapServer['recoverPassword']['recoveryEmailSender']) {
                        $sendParams['From']=$this -> ldapServer['recoverPassword']['recoveryEmailSender'];
                      }
                      
                      if (checkEmail($emailAddress)) {
                        LSdebug('Email : '.$emailAddress);
                        $this -> dn = $user -> getDn();
                        // 1ère étape : envoie du recoveryHash
                        if (!isset($_GET['recoveryHash'])) {
                          // Generer un hash
                          $rdn=$user -> getValue('rdn');
                          $rdn = $rdn[0];
                          $recovery_hash = md5($rdn . strval(time()) . strval(rand()));
                          
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
                                sendMail(
                                  $emailAddress,
                                  $this -> ldapServer['recoverPassword']['recoveryHashMail']['subject'],
                                  getFData($this -> ldapServer['recoverPassword']['recoveryHashMail']['msg'],$recovery_url),
                                  $sendParams
                                )
                              ){
                                // Mail a bien été envoyé
                                $recoveryPasswordInfos['recoveryHashMail']=$emailAddress;
                              }
                              else {
                                // Problème durant l'envoie du mail
                                LSdebug("Problème durant l'envoie du mail");
                                $GLOBALS['LSerror'] -> addErrorCode('LSsession_20',7);
                              }
                            }
                            else {
                              // Erreur durant la mise à jour de l'objet
                              LSdebug("Erreur durant la mise à jour de l'objet");
                              $GLOBALS['LSerror'] -> addErrorCode('LSsession_20',6);
                            }
                          }
                          else {
                            // Erreur durant la validation du formulaire de modification de perte de password
                            LSdebug("Erreur durant la validation du formulaire de modification de perte de password");
                            $GLOBALS['LSerror'] -> addErrorCode('LSsession_20',5);
                          }
                        }
                        // 2nd étape : génération du mot de passe + envoie par mail
                        else {
                          $attr=$user -> attrs[$this -> ldapServer['authObjectTypeAttrPwd']];
                          if ($attr instanceof LSattribute) {
                            $mdp = generatePassword($attr -> config['html_options']['chars'],$attr -> config['html_options']['lenght']);
                            LSdebug('Nvx mpd : '.$mdp);
                            $lostPasswdForm = $user -> getForm('lostPassword');
                            $lostPasswdForm -> setPostData(
                              array(
                                $this -> ldapServer['recoverPassword']['recoveryHashAttr'] => array(''),
                                $this -> ldapServer['authObjectTypeAttrPwd'] => array($mdp)
                              )
                              ,true
                            );
                            if($lostPasswdForm -> validate()) {
                              if ($user -> updateData('lostPassword')) {
                                if (
                                  sendMail(
                                    $emailAddress,
                                    $this -> ldapServer['recoverPassword']['newPasswordMail']['subject'],
                                    getFData($this -> ldapServer['recoverPassword']['newPasswordMail']['msg'],$mdp),
                                    $sendParams
                                  )
                                ){
                                  // Mail a bien été envoyé
                                  $recoveryPasswordInfos['newPasswordMail']=$emailAddress;
                                }
                                else {
                                  // Problème durant l'envoie du mail
                                  LSdebug("Problème durant l'envoie du mail");
                                  $GLOBALS['LSerror'] -> addErrorCode('LSsession_20',4);
                                }
                              }
                              else {
                                // Erreur durant la mise à jour de l'objet
                                LSdebug("Erreur durant la mise à jour de l'objet");
                                $GLOBALS['LSerror'] -> addErrorCode('LSsession_20',3);
                              }
                            }
                            else {
                              // Erreur durant la validation du formulaire de modification de perte de password
                              LSdebug("Erreur durant la validation du formulaire de modification de perte de password");
                              $GLOBALS['LSerror'] -> addErrorCode('LSsession_20',2);
                            }
                          }
                          else {
                            // l'attribut password n'existe pas
                            LSdebug("L'attribut password n'existe pas");
                            $GLOBALS['LSerror'] -> addErrorCode('LSsession_20',1);
                          }
                        }
                      }
                      else {
                        $GLOBALS['LSerror'] -> addErrorCode('LSsession_19');
                      }
                    }
                  }
                  else {
                    $GLOBALS['LSerror'] -> addErrorCode('LSsession_18');
                  }
                }
                else {
                  if ( $this -> checkUserPwd($result[0],$_POST['LSsession_pwd']) ) {
                    // Authentification rÃ©ussi
                    $this -> LSuserObject = $result[0];
                    $this -> dn = $result[0]->getValue('dn');
                    $this -> rdn = $_POST['LSsession_user'];
                    $this -> loadLSprofiles();
                    $this -> loadLSaccess();
                    $GLOBALS['Smarty'] -> assign('LSsession_username',$this -> LSuserObject -> getDisplayName());
                    $_SESSION['LSsession']=get_object_vars($this);
                    return true;
                  }
                  else {
                    $GLOBALS['LSerror'] -> addErrorCode('LSsession_06');
                    LSdebug('mdp incorrect');
                  }
                }
              }
            }
            else {
              $GLOBALS['LSerror'] -> addErrorCode('LSsession_10');
            }
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode('LSsession_09');
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
  * Modifie l'utilisateur connecté à la volé
  * 
  * @param[in] $object Mixed  L'objet Ldap du nouvel utilisateur
  *                           le type doit correspondre à
  *                           $this -> ldapServer['authObjectType']
  * 
  * @retval boolean True en cas de succès, false sinon
  */
 function changeAuthUser($object) {
  if ($object instanceof $this -> ldapServer['authObjectType']) {
    $this -> dn = $object -> getDn();
    $rdn = $object -> getValue('rdn');
    if(is_array($rdn)) {
      $rdn = $rdn[0];
    }
    $this -> rdn = $rdn;
    $this -> LSuserObject = $object;
    
    if($this -> loadLSprofiles()) {
      $this -> loadLSaccess();
      $_SESSION['LSsession']=get_object_vars($this);
      return true;
    }
  }
  return;
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
      self::includeFile($GLOBALS['LSconfig']['NetLDAP2']);
      if (!$this -> loadLSclass('LSldap')) {
        return;
      }
      $GLOBALS['LSldap'] = @new LSldap($this -> ldapServer['ldap_config']);
      if ($GLOBALS['LSldap'] -> isConnected()) {
        return true;
      }
      else {
        return;
      }
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode('LSsession_03');
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
    if (!isset($this ->ldapServer['subDn'])) {
      return;
    }
    if ( !is_array($this ->ldapServer['subDn']) ) {
      return;
    }
    $return=array();
    foreach($this ->ldapServer['subDn'] as $subDn_name => $subDn_config) {
      if ($subDn_name == 'LSobject') {
        if (is_array($subDn_config)) {
          foreach($subDn_config as $LSobject_name => $LSoject_config) {
            if ($LSoject_config['basedn']) {
              $basedn = $LSoject_config['basedn'];
            }
            else {
              $basedn = NULL;
            }
            if ($LSoject_config['displayName']) {
              $displayName = $LSoject_config['displayName'];
            }
            else {
              $displayName = NULL;
            }
            if( $this -> loadLSobject($LSobject_name) ) {
              if ($subdnobject = new $LSobject_name()) {
                $tbl_return = $subdnobject -> getSelectArray(NULL,$basedn,$displayName);
                if (is_array($tbl_return)) {
                  $return=array_merge($return,$tbl_return);
                }
                else {
                  $GLOBALS['LSerror'] -> addErrorCode('LSsession_17',3);
                }
              }
              else {
                $GLOBALS['LSerror'] -> addErrorCode('LSsession_17',2);
              }
            }
          }
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode('LSsession_17',1);
        }
      }
      else {
        if ((isCompatibleDNs($subDn_config['dn'],$this -> ldapServer['ldap_config']['basedn']))&&($subDn_config['dn']!="")) {
          $return[$subDn_config['dn']] = $subDn_name;
        }
      }
    }
    if ($this -> cacheSudDn()) {
      $this -> _subDnLdapServer[$this -> ldapServerId]=$return;
      $_SESSION['LSsession_subDnLdapServer'] = $this -> _subDnLdapServer;
    }
    return $return;
  }
  
  /**
   * Retourne la liste de subDn du serveur Ldap utilise
   * trié par la profondeur dans l'arboressence (ordre décroissant)
   * 
   * @return array() Tableau des subDn trié
   */  
  function getSortSubDnLdapServer() {
    $subDnLdapServer = $this  -> getSubDnLdapServer();
    if (!$subDnLdapServer) {
      return array();
    }
    uksort($subDnLdapServer,"compareDn");
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
      asort($list);
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
    $GLOBALS['Smarty'] -> assign('loginform_label_recoverPassword',_('Mot de passe oublié ?'));
    
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
    $this -> addJSscript('LSsession_recoverPassword.js');
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
  function addJSscript($file,$path=NULL) {
    $script=array(
      'file' => $file,
      'path' => $path
    );
    $this -> JSscripts[$path.$file]=$script;
  }

 /**
  * Ajouter un paramètre de configuration Javascript
  * 
  * @param[in] $name string Nom de la variable de configuration
  * @param[in] $val mixed Valeur de la variable de configuration
  *
  * @retval void
  */
  function addJSconfigParam($name,$val) {
    $this -> _JSconfigParams[$name]=$val;
  }

 /**
  * Ajoute une feuille de style au chargement de la page
  *
  * Remarque : les scripts doivents Ãªtre dans le dossier LS_CSS_DIR.
  *
  * @param[in] $script Le nom du fichier css Ã  charger.
  *
  * @retval void
  */
  function addCssFile($file,$path=NULL) {
    $cssFile=array(
      'file' => $file,
      'path' => $path
    );
    $this -> CssFiles[$path.$file]=$cssFile;
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
      if (!$script['path']) {
        $script['path']=LS_JS_DIR;
      }
      else {
        $script['path'].='/';
      }
      $JSscript_txt.="<script src='".$script['path'].$script['file']."' type='text/javascript'></script>\n";
    }

    $GLOBALS['Smarty'] -> assign('LSjsConfig',json_encode($this -> _JSconfigParams));
    
    if ($GLOBALS['LSdebug']['active']) {
      $JSscript_txt.="<script type='text/javascript'>LSdebug_active = 1;</script>\n";
    }
    else {
      $JSscript_txt.="<script type='text/javascript'>LSdebug_active = 0;</script>\n";
    }
    
    $GLOBALS['Smarty'] -> assign('LSsession_js',$JSscript_txt);

    // Css
    $this -> addCssFile("LSdefault.css");
    $Css_txt='';
    foreach ($this -> CssFiles as $file) {
      if (!$file['path']) {
        $file['path']=LS_CSS_DIR.'/';
      }
      $Css_txt.="<link rel='stylesheet' type='text/css' href='".$file['path'].$file['file']."' />\n";
    }
    $GLOBALS['Smarty'] -> assign('LSsession_css',$Css_txt);
  
    if (isset($this -> LSaccess[$this -> topDn])) {
      $GLOBALS['Smarty'] -> assign('LSaccess',$this -> LSaccess[$this -> topDn]);
    }
    
    // Niveau
    $listTopDn = $this -> getSubDnLdapServer();
    if (is_array($listTopDn)) {
      asort($listTopDn);
      $GLOBALS['Smarty'] -> assign('label_level',$this -> getSubDnLabel());
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

    // Infos
    if((!empty($_SESSION['LSsession_infos']))&&(is_array($_SESSION['LSsession_infos']))) {
      $txt_infos="<ul>\n";
      foreach($_SESSION['LSsession_infos'] as $info) {
        $txt_infos.="<li>$info</li>\n";
      }
      $txt_infos.="</ul>\n";
      $GLOBALS['Smarty'] -> assign('LSinfos',$txt_infos);
      $_SESSION['LSsession_infos']=array();
    }
    
    if ($this -> ajaxDisplay) {
      $GLOBALS['Smarty'] -> assign('LSerror_txt',$GLOBALS['LSerror']->getErrors());
      $GLOBALS['Smarty'] -> assign('LSdebug_txt',LSdebug_print(true));
    }
    else {
      $GLOBALS['LSerror'] -> display();
      LSdebug_print();
    }
    if (!$this -> template)
      $this -> setTemplate('empty.tpl');
    $GLOBALS['Smarty'] -> display($this -> template);
  }
  
 /**
  * Affiche un retour Ajax
  *
  * @retval void
  */
  function displayAjaxReturn($data=array()) {
    if (isset($data['LSredirect']) && (!LSdebugDefined()) ) {
      echo json_encode($data);
      return;
    }
    
    $data['LSjsConfig'] = $this -> _JSconfigParams;
    
    // Infos
    if((!empty($_SESSION['LSsession_infos']))&&(is_array($_SESSION['LSsession_infos']))) {
      $txt_infos="<ul>\n";
      foreach($_SESSION['LSsession_infos'] as $info) {
        $txt_infos.="<li>$info</li>\n";
      }
      $txt_infos.="</ul>\n";
      $data['LSinfos'] = $txt_infos;
      $_SESSION['LSsession_infos']=array();
    }
    
    if ($GLOBALS['LSerror']->errorsDefined()) {
      $data['LSerror'] = $GLOBALS['LSerror']->getErrors();
    }

    if (isset($_REQUEST['imgload'])) {
      $data['imgload'] = $_REQUEST['imgload'];
    }

    if (LSdebugDefined()) {
      $data['LSdebug'] = LSdebug_print(true);
    }

    echo json_encode($data);  
  }
 
 /**
  * Retournne un template Smarty compilé
  *
  * @param[in] string $template Le template à retourner
  * @param[in] array $variables Variables Smarty à assigner avant l'affichage
  * 
  * @retval string Le HTML compilé du template
  */
  function fetchTemplate($template,$variables=array()) {
    foreach($variables as $name => $val) {
      $GLOBALS['Smarty'] -> assign($name,$val);
    }
    return $GLOBALS['Smarty'] -> fetch($template);
  }
  
  /**
   * Charge les droits LS de l'utilisateur
   * 
   * @retval boolean True si le chargement Ã  rÃ©ussi, false sinon.
   **/
  function loadLSprofiles() {
    if (is_array($this -> ldapServer['LSprofiles'])) {
      foreach ($this -> ldapServer['LSprofiles'] as $profile => $profileInfos) {
        if (is_array($profileInfos)) {
          foreach ($profileInfos as $topDn => $rightsInfos) {
            /*
             * If $topDn == 'LSobject', we search for each LSobject type to find
             * all items on witch the user will have powers.
             */
            if ($topDn == 'LSobjects') {
              if (is_array($rightsInfos)) {
                foreach ($rightsInfos as $LSobject => $listInfos) {
                  if ($this -> loadLSobject($LSobject)) {
                    if ($object = new $LSobject()) {
                      if ($listInfos['filter']) {
                        $filter = $this -> LSuserObject -> getFData($listInfos['filter']);
                      }
                      else {
                        $filter = $listInfos['attr'].'='.$this -> LSuserObject -> getFData($listInfos['attr_value']);
                      }
                      $list = $object -> search($filter,$listInfos['basedn'],$listInfos['params']);
                      foreach($list as $obj) {
                        $this -> LSprofiles[$profile][] = $obj['dn'];
                      }
                    }
                    else {
                      LSdebug('Impossible de crÃ©er l\'objet de type : '.$LSobject);
                    }
                  }
                }
              }
              else {
                LSdebug('LSobjects => [] doit etre un tableau');
              }
            }
            else {
              if (is_array($rightsInfos)) {
                foreach($rightsInfos as $dn => $conf) {
                  if ((isset($conf['attr'])) && (isset($conf['LSobject']))) {
                    if( $this -> loadLSobject($conf['LSobject']) ) {
                      if ($object = new $conf['LSobject']()) {
                        if ($object -> loadData($dn)) {
                          $listDns=$object -> getValue($conf['attr']);
                          $valKey = (isset($conf['attr_value']))?$conf['attr_value']:'%{dn}';
                          $val = $this -> LSuserObject -> getFData($valKey);
                          if (is_array($listDns)) {
                            if (in_array($val,$listDns)) {
                              $this -> LSprofiles[$profile][] = $topDn;
                            }
                          }
                        }
                        else {
                          LSdebug('Impossible de chargÃ© le dn : '.$dn);
                        }
                      }
                      else {
                        LSdebug('Impossible de crÃ©er l\'objet de type : '.$conf['LSobject']);
                      }
                    }
                  }
                  else {
                    if ($this -> dn == $dn) {
                      $this -> LSprofiles[$profile][] = $topDn;
                    }
                  }
                }
              }
              else {
                if ( $this -> dn == $rightsInfos ) {
                  $this -> LSprofiles[$profile][] = $topDn;
                }
              }
            } // fin else ($topDn == 'LSobjects')
          } // fin foreach($profileInfos)
        } // fin is_array($profileInfos)
      } // fin foreach LSprofiles
      LSdebug($this -> LSprofiles);
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
    $LSaccess=array();
    if (is_array($this -> ldapServer['subDn'])) {
      foreach($this -> ldapServer['subDn'] as $name => $config) {
        if ($name=='LSobject') {
          if (is_array($config)) {

            // Définition des subDns 
            foreach($config as $objectType => $objectConf) {
              if ($this -> loadLSobject($objectType)) {
                if ($subdnobject = new $objectType()) {
                  $tbl = $subdnobject -> getSelectArray();
                  if (is_array($tbl)) {
                    // Définition des accès
                    $access=array();
                    if (is_array($objectConf['LSobjects'])) {
                      foreach($objectConf['LSobjects'] as $type) {
                        if ($this -> loadLSobject($type)) {
                          if ($this -> canAccess($type)) {
                            $access[$type] = $GLOBALS['LSobjects'][$type]['label'];
                          }
                        }
                      }
                    }
                    foreach($tbl as $dn => $dn_name) {
                      $LSaccess[$dn]=$access;
                    }
                  }
                }
              }
            }
          }
        }
        else {
          if ((isCompatibleDNs($this -> ldapServer['ldap_config']['basedn'],$config['dn']))&&($config['dn']!='')) {
            $access=array();
            if (is_array($config['LSobjects'])) {
              foreach($config['LSobjects'] as $objectType) {
                if ($this -> loadLSobject($objectType)) {
                  if ($this -> canAccess($objectType)) {
                    $access[$objectType] = $GLOBALS['LSobjects'][$objectType]['label'];
                  }
                }
              }
            }
            $LSaccess[$config['dn']]=$access;
          }
        }
      }
    }
    else {
      if(is_array($this -> ldapServer['LSaccess'])) {
        $access=array();
        foreach($this -> ldapServer['LSaccess'] as $objectType) {
          if ($this -> loadLSobject($objectType)) {
            if ($this -> canAccess($objectType)) {
                $access[$objectType] = $GLOBALS['LSobjects'][$objectType]['label'];
            }
          }
        }
        $LSaccess[$this -> topDn] = $access;
      }
    }
    foreach($LSaccess as $dn => $access) {
      $LSaccess[$dn] = array_merge(
        array(
          'SELF' => _('Mon compte')
        ),
        $access
      );
    }
    
    $this -> LSaccess = $LSaccess;
    $_SESSION['LSsession']['LSaccess'] = $LSaccess;
  }
  
  /**
   * Dit si l'utilisateur est du profil pour le DN spécifié
   *
   * @param[in] string $profile de l'objet
   * @param[in] string $dn DN de l'objet
   * 
   * @retval boolean True si l'utilisateur est du profil sur l'objet, false sinon.
   */
  function isProfile($dn,$profile) {
    if (is_array($this -> LSprofiles[$profile])) {
      foreach($this -> LSprofiles[$profile] as $topDn) {
        if($dn == $topDn) {
          return true;
        }
        else if ( isCompatibleDNs($dn,$topDn) ) {
          return true;
        }
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
    $retval = array('user');
    
    foreach($this -> LSprofiles as $profile => $infos) {
      if($this -> isProfile($dn,$profile)) {
       $retval[]=$profile;
      }
    }
    
    if ($this -> dn == $dn) {
      $retval[]='self';
    }
    
    return $retval;
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
    if (!$this -> loadLSobject($LSobject)) {
      return;
    }
    if ($dn) {
      $whoami = $this -> whoami($dn);
      if ($dn==$this -> LSuserObject -> getValue('dn')) {
        if (!$this -> in_menu('SELF')) {
          return;
        }
      }
      else {
        $obj = new $LSobject();
        $obj -> dn = $dn;
        if (!$this -> in_menu($LSobject,$obj -> getSubDnValue())) {
          return;
        }
      }
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

      $r = 'n';
      foreach($whoami as $who) {
        $nr = $GLOBALS['LSobjects'][$LSobject]['attrs'][$attr]['rights'][$who];
        if($nr == 'w') {
          $r = 'w';
        }
        else if($nr == 'r') {
          if ($r=='n') {
            $r='r';
          }
        }
      }
      
      if (($right=='r')||($right=='w')) {
        if ($r==$right) {
          return true;
        }
        return;
      }
      else {
        if ( ($r=='r') || ($r=='w') ) {
          return true;
        }
        return;
      }
    }
    
    // Pour un attribut quelconque
    if (is_array($GLOBALS['LSobjects'][$LSobject]['attrs'])) {
      if (($right=='r')||($right=='w')) {
        foreach($whoami as $who) {
          foreach ($GLOBALS['LSobjects'][$LSobject]['attrs'] as $attr_name => $attr_config) {
            if ($attr_config['rights'][$who]==$right) {
              return true;
            }
          }
        }
      }
      else {
        foreach($whoami as $who) {
          foreach ($GLOBALS['LSobjects'][$LSobject]['attrs'] as $attr_name => $attr_config) {
            if ( ($attr_config['rights'][$who]=='r') || ($attr_config['rights'][$who]=='w') ) {
              return true;
            }
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
   * @param[in] string $LSobject Le type de l'objet
   * @param[in] string $relationName Le nom de la relation avec l'objet
   * @param[in] string $right Le type de droit a vÃ©rifier ('r' ou 'w')
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */
  function relationCanAccess($dn,$LSobject,$relationName,$right=NULL) {
    if (!isset($GLOBALS['LSobjects'][$LSobject]['LSrelation'][$relationName]))
      return;
    $whoami = $this -> whoami($dn);

    if (($right=='w') || ($right=='r')) {
      $r = 'n';
      foreach($whoami as $who) {
        $nr = $GLOBALS['LSobjects'][$LSobject]['LSrelation'][$relationName]['rights'][$who];
        if($nr == 'w') {
          $r = 'w';
        }
        else if($nr == 'r') {
          if ($r=='n') {
            $r='r';
          }
        }
      }
      
      if ($r == $right) {
        return true;
      }
    }
    else {
      foreach($whoami as $who) {
        if (($GLOBALS['LSobjects'][$LSobject]['LSrelation'][$relationName]['rights'][$who] == 'w') || ($GLOBALS['LSobjects'][$LSobject]['LSrelation'][$relationName]['rights'][$who] == 'r')) {
          return true;
        }
      }
    }
    return;
  }

  /**
   * Retourne le droit de l'utilisateur Ã  modifier la relation d'objet
   * 
   * @param[in] string $dn Le DN de l'objet (le container_dn du type de l'objet par dÃ©faut)
   * @param[in] string $LSobject Le type de l'objet
   * @param[in] string $relationName Le nom de la relation avec l'objet
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */  
  function relationCanEdit($dn,$LSobject,$relationName) {
    return $this -> relationCanAccess($dn,$LSobject,$relationName,'w');
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
  function cacheLSprofiles() {
    return ( ($GLOBALS['LSconfig']['cacheLSprofiles']) || ($this -> ldapServer['cacheLSprofiles']) );
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
  function getSubDnLabel() {
    return ($this -> ldapServer['subDnLabel']!='')?$this -> ldapServer['subDnLabel']:_('Niveau');
  }
  
  /**
   * Retourne le nom du subDn
   * 
   * @param[in] $subDn string subDn
   * 
   * @retval string Le nom du subDn ou '' sinon
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

  /**
   * L'objet est t-il utilisé pour listé les subDnS
   * 
   * @param[in] $type string Le type d'objet
   * 
   * @retval boolean true si le type d'objet est un subDnObject, false sinon
   */
  function isSubDnLSobject($type) {
    $result = false;
    if (is_array($this -> ldapServer['subDn']['LSobject'])) {
      foreach($this -> ldapServer['subDn']['LSobject'] as $key => $value) {
        if ($key==$type) {
          $result=true;
        }
      }
    }
    return $result;
  }
  
  /**
   * Indique si un type d'objet est dans le menu courant
   * 
   * @retval boolean true si le type d'objet est dans le menu, false sinon
   */
  function in_menu($LSobject,$topDn=NULL) {
    if (!$topDn) {
      $topDn=$this -> topDn;
    }
    return isset($this -> LSaccess[$topDn][$LSobject]);
  }
  
  /**
   * Indique si le serveur LDAP courant a des subDn
   * 
   * @retval boolean true si le serveur LDAP courant a des subDn, false sinon
   */
  function haveSubDn() {
    return (is_array($this -> ldapServer['subDn']));
  }

  /**
   * Ajoute une information à afficher
   * 
   * @param[in] $msg string Le message à afficher
   * 
   * @retval void
   */
  function addInfo($msg) {
    $_SESSION['LSsession_infos'][]=$msg;
  }
  
  /**
   * Redirection de l'utilisateur vers une autre URL
   * 
   * @param[in] $url string L'URL
   * @param[in] $exit boolean Si true, l'execution script s'arrête après la redirection
   * 
   * @retval void
   */  
  function redirect($url,$exit=true) {
    $GLOBALS['Smarty'] -> assign('url',$url);
    $GLOBALS['Smarty'] -> display('redirect.tpl');
    if ($exit) {
      exit();
    }
  }
  
  /**
   * Retourne l'adresse mail d'emission configurée pour le serveur courant
   * 
   * @retval string Adresse mail d'emission
   */
  function getEmailSender() {
    return $this -> ldapServer['emailSender'];  
  }
  
  /**
   * Ajout d'une information d'aide
   * 
   * @param[in] $group string Le nom du groupe d'infos dans lequels ajouter
   *                          celle-ci
   * @param[in] $infos array  Tableau array(name => value) des infos
   * 
   * @retval void
   */
  function addHelpInfos($group,$infos) {
    if (is_array($infos)) {
      if (is_array($this -> _JSconfigParams['helpInfos'][$group])) {
        $this -> _JSconfigParams['helpInfos'][$group] = array_merge($this -> _JSconfigParams['helpInfos'][$group],$infos);
      }
      else {
        $this -> _JSconfigParams['helpInfos'][$group] = $infos;
      }
    }
  }
}

/*
 * Error Codes
 */
$GLOBALS['LSerror_code']['LSsession_01'] = array (
  'msg' => _("LSsession : The constant %{const} is not defined.")
);
$GLOBALS['LSerror_code']['LSsession_02'] = array (
  'msg' => _("LSsession : The %{addon} support is uncertain. Verify system compatibility and the add-on configuration.")
);
$GLOBALS['LSerror_code']['LSsession_03'] = array (
  'msg' => _("LSsession : LDAP server's configuration data are invalid. Impossible d'établir une connexion.")
);
$GLOBALS['LSerror_code']['LSsession_04'] = array (
  'msg' => _("LSsession : Failed to load LSobject type %{type} : unknon type.")
);
// no longer used
/*$GLOBALS['LSerror_code'][1005] = array (
  'msg' => _("LSsession : Object type use for authentication is unknow (%{type}).")
);*/
$GLOBALS['LSerror_code']['LSsession_06'] = array (
  'msg' => _("LSsession : Login or password incorrect.")
);
$GLOBALS['LSerror_code']['LSsession_07'] = array (
  'msg' => _("LSsession : Impossible to identify you : Duplication of identities.")
);
$GLOBALS['LSerror_code']['LSsession_08'] = array (
  'msg' => _("LSsession : Can't load Smarty template engine.")
);
$GLOBALS['LSerror_code']['LSsession_09'] = array (
  'msg' => _("LSsession : Can't connect to LDAP server.")
);
$GLOBALS['LSerror_code']['LSsession_10'] = array (
  'msg' => _("LSsession : Impossible to load authentification objects's class.")
);
$GLOBALS['LSerror_code']['LSsession_11'] = array (
  'msg' => _("LSsession : Your are not authorized to do this action.")
);
$GLOBALS['LSerror_code']['LSsession_12'] = array (
  'msg' => _("LSsession : Some informations are missing to display this page.")
);
// 13 -> 16 : not yet used
$GLOBALS['LSerror_code']['LSsession_17'] = array (
  'msg' => _("LSsession : Error during creation of list of levels. Contact administrators. (Code : %{code})")
);
$GLOBALS['LSerror_code']['LSsession_18'] = array (
  'msg' => _("LSsession : The password recovery is disabled for this LDAP server.")
);
$GLOBALS['LSerror_code']['LSsession_19'] = array (
  'msg' => _("LSsession : Some informations are missing to recover your password. Contact administrators.")
);
$GLOBALS['LSerror_code']['LSsession_20'] = array (
  'msg' => _("LSsession : Error during password recovery. Contact administrators.(Step : %{step})")
);
// 21 : not yet used
$GLOBALS['LSerror_code']['LSsession_22'] = array(
  'msg' => _("LSsession : problem during initialisation.")
);


// LSrelations
$GLOBALS['LSerror_code']['LSrelations_01'] = array (
  'msg' => _("LSrelations : The listing function for the relation %{relation} is unknow.")
);
$GLOBALS['LSerror_code']['LSrelations_02'] = array (
  'msg' => _("LSrelations : The update function of the relation %{relation} is unknow.")
);
$GLOBALS['LSerror_code']['LSrelations_03'] = array (
  'msg' => _("LSrelations : Error during relation update of the relation %{relation}.")
);
$GLOBALS['LSerror_code']['LSrelations_04'] = array (
  'msg' => _("LSrelations : Object type %{LSobject} unknow (Relation : %{relation}).")
);
$GLOBALS['LSerror_code']['LSrelations_05'] = array (
  'msg' => _("LSrelation : Some parameters are missing in the invocation of the methods of handling relations standard (Methode : %{meth}).")
);
?>
