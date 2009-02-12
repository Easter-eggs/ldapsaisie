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

  // La configuration du serveur Ldap utilisé
  public static $ldapServer = NULL;
  
  // L'id du serveur Ldap utilisé
  private static $ldapServerId = NULL;
  
  // Le topDn courant
  private static $topDn = NULL;
  
  // Le DN de l'utilisateur connecté
  private static $dn = NULL;
  
  // Le RDN de l'utilisateur connecté (son identifiant)
  private static $rdn = NULL;
  
  // Les LSprofiles de l'utilisateur
  private static $LSprofiles = array();
  
  // Les droits d'accès de l'utilisateur
  private static $LSaccess = array();
  
  // Les fichiers temporaires
  private static $tmp_file = array();
  
  /*
   * Constante de classe non stockée en session
   */
  // Le template à afficher
  private static $template = NULL;
  
  // Les subDn des serveurs Ldap
  private static $_subDnLdapServer = array();
  
  // Affichage Ajax
  private static $ajaxDisplay = false;

  // Les fichiers JS à charger dans la page
  private static $JSscripts = array();
  
  // Les paramètres JS à communiquer dans la page
  private static $_JSconfigParams = array();
  
  // Les fichiers CSS à charger dans la page
  private static $CssFiles = array();

  // L'objet de l'utilisateur connecté
  private static $LSuserObject = NULL;

 /**
  * Include un fichier PHP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval true si tout c'est bien passé, false sinon
  */
  public static function includeFile($file) {
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
  private static function loadConfig() {
    if (loadDir(LS_DEFAULT_CONF_DIR, '^config\..*\.php$')) {
      if ( self :: includeFile($GLOBALS['LSconfig']['Smarty']) ) {
        $GLOBALS['Smarty'] = new Smarty();
        $GLOBALS['Smarty'] -> template_dir = LS_TEMPLATES_DIR;
        $GLOBALS['Smarty'] -> compile_dir = LS_TMP_DIR;
        
        $GLOBALS['Smarty'] -> assign('LS_CSS_DIR',LS_CSS_DIR);
        $GLOBALS['Smarty'] -> assign('LS_IMAGES_DIR',LS_IMAGES_DIR);
        
        self :: addJSconfigParam('LS_IMAGES_DIR',LS_IMAGES_DIR);
        return true;
      }
      else {
        die("ERROR : Can't load Smarty.");
        return;
      }
      return true;
    }
    else {
      die("ERROR : Can't load configuration files.");
      return;
    }
    
  }
  
  public static function getTopDn() {
    return self :: $topDn;
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
  private static function startLSerror() {
    if(!self :: loadLSclass('LSerror')) {
      return;
    }
    self :: defineLSerrors();
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
  public static function loadLSclass($class,$type='') {
    if (class_exists($class))
      return true;
    if($type!='')
      $type=$type.'.';
    return self :: includeFile(LS_CLASS_DIR .'class.'.$type.$class.'.php');
  }

 /**
  * Chargement d'un object LdapSaisie
  *
  * @param[in] $object Nom de l'objet Ã  charger
  *
  * @retval boolean true si le chargement a rÃ©ussi, false sinon.
  */
  public static function loadLSobject($object) {
    $error = 0;
    self :: loadLSclass('LSldapObject');
    if (!self :: loadLSclass($object,'LSobjects')) {
      $error = 1;
    }
    if (!self :: includeFile( LS_OBJECTS_DIR . 'config.LSobjects.'.$object.'.php' )) {
      $error = 1;
    }
    if ($error) {
      LSerror :: addErrorCode('LSsession_04',$object);
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
  public static function loadLSaddon($addon) {
    if(self :: includeFile(LS_ADDONS_DIR .'LSaddons.'.$addon.'.php')) {
      self :: includeFile(LS_CONF_DIR."LSaddons/config.LSaddons.".$addon.".php");
      if (!call_user_func('LSaddon_'. $addon .'_support')) {
        LSerror :: addErrorCode('LSsession_02',$addon);
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
  public static function loadLSaddons() {
    if(!is_array($GLOBALS['LSaddons']['loads'])) {
      LSerror :: addErrorCode('LSsession_01',"LSaddons['loads']");
      return;
    }

    foreach ($GLOBALS['LSaddons']['loads'] as $addon) {
      self :: loadLSaddon($addon);
    }
    return true;
  }

/**
  * Initialisation LdapSaisie
  *
  * @retval boolean True si l'initialisation à réussi, false sinon.
  */
  public static function initialize() {
    if (!self :: loadConfig()) {
      return;
    }   
    self :: startLSerror();
    self :: loadLSaddons();
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
  public static function startLSsession() {
    if (!self :: initialize()) {
      return;
    }   

    session_start();

    // DÃ©connexion
    if (isset($_GET['LSsession_logout'])||isset($_GET['LSsession_recoverPassword'])) {
      session_destroy();
      
      if (is_array($_SESSION['LSsession']['tmp_file'])) {
        self :: $tmp_file = $_SESSION['LSsession']['tmp_file'];
      }
      self :: deleteTmpFile();
      unset($_SESSION['LSsession']);
    }
    
    // Récupération de mot de passe
    if (isset($_GET['recoveryHash'])) {
      $_POST['LSsession_user'] = 'a determiner plus tard';
    }
    
    if(isset($_SESSION['LSsession'])) {
      // Session existante
      self :: $topDn        = $_SESSION['LSsession']['topDn'];
      self :: $dn           = $_SESSION['LSsession']['dn'];
      self :: $rdn          = $_SESSION['LSsession']['rdn'];
      self :: $ldapServerId = $_SESSION['LSsession']['ldapServerId'];
      self :: $tmp_file     = $_SESSION['LSsession']['tmp_file'];
      
      if ( self :: cacheLSprofiles() && !isset($_REQUEST['LSsession_refresh']) ) {
        self :: setLdapServer(self :: $ldapServerId);
        self :: $LSprofiles   = $_SESSION['LSsession']['LSprofiles'];
        self :: $LSaccess   = $_SESSION['LSsession']['LSaccess'];
        if (!self :: LSldapConnect())
          return;
      }
      else {
        self :: setLdapServer(self :: $ldapServerId);
        if (!self :: LSldapConnect())
          return;
        self :: loadLSprofiles();
      }
      
      if ( self :: cacheSudDn() && (!isset($_REQUEST['LSsession_refresh'])) ) {
        self :: $_subDnLdapServer = $_SESSION['LSsession_subDnLdapServer'];
      }
      
      if (!self :: loadLSobject(self :: $ldapServer['authObjectType'])) {
        return;
      }
      
      self :: getLSuserObject();
      
      if ( !self :: cacheLSprofiles() || isset($_REQUEST['LSsession_refresh']) ) {
        self :: loadLSaccess();
      }
      
      $GLOBALS['Smarty'] -> assign('LSsession_username',self :: getLSuserObject() -> getDisplayName());
      
      if ($_POST['LSsession_topDn']) {
        if (self :: validSubDnLdapServer($_POST['LSsession_topDn'])) {
          self :: $topDn = $_POST['LSsession_topDn'];
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
          self :: setLdapServer($_POST['LSsession_ldapserver']);
        }
        else {
          self :: setLdapServer(0);
        }
        
        // Connexion au serveur LDAP
        if (self :: LSldapConnect()) {

          // topDn
          if ( $_POST['LSsession_topDn'] != '' ){
            self :: $topDn = $_POST['LSsession_topDn'];
          }
          else {
            self :: $topDn = self :: $ldapServer['ldap_config']['basedn'];
          }
          $_SESSION['LSsession_topDn']=self :: $topDn;

          if ( self :: loadLSobject(self :: $ldapServer['authObjectType']) ) {
            $authobject = new self :: $ldapServer['authObjectType']();
            $find=true;
            if (isset($_GET['recoveryHash'])) {
              $filter=self :: $ldapServer['recoverPassword']['recoveryHashAttr']."=".$_GET['recoveryHash'];
              $result = $authobject -> listObjects($filter,self :: $topDn);
              $nbresult=count($result);
              if ($nbresult==1) {
                $rdn = $result[0] -> getValue('rdn');
                $rdn = $rdn[0];
                $_POST['LSsession_user'] = $rdn;
                $find=false;
              }
            }
            if ($find) {
              $result = $authobject -> searchObject($_POST['LSsession_user'],self :: $topDn);
              $nbresult=count($result);
            }
            if ($nbresult==0) {
              // identifiant incorrect
              LSdebug('identifiant incorrect');
              LSerror :: addErrorCode('LSsession_06');
            }
            else if ($nbresult>1) {
              // duplication d'authentitÃ©
              LSerror :: addErrorCode('LSsession_07');
            }
            else {
              if (isset($_GET['LSsession_recoverPassword'])) {
                LSdebug('Recover : Id trouvé');
                if (self :: $ldapServer['recoverPassword']) {
                  if (self :: loadLSaddon('mail')) {
                    LSdebug('Récupération active');
                    $user=$result[0];
                    $emailAddress = $user -> getValue(self :: $ldapServer['recoverPassword']['mailAttr']);
                    $emailAddress = $emailAddress[0];
                    
                    // Header des mails
                    $sendParams=array();
                    if (self :: $ldapServer['recoverPassword']['recoveryEmailSender']) {
                      $sendParams['From']=self :: $ldapServer['recoverPassword']['recoveryEmailSender'];
                    }
                    
                    if (checkEmail($emailAddress)) {
                      LSdebug('Email : '.$emailAddress);
                      self :: $dn = $user -> getDn();
                      // 1ère étape : envoie du recoveryHash
                      if (!isset($_GET['recoveryHash'])) {
                        // Generer un hash
                        $rdn=$user -> getValue('rdn');
                        $rdn = $rdn[0];
                        $recovery_hash = md5($rdn . strval(time()) . strval(rand()));
                        
                        $lostPasswdForm = $user -> getForm('lostPassword');
                        $lostPasswdForm -> setPostData(
                          array(
                            self :: $ldapServer['recoverPassword']['recoveryHashAttr'] => $recovery_hash
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
                                self :: $ldapServer['recoverPassword']['recoveryHashMail']['subject'],
                                getFData(self :: $ldapServer['recoverPassword']['recoveryHashMail']['msg'],$recovery_url),
                                $sendParams
                              )
                            ){
                              // Mail a bien été envoyé
                              $recoveryPasswordInfos['recoveryHashMail']=$emailAddress;
                            }
                            else {
                              // Problème durant l'envoie du mail
                              LSdebug("Problème durant l'envoie du mail");
                              LSerror :: addErrorCode('LSsession_20',7);
                            }
                          }
                          else {
                            // Erreur durant la mise à jour de l'objet
                            LSdebug("Erreur durant la mise à jour de l'objet");
                            LSerror :: addErrorCode('LSsession_20',6);
                          }
                        }
                        else {
                          // Erreur durant la validation du formulaire de modification de perte de password
                          LSdebug("Erreur durant la validation du formulaire de modification de perte de password");
                          LSerror :: addErrorCode('LSsession_20',5);
                        }
                      }
                      // 2nd étape : génération du mot de passe + envoie par mail
                      else {
                        $attr=$user -> attrs[self :: $ldapServer['authObjectTypeAttrPwd']];
                        if ($attr instanceof LSattribute) {
                          $mdp = generatePassword($attr -> config['html_options']['chars'],$attr -> config['html_options']['lenght']);
                          LSdebug('Nvx mpd : '.$mdp);
                          $lostPasswdForm = $user -> getForm('lostPassword');
                          $lostPasswdForm -> setPostData(
                            array(
                              self :: $ldapServer['recoverPassword']['recoveryHashAttr'] => array(''),
                              self :: $ldapServer['authObjectTypeAttrPwd'] => array($mdp)
                            )
                            ,true
                          );
                          if($lostPasswdForm -> validate()) {
                            if ($user -> updateData('lostPassword')) {
                              if (
                                sendMail(
                                  $emailAddress,
                                  self :: $ldapServer['recoverPassword']['newPasswordMail']['subject'],
                                  getFData(self :: $ldapServer['recoverPassword']['newPasswordMail']['msg'],$mdp),
                                  $sendParams
                                )
                              ){
                                // Mail a bien été envoyé
                                $recoveryPasswordInfos['newPasswordMail']=$emailAddress;
                              }
                              else {
                                // Problème durant l'envoie du mail
                                LSdebug("Problème durant l'envoie du mail");
                                LSerror :: addErrorCode('LSsession_20',4);
                              }
                            }
                            else {
                              // Erreur durant la mise à jour de l'objet
                              LSdebug("Erreur durant la mise à jour de l'objet");
                              LSerror :: addErrorCode('LSsession_20',3);
                            }
                          }
                          else {
                            // Erreur durant la validation du formulaire de modification de perte de password
                            LSdebug("Erreur durant la validation du formulaire de modification de perte de password");
                            LSerror :: addErrorCode('LSsession_20',2);
                          }
                        }
                        else {
                          // l'attribut password n'existe pas
                          LSdebug("L'attribut password n'existe pas");
                          LSerror :: addErrorCode('LSsession_20',1);
                        }
                      }
                    }
                    else {
                      LSerror :: addErrorCode('LSsession_19');
                    }
                  }
                }
                else {
                  LSerror :: addErrorCode('LSsession_18');
                }
              }
              else {
                if ( self :: checkUserPwd($result[0],$_POST['LSsession_pwd']) ) {
                  // Authentification rÃ©ussi
                  self :: $LSuserObject = $result[0];
                  self :: $dn = $result[0]->getValue('dn');
                  self :: $rdn = $_POST['LSsession_user'];
                  self :: loadLSprofiles();
                  self :: loadLSaccess();
                  $GLOBALS['Smarty'] -> assign('LSsession_username',self :: getLSuserObject() -> getDisplayName());
                  $_SESSION['LSsession']=self :: getContextInfos();
                  return true;
                }
                else {
                  LSerror :: addErrorCode('LSsession_06');
                  LSdebug('mdp incorrect');
                }
              }
            }
          }
          else {
            LSerror :: addErrorCode('LSsession_10');
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_09');
        }
      }
      if (self :: $ldapServerId) {
        $GLOBALS['Smarty'] -> assign('ldapServerId',self :: $ldapServerId);
      }
      $GLOBALS['Smarty'] -> assign('topDn',self :: $topDn);
      if (isset($_GET['LSsession_recoverPassword'])) {
        self :: displayRecoverPasswordForm($recoveryPasswordInfos);
      }
      else {
        self :: displayLoginForm();
      }
      return;
    }
  }
  
 /**
  * Retourne les informations du contexte
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  * 
  * @retval array Tableau associatif des informations du contexte
  */
  private static function getContextInfos() {
    return array(
      'tmp_file' => self :: $tmp_file,
      'topDn' => self :: $topDn,
      'dn' => self :: $dn,
      'rdn' => self :: $rdn,
      'ldapServerId' => self :: $ldapServerId,
      'ldapServer' => self :: $ldapServer,
      'LSprofiles' => self :: $LSprofiles,
      'LSaccess' => self :: $LSaccess
    );
  }
  
  /**
  * Retourne l'objet de l'utilisateur connecté
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  * 
  * @retval mixed L'objet de l'utilisateur connecté ou false si il n'a pas put
  *               être créé
  */
  public static function getLSuserObject($dn=null) {
    if ($dn) {
      self :: $dn = $dn;
    }
    if (!self :: $LSuserObject) {
      if (self :: loadLSobject(self :: $ldapServer['authObjectType'])) {
        self :: $LSuserObject = new self :: $ldapServer['authObjectType']();
        self :: $LSuserObject -> loadData(self :: $dn);
      }
      else {
        return;
      }
    }
    return self :: $LSuserObject;
  }
  
 /**
  * Retourne le DN de l'utilisateur connecté
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  * 
  * @retval string Le DN de l'utilisateur connecté
  */
  public static function getLSuserObjectDn() {
    return self :: $dn;
  }

 /**
  * Modifie l'utilisateur connecté à la volé
  * 
  * @param[in] $object Mixed  L'objet Ldap du nouvel utilisateur
  *                           le type doit correspondre à
  *                           self :: $ldapServer['authObjectType']
  * 
  * @retval boolean True en cas de succès, false sinon
  */
 public static function changeAuthUser($object) {
  if ($object instanceof self :: $ldapServer['authObjectType']) {
    self :: $dn = $object -> getDn();
    $rdn = $object -> getValue('rdn');
    if(is_array($rdn)) {
      $rdn = $rdn[0];
    }
    self :: $rdn = $rdn;
    self :: $LSuserObject = $object;
    
    if(self :: loadLSprofiles()) {
      self :: loadLSaccess();
      $_SESSION['LSsession']=self :: getContextInfos();
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
  public static function setLdapServer($id) {
    if ( isset($GLOBALS['LSconfig']['ldap_servers'][$id]) ) {
      self :: $ldapServerId = $id;
      self :: $ldapServer=$GLOBALS['LSconfig']['ldap_servers'][$id];
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
  public static function LSldapConnect() {
    if (self :: $ldapServer) {
      self :: includeFile($GLOBALS['LSconfig']['NetLDAP2']);
      if (!self :: loadLSclass('LSldap')) {
        return;
      }
      LSldap :: connect(self :: $ldapServer['ldap_config']);
      if (LSldap :: isConnected()) {
        return true;
      }
      else {
        return;
      }
    }
    else {
      LSerror :: addErrorCode('LSsession_03');
      return;
    }
  }

 /**
  * Retourne les sous-dns du serveur Ldap courant
  *
  * @retval mixed Tableau des subDn, false si une erreur est survenue.
  */
  public static function getSubDnLdapServer() {
    if (self :: cacheSudDn() && isset(self :: $_subDnLdapServer[self :: $ldapServerId])) {
      return self :: $_subDnLdapServer[self :: $ldapServerId];
    }
    if (!isset(self :: $ldapServer['subDn'])) {
      return;
    }
    if ( !is_array(self :: $ldapServer['subDn']) ) {
      return;
    }
    $return=array();
    foreach(self :: $ldapServer['subDn'] as $subDn_name => $subDn_config) {
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
            if( self :: loadLSobject($LSobject_name) ) {
              if ($subdnobject = new $LSobject_name()) {
                $tbl_return = $subdnobject -> getSelectArray(NULL,$basedn,$displayName);
                if (is_array($tbl_return)) {
                  $return=array_merge($return,$tbl_return);
                }
                else {
                  LSerror :: addErrorCode('LSsession_17',3);
                }
              }
              else {
                LSerror :: addErrorCode('LSsession_17',2);
              }
            }
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_17',1);
        }
      }
      else {
        if ((isCompatibleDNs($subDn_config['dn'],self :: $ldapServer['ldap_config']['basedn']))&&($subDn_config['dn']!="")) {
          $return[$subDn_config['dn']] = $subDn_name;
        }
      }
    }
    if (self :: cacheSudDn()) {
      self :: $_subDnLdapServer[self :: $ldapServerId]=$return;
      $_SESSION['LSsession_subDnLdapServer'] = self :: $_subDnLdapServer;
    }
    return $return;
  }
  
  /**
   * Retourne la liste de subDn du serveur Ldap utilise
   * trié par la profondeur dans l'arboressence (ordre décroissant)
   * 
   * @return array() Tableau des subDn trié
   */  
  public static function getSortSubDnLdapServer() {
    $subDnLdapServer = self :: getSubDnLdapServer();
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
  * Liste les subdn (self :: $ldapServer['subDn'])
  *
  * @retval string Les options (<option>) pour la sÃ©lection du topDn.
  */
  public static function getSubDnLdapServerOptions($selected=NULL) {
    $list = self :: getSubDnLdapServer();
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

 /**
  * Vérifie qu'un subDn est déclaré
  *
  * @param[in] string Un subDn
  * 
  * @retval boolean True si le subDn existe, False sinon
  */
  public static function validSubDnLdapServer($subDn) {
    $listTopDn = self :: getSubDnLdapServer();
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
  public static function checkUserPwd($object,$pwd) {
    return LSldap :: checkBind($object -> getValue('dn'),$pwd);
  }

 /**
  * Affiche le formulaire de login
  *
  * DÃ©fini les informations pour le template Smarty du formulaire de login.
  *
  * @retval void
  */
  public static function displayLoginForm() {
    $GLOBALS['Smarty'] -> assign('pagetitle',_('Connection'));
    if (isset($_GET['LSsession_logout'])) {
      $GLOBALS['Smarty'] -> assign('loginform_action','index.php');
    }
    else {
      $GLOBALS['Smarty'] -> assign('loginform_action',$_SERVER['REQUEST_URI']);
    }
    if (count($GLOBALS['LSconfig']['ldap_servers'])==1) {
      $GLOBALS['Smarty'] -> assign('loginform_ldapserver_style','style="display: none"');
    }
    $GLOBALS['Smarty'] -> assign('loginform_label_ldapserver',_('LDAP server'));
    $ldapservers_name=array();
    $ldapservers_index=array();
    foreach($GLOBALS['LSconfig']['ldap_servers'] as $id => $infos) {
      $ldapservers_index[]=$id;
      $ldapservers_name[]=$infos['name'];
    }
    $GLOBALS['Smarty'] -> assign('loginform_ldapservers_name',$ldapservers_name);
    $GLOBALS['Smarty'] -> assign('loginform_ldapservers_index',$ldapservers_index);

    $GLOBALS['Smarty'] -> assign('loginform_label_level',_('Niveau'));
    $GLOBALS['Smarty'] -> assign('loginform_label_user',_('Identifier'));
    $GLOBALS['Smarty'] -> assign('loginform_label_pwd',_('Password'));
    $GLOBALS['Smarty'] -> assign('loginform_label_submit',_('Connect'));
    $GLOBALS['Smarty'] -> assign('loginform_label_recoverPassword',_('Forgot your password ?'));
    
    self :: setTemplate('login.tpl');
    self :: addJSscript('LSsession_login.js');
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
  public static function displayRecoverPasswordForm($recoveryPasswordInfos) {
    $GLOBALS['Smarty'] -> assign('pagetitle',_('Recovery of your credentials'));
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_action','index.php?LSsession_recoverPassword');
    
    if (count($GLOBALS['LSconfig']['ldap_servers'])==1) {
      $GLOBALS['Smarty'] -> assign('recoverpasswordform_ldapserver_style','style="display: none"');
    }
    
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_label_ldapserver',_('LDAP server'));
    $ldapservers_name=array();
    $ldapservers_index=array();
    foreach($GLOBALS['LSconfig']['ldap_servers'] as $id => $infos) {
      $ldapservers_index[]=$id;
      $ldapservers_name[]=$infos['name'];
    }
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_ldapservers_name',$ldapservers_name);
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_ldapservers_index',$ldapservers_index);

    $GLOBALS['Smarty'] -> assign('recoverpasswordform_label_user',_('Identifier'));
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_label_submit',_('Valid'));
    $GLOBALS['Smarty'] -> assign('recoverpasswordform_label_back',_('Back'));
    
    $recoverpassword_msg = _('Please fill the identifier field to proceed recovery procedure');
    
    if (isset($recoveryPasswordInfos['recoveryHashMail'])) {
      $recoverpassword_msg = getFData(
        _("An email has been sent to  %{mail}. " .
        "Please follow the instructions on it."),
        $recoveryPasswordInfos['recoveryHashMail']
      );
    }
    
    if (isset($recoveryPasswordInfos['newPasswordMail'])) {
      $recoverpassword_msg = getFData(
        _("Your new password has been sent to %{mail}. "),
        $recoveryPasswordInfos['newPasswordMail']
      );
    }
    
    $GLOBALS['Smarty'] -> assign('recoverpassword_msg',$recoverpassword_msg);
    
    self :: setTemplate('recoverpassword.tpl');
    self :: addJSscript('LSsession_recoverPassword.js');
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
  public static function setTemplate($template) {
    self :: $template = $template;
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
  public static function addJSscript($file,$path=NULL) {
    $script=array(
      'file' => $file,
      'path' => $path
    );
    self :: $JSscripts[$path.$file]=$script;
  }

 /**
  * Ajouter un paramètre de configuration Javascript
  * 
  * @param[in] $name string Nom de la variable de configuration
  * @param[in] $val mixed Valeur de la variable de configuration
  *
  * @retval void
  */
  public static function addJSconfigParam($name,$val) {
    self :: $_JSconfigParams[$name]=$val;
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
  public static function addCssFile($file,$path=NULL) {
    $cssFile=array(
      'file' => $file,
      'path' => $path
    );
    self :: $CssFiles[$path.$file]=$cssFile;
  }

 /**
  * Affiche le template Smarty
  *
  * Charge les dÃ©pendances et affiche le template Smarty
  *
  * @retval void
  */
  public static function displayTemplate() {
    // JS
    $JSscript_txt='';
    foreach ($GLOBALS['defaultJSscipts'] as $script) {
      $JSscript_txt.="<script src='".LS_JS_DIR.$script."' type='text/javascript'></script>\n";
    }

    foreach (self :: $JSscripts as $script) {
      if (!$script['path']) {
        $script['path']=LS_JS_DIR;
      }
      else {
        $script['path'].='/';
      }
      $JSscript_txt.="<script src='".$script['path'].$script['file']."' type='text/javascript'></script>\n";
    }

    $GLOBALS['Smarty'] -> assign('LSjsConfig',json_encode(self :: $_JSconfigParams));
    
    if ($GLOBALS['LSdebug']['active']) {
      $JSscript_txt.="<script type='text/javascript'>LSdebug_active = 1;</script>\n";
    }
    else {
      $JSscript_txt.="<script type='text/javascript'>LSdebug_active = 0;</script>\n";
    }
    
    $GLOBALS['Smarty'] -> assign('LSsession_js',$JSscript_txt);

    // Css
    self :: addCssFile("LSdefault.css");
    $Css_txt='';
    foreach (self :: $CssFiles as $file) {
      if (!$file['path']) {
        $file['path']=LS_CSS_DIR.'/';
      }
      $Css_txt.="<link rel='stylesheet' type='text/css' href='".$file['path'].$file['file']."' />\n";
    }
    $GLOBALS['Smarty'] -> assign('LSsession_css',$Css_txt);
  
    if (isset(self :: $LSaccess[self :: $topDn])) {
      $GLOBALS['Smarty'] -> assign('LSaccess',self :: $LSaccess[self :: $topDn]);
    }
    
    // Niveau
    $listTopDn = self :: getSubDnLdapServer();
    if (is_array($listTopDn)) {
      asort($listTopDn);
      $GLOBALS['Smarty'] -> assign('label_level',self :: getSubDnLabel());
      $GLOBALS['Smarty'] -> assign('_refresh',_('Refresh'));
      $LSsession_topDn_index = array();
      $LSsession_topDn_name = array();
      foreach($listTopDn as $index => $name) {
        $LSsession_topDn_index[]  = $index;
        $LSsession_topDn_name[]   = $name;
      }
      $GLOBALS['Smarty'] -> assign('LSsession_subDn_indexes',$LSsession_topDn_index);
      $GLOBALS['Smarty'] -> assign('LSsession_subDn_names',$LSsession_topDn_name);
      $GLOBALS['Smarty'] -> assign('LSsession_subDn',self :: $topDn);
      $GLOBALS['Smarty'] -> assign('LSsession_subDnName',self :: getSubDnName());
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
    
    if (self :: $ajaxDisplay) {
      $GLOBALS['Smarty'] -> assign('LSerror_txt',LSerror :: getErrors());
      $GLOBALS['Smarty'] -> assign('LSdebug_txt',LSdebug_print(true));
    }
    else {
      LSerror :: display();
      LSdebug_print();
    }
    if (!self :: $template)
      self :: setTemplate('empty.tpl');
    $GLOBALS['Smarty'] -> display(self :: $template);
  }
  
 /**
  * Défini que l'affichage se fera ou non via un retour Ajax
  * 
  * @param[in] $val boolean True pour que l'affichage se fasse par un retour
  *                         Ajax, false sinon
  * @retval void
  */
  public static function setAjaxDisplay($val=true) {
    self :: $ajaxDisplay = (boolean)$val;
  }
  
 /**
  * Affiche un retour Ajax
  *
  * @retval void
  */
  public static function displayAjaxReturn($data=array()) {
    if (isset($data['LSredirect']) && (!LSdebugDefined()) ) {
      echo json_encode($data);
      return;
    }
    
    $data['LSjsConfig'] = self :: $_JSconfigParams;
    
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
    
    if (LSerror :: errorsDefined()) {
      $data['LSerror'] = LSerror :: getErrors();
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
  public static function fetchTemplate($template,$variables=array()) {
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
  private static function loadLSprofiles() {
    if (is_array(self :: $ldapServer['LSprofiles'])) {
      foreach (self :: $ldapServer['LSprofiles'] as $profile => $profileInfos) {
        if (is_array($profileInfos)) {
          foreach ($profileInfos as $topDn => $rightsInfos) {
            /*
             * If $topDn == 'LSobject', we search for each LSobject type to find
             * all items on witch the user will have powers.
             */
            if ($topDn == 'LSobjects') {
              if (is_array($rightsInfos)) {
                foreach ($rightsInfos as $LSobject => $listInfos) {
                  if (self :: loadLSobject($LSobject)) {
                    if ($object = new $LSobject()) {
                      if ($listInfos['filter']) {
                        $filter = self :: getLSuserObject() -> getFData($listInfos['filter']);
                      }
                      else {
                        $filter = $listInfos['attr'].'='.self :: getLSuserObject() -> getFData($listInfos['attr_value']);
                      }
                      $list = $object -> search($filter,$listInfos['basedn'],$listInfos['params']);
                      foreach($list as $obj) {
                        self :: $LSprofiles[$profile][] = $obj['dn'];
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
                    if( self :: loadLSobject($conf['LSobject']) ) {
                      if ($object = new $conf['LSobject']()) {
                        if ($object -> loadData($dn)) {
                          $listDns=$object -> getValue($conf['attr']);
                          $valKey = (isset($conf['attr_value']))?$conf['attr_value']:'%{dn}';
                          $val = self :: getLSuserObject() -> getFData($valKey);
                          if (is_array($listDns)) {
                            if (in_array($val,$listDns)) {
                              self :: $LSprofiles[$profile][] = $topDn;
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
                    if (self :: $dn == $dn) {
                      self :: $LSprofiles[$profile][] = $topDn;
                    }
                  }
                }
              }
              else {
                if ( self :: $dn == $rightsInfos ) {
                  self :: $LSprofiles[$profile][] = $topDn;
                }
              }
            } // fin else ($topDn == 'LSobjects')
          } // fin foreach($profileInfos)
        } // fin is_array($profileInfos)
      } // fin foreach LSprofiles
      LSdebug(self :: $LSprofiles);
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
  private static function loadLSaccess() {
    $LSaccess=array();
    if (is_array(self :: $ldapServer['subDn'])) {
      foreach(self :: $ldapServer['subDn'] as $name => $config) {
        if ($name=='LSobject') {
          if (is_array($config)) {

            // Définition des subDns 
            foreach($config as $objectType => $objectConf) {
              if (self :: loadLSobject($objectType)) {
                if ($subdnobject = new $objectType()) {
                  $tbl = $subdnobject -> getSelectArray();
                  if (is_array($tbl)) {
                    // Définition des accès
                    $access=array();
                    if (is_array($objectConf['LSobjects'])) {
                      foreach($objectConf['LSobjects'] as $type) {
                        if (self :: loadLSobject($type)) {
                          if (self :: canAccess($type)) {
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
          if ((isCompatibleDNs(self :: $ldapServer['ldap_config']['basedn'],$config['dn']))&&($config['dn']!='')) {
            $access=array();
            if (is_array($config['LSobjects'])) {
              foreach($config['LSobjects'] as $objectType) {
                if (self :: loadLSobject($objectType)) {
                  if (self :: canAccess($objectType)) {
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
      if(is_array(self :: $ldapServer['LSaccess'])) {
        $access=array();
        foreach(self :: $ldapServer['LSaccess'] as $objectType) {
          if (self :: loadLSobject($objectType)) {
            if (self :: canAccess($objectType)) {
                $access[$objectType] = $GLOBALS['LSobjects'][$objectType]['label'];
            }
          }
        }
        $LSaccess[self :: $topDn] = $access;
      }
    }
    foreach($LSaccess as $dn => $access) {
      $LSaccess[$dn] = array_merge(
        array(
          'SELF' => _('My account')
        ),
        $access
      );
    }
    
    self :: $LSaccess = $LSaccess;
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
  public static function isLSprofile($dn,$profile) {
    if (is_array(self :: $LSprofiles[$profile])) {
      foreach(self :: $LSprofiles[$profile] as $topDn) {
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
  public static function whoami($dn) {
    $retval = array('user');
    
    foreach(self :: $LSprofiles as $profile => $infos) {
      if(self :: isLSprofile($dn,$profile)) {
       $retval[]=$profile;
      }
    }
    
    if (self :: $dn == $dn) {
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
  public static function canAccess($LSobject,$dn=NULL,$right=NULL,$attr=NULL) {
    if (!self :: loadLSobject($LSobject)) {
      return;
    }
    if ($dn) {
      $whoami = self :: whoami($dn);
      if ($dn==self :: getLSuserObject() -> getValue('dn')) {
        if (!self :: in_menu('SELF')) {
          return;
        }
      }
      else {
        $obj = new $LSobject();
        $obj -> dn = $dn;
        if (!self :: in_menu($LSobject,$obj -> getSubDnValue())) {
          return;
        }
      }
    }
    else {
      $objectdn=$GLOBALS['LSobjects'][$LSobject]['container_dn'].','.self :: $topDn;
      $whoami = self :: whoami($objectdn);
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
  public static function canEdit($LSobject,$dn=NULL,$attr=NULL) {
    return self :: canAccess($LSobject,$dn,'w',$attr);
  }

  /**
   * Retourne le droit de l'utilisateur Ã  supprimer un objet
   * 
   * @param[in] string $LSobject Le type de l'objet
   * @param[in] string $dn Le DN de l'objet (le container_dn du type de l'objet par dÃ©faut)
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */  
  public static function canRemove($LSobject,$dn) {
    return self :: canAccess($LSobject,$dn,'w','rdn');
  }
  
  /**
   * Retourne le droit de l'utilisateur Ã  crÃ©er un objet
   * 
   * @param[in] string $LSobject Le type de l'objet
   *
   * @retval boolean True si l'utilisateur a accÃ¨s, false sinon
   */    
  public static function canCreate($LSobject) {
    return self :: canAccess($LSobject,NULL,'w','rdn');
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
  public static function relationCanAccess($dn,$LSobject,$relationName,$right=NULL) {
    if (!isset($GLOBALS['LSobjects'][$LSobject]['LSrelation'][$relationName]))
      return;
    $whoami = self :: whoami($dn);

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
  public static function relationCanEdit($dn,$LSobject,$relationName) {
    return self :: relationCanAccess($dn,$LSobject,$relationName,'w');
  }

  /**
   * Ajoute un fichier temporaire
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval void
   **/
  public static function addTmpFile($value,$filePath) {
    $hash = mhash(MHASH_MD5,$value);
    self :: $tmp_file[$filePath] = $hash;
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
  public static function tmpFileExist($value) {
    $hash = mhash(MHASH_MD5,$value);
    foreach(self :: $tmp_file as $filePath => $contentHash) {
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
  public static function getTmpFile($value) {
    $exist = self :: tmpFileExist($value);
    if (!$exist) {
      $img_path = LS_TMP_DIR .rand().'.tmp';
      $fp = fopen($img_path, "w");
      fwrite($fp, $value);
      fclose($fp);
      self :: addTmpFile($value,$img_path);
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
  public static function deleteTmpFile($filePath=NULL) {
    if ($filePath) {
        @unlink($filePath);
        unset(self :: $tmp_file[$filePath]);
        unset($_SESSION['LSsession']['tmp_file'][$filePath]);
    }
    else {
      foreach(self :: $tmp_file as $file => $content) {
        @unlink($file);
      }
      self :: $tmp_file = array();
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
  public static function cacheLSprofiles() {
    return ( ($GLOBALS['LSconfig']['cacheLSprofiles']) || (self :: $ldapServer['cacheLSprofiles']) );
  }

  /**
   * Retourne true si le cache des subDn est activé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si le cache des subDn est activé, false sinon.
   */
  public static function cacheSudDn() {
    return (($GLOBALS['LSconfig']['cacheSubDn']) || (self :: $ldapServer['cacheSubDn']));
  }
  
  /**
   * Retourne true si le cache des recherches est activé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval boolean True si le cache des recherches est activé, false sinon.
   */
  public static function cacheSearch() {
    return (($GLOBALS['LSconfig']['cacheSearch']) || (self :: $ldapServer['cacheSearch']));
  }
  
  /**
   * Retourne le label des niveaux pour le serveur ldap courant
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @retval string Le label des niveaux pour le serveur ldap dourant
   */
  public static function getSubDnLabel() {
    return (self :: $ldapServer['subDnLabel']!='')?self :: $ldapServer['subDnLabel']:_('Level');
  }
  
  /**
   * Retourne le nom du subDn
   * 
   * @param[in] $subDn string subDn
   * 
   * @retval string Le nom du subDn ou '' sinon
   */
  public static function getSubDnName($subDn=false) {
    if (!$subDn) {
      $subDn = self :: $topDn;
    }
    if (self :: getSubDnLdapServer()) {
      if (isset(self :: $_subDnLdapServer[self :: $ldapServerId][$subDn])) {
        return self :: $_subDnLdapServer[self :: $ldapServerId][$subDn];
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
  public static function isSubDnLSobject($type) {
    $result = false;
    if (is_array(self :: $ldapServer['subDn']['LSobject'])) {
      foreach(self :: $ldapServer['subDn']['LSobject'] as $key => $value) {
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
  public static function in_menu($LSobject,$topDn=NULL) {
    if (!$topDn) {
      $topDn=self :: $topDn;
    }
    return isset(self :: $LSaccess[$topDn][$LSobject]);
  }
  
  /**
   * Indique si le serveur LDAP courant a des subDn
   * 
   * @retval boolean true si le serveur LDAP courant a des subDn, false sinon
   */
  public static function haveSubDn() {
    return (is_array(self :: $ldapServer['subDn']));
  }

  /**
   * Ajoute une information à afficher
   * 
   * @param[in] $msg string Le message à afficher
   * 
   * @retval void
   */
  public static function addInfo($msg) {
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
  public static function redirect($url,$exit=true) {
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
  public static function getEmailSender() {
    return self :: $ldapServer['emailSender'];  
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
  public static function addHelpInfos($group,$infos) {
    if (is_array($infos)) {
      if (is_array(self :: $_JSconfigParams['helpInfos'][$group])) {
        self :: $_JSconfigParams['helpInfos'][$group] = array_merge(self :: $_JSconfigParams['helpInfos'][$group],$infos);
      }
      else {
        self :: $_JSconfigParams['helpInfos'][$group] = $infos;
      }
    }
  }
  
 /**
  * Défini les codes erreur relative à la classe LSsession
  * 
  * @retval void
  */  
  private static function defineLSerrors() {
    /*
     * Error Codes
     */
    LSerror :: defineError('LSsession_01',
    _("LSsession : The constant %{const} is not defined.")
    );
    LSerror :: defineError('LSsession_02',
    _("LSsession : The %{addon} support is uncertain. Verify system compatibility and the add-on configuration.")
    );
    LSerror :: defineError('LSsession_03',
    _("LSsession : LDAP server's configuration data are invalid. Impossible d'établir une connexion.")
    );
    LSerror :: defineError('LSsession_04',
    _("LSsession : Failed to load LSobject type %{type} : unknon type.")
    );
    // no longer used
    /*LSerror :: defineError(1005,
    _("LSsession : Object type use for authentication is unknow (%{type}).")
    );*/
    LSerror :: defineError('LSsession_06',
    _("LSsession : Login or password incorrect.")
    );
    LSerror :: defineError('LSsession_07',
    _("LSsession : Impossible to identify you : Duplication of identities.")
    );
    LSerror :: defineError('LSsession_08',
    _("LSsession : Can't load Smarty template engine.")
    );
    LSerror :: defineError('LSsession_09',
    _("LSsession : Can't connect to LDAP server.")
    );
    LSerror :: defineError('LSsession_10',
    _("LSsession : Impossible to load authentification objects's class.")
    );
    LSerror :: defineError('LSsession_11',
    _("LSsession : Your are not authorized to do this action.")
    );
    LSerror :: defineError('LSsession_12',
    _("LSsession : Some informations are missing to display this page.")
    );
    // 13 -> 16 : not yet used
    LSerror :: defineError('LSsession_17',
    _("LSsession : Error during creation of list of levels. Contact administrators. (Code : %{code})")
    );
    LSerror :: defineError('LSsession_18',
    _("LSsession : The password recovery is disabled for this LDAP server.")
    );
    LSerror :: defineError('LSsession_19',
    _("LSsession : Some informations are missing to recover your password. Contact administrators.")
    );
    LSerror :: defineError('LSsession_20',
    _("LSsession : Error during password recovery. Contact administrators.(Step : %{step})")
    );
    // 21 : not yet used
    LSerror :: defineError('LSsession_22',
    _("LSsession : problem during initialisation.")
    );


    // LSrelations
    LSerror :: defineError('LSrelations_01',
    _("LSrelations : The listing function for the relation %{relation} is unknow.")
    );
    LSerror :: defineError('LSrelations_02',
    _("LSrelations : The update function of the relation %{relation} is unknow.")
    );
    LSerror :: defineError('LSrelations_03',
    _("LSrelations : Error during relation update of the relation %{relation}.")
    );
    LSerror :: defineError('LSrelations_04',
    _("LSrelations : Object type %{LSobject} unknow (Relation : %{relation}).")
    );
    LSerror :: defineError('LSrelations_05',
    _("LSrelation : Some parameters are missing in the invocation of the methods of handling relations standard (Methode : %{meth}).")
    );
  }
}

?>
