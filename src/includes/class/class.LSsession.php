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

  // LSaddons views
  private static $LSaddonsViews = array();
  private static $LSaddonsViewsAccess = array();

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

  // Libs JS files to load on page
  private static $LibsJSscripts = array();

  // Les paramètres JS à communiquer dans la page
  private static $_JSconfigParams = array();

  // Les fichiers CSS à charger dans la page
  private static $CssFiles = array();

  // Libs CSS files to load on page
  private static $LibsCssFiles = array();

  // L'objet de l'utilisateur connecté
  private static $LSuserObject = NULL;

  // The LSauht object of the session
  private static $LSauthObject = false;

  // User LDAP credentials
  private static $userLDAPcreds = false;

  // Initialized telltale
  private static $initialized = false;

 /**
  * Include un fichier PHP
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval true si tout c'est bien passé, false sinon
  */
  public static function includeFile($file, $external=false) {
    $path = ($external?'':LS_ROOT_DIR."/").$file;
    $local_path = ($external?'':LS_ROOT_DIR."/").LS_LOCAL_DIR.$file;
    $path = (file_exists($local_path)?$local_path:$path);
    if ($path[0] != '/') {
      $found = stream_resolve_include_path($path);
      if ($found === false) {
        $log_msg = "includeFile($file, external=$external) : file $path not found in include path.";
        if (class_exists('LSlog'))
          LSlog :: warning($log_msg);
        else
          error_log($log_msg);
        return;
      }
      else {
        $path = $found;
      }
    }
    else if (!file_exists($path)) {
      $log_msg = "includeFile($file, external=$external) : file not found ($local_path / $path)";
      if (class_exists('LSlog'))
        LSlog :: warning($log_msg);
      else
        error_log($log_msg);
      return;
    }
    return include_once($path);
  }

 /**
  * Lancement de LSconfig
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval true si tout c'est bien passÃ©, false sinon
  */
  private static function startLSconfig() {
    if (self :: loadLSclass('LSconfig')) {
      if (LSconfig :: start()) {
        return true;
      }
    }
    die("ERROR : Can't load configuration files.");
    return;
  }

 /**
  * Lancement de LSlog
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval true si tout c'est bien passÃ©, false sinon
  */
  private static function startLSlog() {
    if (self :: loadLSclass('LSlog')) {
      if (LSlog :: start()) {
        return true;
      }
    }
    return False;
  }

 /**
  * Lancement de LSurl
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval true si tout c'est bien passÃ©, false sinon
  */
  private static function startLSurl() {
    if (self :: loadLSclass('LSurl') && self :: includeFile(LS_INCLUDE_DIR . "routes.php")) {
      return true;
    }
    return False;
  }

 /**
  * Lancement et initialisation de Smarty
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval true si tout c'est bien passÃ©, false sinon
  */
  private static function startLStemplate() {
    if ( self :: loadLSclass('LStemplate') ) {
      return LStemplate :: start(
        array(
          'smarty_path'   => LSconfig :: get('Smarty'),
          'template_dir'  => LS_ROOT_DIR . '/'. LS_TEMPLATES_DIR,
          'image_dir'     => LS_ROOT_DIR. '/'. LS_IMAGES_DIR,
          'css_dir'       => LS_ROOT_DIR. '/'. LS_CSS_DIR,
          'js_dir'        => LS_ROOT_DIR. '/'. LS_JS_DIR,
          'libs_dir'      => LS_ROOT_DIR. '/'. LS_LIB_DIR,
          'compile_dir'   => LS_TMP_DIR_PATH,
          'debug'         => LSdebug,
          'debug_smarty'  => (isset($_REQUEST) && isset($_REQUEST['LStemplate_debug'])),
        )
      );
    }
    return False;
  }

 /**
  * Retourne le topDn de la session
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval string le topDn de la session
  */
  public static function getTopDn() {
    if (!is_null(self :: $topDn)) {
      return self :: $topDn;
    }
    else {
      return self :: getRootDn();
    }
  }

 /**
  * Retourne le rootDn de la session
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval string le rootDn de la session
  */
  public static function getRootDn() {
    return self :: $ldapServer['ldap_config']['basedn'];
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
  * Load an LdapSaisie class
  *
  * @param[in] $class The class name to load (Example : LSpeople)
  * @param[in] $type (Optionnel) The class type to load (Example : LSobjects)
  * @param[in] $warn (Optionnel) Trigger LSsession_05 error if an error occured loading this class (Default: false)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval boolean true on success, otherwise false
  */
  public static function loadLSclass($class, $type=null, $warn=false) {
    if (class_exists($class))
      return true;
    if($type)
      $class = "$type.$class";
    if (self :: includeFile(LS_CLASS_DIR .'class.'.$class.'.php'))
      return true;
    if ($warn)
      LSerror :: addErrorCode('LSsession_05', $class);
    return False;
  }

 /**
  * Chargement d'un object LdapSaisie
  *
  * @param[in] $object Nom de l'objet Ã  charger
  *
  * @retval boolean true si le chargement a rÃ©ussi, false sinon.
  */
  public static function loadLSobject($object) {
    if(class_exists($object)) {
      return true;
    }
    $error = 0;
    self :: loadLSclass('LSldapObject');
    if (!self :: loadLSclass($object,'LSobjects')) {
      LSlog :: error("LSsession :: loadLSobject($object) : Fail to load LSldapObject class");
      $error = 1;
    }
    if (!self :: includeFile( LS_OBJECTS_DIR . 'config.LSobjects.'.$object.'.php' )) {
      LSlog :: error("LSsession :: loadLSobject($object) : Fail to include 'config.LSobjects.$object.php' file");
      $error = 1;
    }
    else {
      if (!LSconfig :: set("LSobjects.$object",$GLOBALS['LSobjects'][$object])) {
        LSlog :: error("LSsession :: loadLSobject($object) : Fail to LSconfig :: set('LSobjects.$object', \$GLOBALS['LSobjects'][$object])");
        $error = 1;
      }
      else if (isset($GLOBALS['LSobjects'][$object]['LSaddons'])){
        if (is_array($GLOBALS['LSobjects'][$object]['LSaddons'])) {
          foreach ($GLOBALS['LSobjects'][$object]['LSaddons'] as $addon) {
            if (!self :: loadLSaddon($addon)) {
              LSlog :: error("LSsession :: loadLSobject($object) : Fail to load LSaddon '$addon'");
              $error = 1;
            }
          }
        }
        else {
          if (!self :: loadLSaddon($GLOBALS['LSobjects'][$object]['LSaddons'])) {
            LSlog :: error("LSsession :: loadLSobject($object) : Fail to load LSaddon '".$GLOBALS['LSobjects'][$object]['LSaddons']."'");
            $error = 1;
          }
        }
      }
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
  * Chargement d'une classe d'authentification d'LdapSaisie
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval boolean true si le chargement a reussi, false sinon.
  */
  public static function loadLSauth() {
    if (self :: loadLSclass('LSauth')) {
      return true;
    }
    else {
      LSerror :: addErrorCode('LSsession_05','LSauth');
    }
    return;
  }

 /**
  * Load LdapSaisie CLI class
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval boolean true if loaded, false otherwise.
  */
  public static function loadLScli() {
    if (self :: loadLSclass('LScli')) {
      return true;
    }
    else {
      LSerror :: addErrorCode('LSsession_05','LScli');
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
    $conf=LSconfig :: get('LSaddons.loads');
    if(!is_array($conf)) {
      LSerror :: addErrorCode('LSsession_01',"LSaddons['loads']");
      return;
    }

    foreach ($conf as $addon) {
      self :: loadLSaddon($addon);
    }
    return true;
  }


 /**
  * Load and start LSlang, the I18N manager
  *
  * @param[in] $lang string|null     The lang (optional, default: see LSlang :: setLocale())
  * @param[in] $encoding string|null The encoding (optional, default: see LSlang :: setLocale())
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval boolean true if LSlang started, false otherwise
  */
  private static function startLSlang($lang=null, $encoding=null) {
    if(!self :: loadLSclass('LSlang')) {
      return;
    }
    LSlang :: setLocale($lang, $encoding);
    return true;
  }

 /**
  * Initialize LdapSaisie
  *
  * @param[in] $lang string|null     The lang (optional, default: see LSlang :: setLocale())
  * @param[in] $encoding string|null The encoding (optional, default: see LSlang :: setLocale())
  *
  * @retval boolean True if initialized, false otherwise
  */
  public static function initialize($lang=null, $encoding=null) {
    if (self :: $initialized)
      return true;
    try {
      if (!self :: startLSconfig()) {
        return;
      }

      self :: startLSerror();
      self :: startLSlog();
      self :: loadLScli();
      self :: startLStemplate();
      self :: startLSurl();

      if (php_sapi_name() != "cli")
        session_start();

      self :: startLSlang($lang, $encoding);

      self :: loadLSaddons();
      self :: loadLSauth();
    }
    catch (Exception $e) {
      die('LSsession : fail to initialize session. Error : '.$e->getMessage());
    }
    self :: $initialized = true;
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

    if(isset($_SESSION['LSsession']['dn']) && !isset($_GET['LSsession_recoverPassword'])) {
      LSlog :: debug('LSsession : existing session');
      // --------------------- Session existante --------------------- //
      self :: $topDn         = $_SESSION['LSsession']['topDn'];
      self :: $dn            = $_SESSION['LSsession']['dn'];
      self :: $rdn           = $_SESSION['LSsession']['rdn'];
      self :: $ldapServerId  = $_SESSION['LSsession']['ldapServerId'];
      self :: $tmp_file      = $_SESSION['LSsession']['tmp_file'];
      self :: $userLDAPcreds = $_SESSION['LSsession']['userLDAPcreds'];

      if ( self :: cacheLSprofiles() && !isset($_REQUEST['LSsession_refresh']) ) {
        self :: setLdapServer(self :: $ldapServerId);
        if (!LSauth :: start()) {
          LSlog :: error("LSsession :: startLSsession() : can't start LSauth -> stop");
          return;
        }
        self :: $LSprofiles   = $_SESSION['LSsession']['LSprofiles'];
        self :: $LSaccess   = $_SESSION['LSsession']['LSaccess'];
        self :: $LSaddonsViewsAccess   = $_SESSION['LSsession']['LSaddonsViewsAccess'];
        if (!self :: LSldapConnect())
          return;
      }
      else {
        self :: setLdapServer(self :: $ldapServerId);
        if (!LSauth :: start()) {
          LSlog :: error("LSsession :: startLSsession() : can't start LSauth -> stop");
          return;
        }
        if (!self :: LSldapConnect())
          return;
        self :: loadLSprofiles();
      }

      if ( self :: cacheSudDn() && (!isset($_REQUEST['LSsession_refresh'])) ) {
        self :: $_subDnLdapServer = ((isset($_SESSION['LSsession_subDnLdapServer']))?$_SESSION['LSsession_subDnLdapServer']:NULL);
      }

      if (!self :: loadLSobject(self :: $ldapServer['authObjectType'])) {
        return;
      }

      LStemplate :: assign('globalSearch', self :: globalSearch());

      if (isset($_GET['LSsession_logout'])) {
        // Trigger LSauth logout
        LSauth :: logout();

        // Delete temporaries files
        if (is_array($_SESSION['LSsession']['tmp_file'])) {
          self :: $tmp_file = $_SESSION['LSsession']['tmp_file'];
        }
        self :: deleteTmpFile();

        // Destroy local session
        unset($_SESSION['LSsession']);
        session_destroy();

        // Trigger LSauth after logout
        LSauth :: afterLogout();

        // Redirect user on home page
        LSurl :: redirect('index.php');
        return;
      }

      if ( !self :: cacheLSprofiles() || isset($_REQUEST['LSsession_refresh']) ) {
        self :: loadLSprofiles();
        self :: loadLSaccess();
        self :: loadLSaddonsViewsAccess();
        $_SESSION['LSsession']=self :: getContextInfos();
      }

      LStemplate :: assign('LSsession_username',self :: getLSuserObject() -> getDisplayName());

      if (isset ($_POST['LSsession_topDn']) && $_POST['LSsession_topDn']) {
        if (self :: validSubDnLdapServer($_POST['LSsession_topDn'])) {
          self :: $topDn = $_POST['LSsession_topDn'];
          $_SESSION['LSsession']['topDn'] = $_POST['LSsession_topDn'];
        } // end if
      } // end if

      return true;

    }
    else {
      // --------------------- Session inexistante --------------------- //
      if (isset($_GET['LSsession_recoverPassword'])) {
        session_destroy();
      }
      // Session inexistante
      if (isset($_POST['LSsession_ldapserver'])) {
        self :: setLdapServer($_POST['LSsession_ldapserver']);
      }
      else {
        self :: setLdapServer(0);
      }

      // Connexion au serveur LDAP
      if (self :: LSldapConnect()) {

        // topDn
        if (isset($_POST['LSsession_topDn']) && $_POST['LSsession_topDn'] != '' ){
          self :: $topDn = $_POST['LSsession_topDn'];
        }
        else {
          self :: $topDn = self :: $ldapServer['ldap_config']['basedn'];
        }
        $_SESSION['LSsession_topDn']=self :: $topDn;

        if (!LSauth :: start()) {
          LSlog :: error("LSsession :: startLSsession() : can't start LSauth -> stop");
          return;
        }

        if (isset($_GET['LSsession_recoverPassword'])) {
          $recoveryPasswordInfos = self :: recoverPasswd(
                                      $_REQUEST['LSsession_user'],
                                      $_GET['recoveryHash']
                                   );
        }
        else {
          $LSuserObject = LSauth :: forceAuthentication();
          if ($LSuserObject) {
            // Authentication successful
            self :: $LSuserObject = $LSuserObject;
            self :: $dn = $LSuserObject->getValue('dn');
            self :: $rdn = $LSuserObject->getValue('rdn');
            if (isset(self :: $ldapServer['useUserCredentials']) && self :: $ldapServer['useUserCredentials']) {
              self :: $userLDAPcreds = LSauth :: getLDAPcredentials($LSuserObject);
              if (!is_array(self :: $userLDAPcreds)) {
                LSerror :: addErrorCode('LSsession_14');
                self :: $userLDAPcreds = false;
                return;
              }
              if (!LSldap :: reconnectAs(self :: $userLDAPcreds['dn'],self :: $userLDAPcreds['pwd'])) {
                LSerror :: addErrorCode('LSsession_15');
                return;
              }
            }
            self :: loadLSprofiles();
            self :: loadLSaccess();
            self :: loadLSaddonsViewsAccess();
            LStemplate :: assign('LSsession_username',self :: getLSuserObject() -> getDisplayName());
            LStemplate :: assign('globalSearch', self :: globalSearch());
            $_SESSION['LSsession']=self :: getContextInfos();
            return true;
          }
        }
      }
      else {
        LSerror :: addErrorCode('LSsession_09');
      }

      if (self :: $ldapServerId) {
        LStemplate :: assign('ldapServerId',self :: $ldapServerId);
      }
      LStemplate :: assign('topDn',self :: $topDn);
      if (isset($_GET['LSsession_recoverPassword'])) {
        self :: displayRecoverPasswordForm($recoveryPasswordInfos);
      }
      elseif(LSauth :: displayLoginForm()) {
        self :: displayLoginForm();
      }
      else {
        self :: setTemplate('blank.tpl');
        LSerror :: addErrorCode('LSsession_10');
      }
      return;
    }
  }

 /**
  * Initialize a CLI session for LdapSaisie
  *
  * @retval boolean True if intialized, false otherwise.
  */
  public static function startCliLSsession() {
    if (php_sapi_name() != "cli") return;
    if (!self :: initialize()) return;
    if (!self :: loadLScli()) return;
    return True;
  }

  /**
   * Do recover password
   *
   * @param[in] $username string The submited username
   * @param[in] $recoveryHash string The submited recoveryHash
   *
   * @retval array The recoveryPassword infos for template
   **/
  private static function recoverPasswd($username,$recoveryHash) {
    $recoveryPasswordInfos=array();
    if ( self :: loadLSobject(self :: $ldapServer['authObjectType']) ) {
      $authobject = new self :: $ldapServer['authObjectType']();
      if (!empty($recoveryHash)) {
        $filter=Net_LDAP2_Filter::create(
          self :: $ldapServer['recoverPassword']['recoveryHashAttr'],
          'equals',
          $recoveryHash
        );
        $result = $authobject -> listObjects($filter,self :: $topDn,array('onlyAccessible' => false));
      }
      elseif (!empty($username)) {
        $result = $authobject -> searchObject(
                    $username,
                    self :: $topDn,
                    self :: $ldapServer['authObjectFilter'],
                    array('onlyAccessible' => false)
                  );
      }
      else {
        return $recoveryPasswordInfos;
      }

      $nbresult=count($result);

      if ($nbresult==0) {
        LSlog :: debug('LSsession :: recoverPasswd() : incorrect hash/username');
        LSerror :: addErrorCode('LSsession_06');
      }
      elseif ($nbresult>1) {
        LSlog :: debug("LSsession :: recoverPasswd() : duplicated user found with hash/username '$username'");
        LSerror :: addErrorCode('LSsession_07');
      }
      else {
        $rdn = $result[0] -> getValue('rdn');
        $username = $rdn[0];
        LSlog :: debug("LSsession :: recoverPasswd() : user found, username = '$username'");
        if (self :: $ldapServer['recoverPassword']) {
          if (self :: loadLSaddon('mail')) {
            LSlog :: debug("LSsession :: recoverPasswd() : start recovering password");
            $user=$result[0];
            $emailAddress = $user -> getValue(self :: $ldapServer['recoverPassword']['mailAttr']);
            $emailAddress = $emailAddress[0];

            if (checkEmail($emailAddress)) {
              LSlog :: debug("LSsession :: recoverPasswd() : Email = '$emailAddress'");
              self :: $dn = $user -> getDn();

              // 1ère étape : envoie du recoveryHash
              if (empty($recoveryHash)) {
                $hash=self :: recoverPasswdFirstStep($user);
                if ($hash) {
                  if (self :: recoverPasswdSendMail($emailAddress,1,$hash)) {
                    // Mail a bien été envoyé
                    $recoveryPasswordInfos['recoveryHashMail']=$emailAddress;
                  }
                }
              }
              // 2nd étape : génération du mot de passe + envoie par mail
              else {
                $pwd=self :: recoverPasswdSecondStep($user);
                if ($pwd) {
                  if (self :: recoverPasswdSendMail($emailAddress,2,$pwd)){
                    // Mail a bien été envoyé
                    $recoveryPasswordInfos['newPasswordMail']=$emailAddress;
                  }
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
    }
    return $recoveryPasswordInfos;
  }

  /**
   * Send recover password mail
   *
   * @param[in] $mail string The user's mail
   * @param[in] $step integer The step
   * @param[in] $info string The info for formatted message
   *
   * @retval boolean True on success or False
   **/
  private static function recoverPasswdSendMail($mail,$step,$info) {
    // Header des mails
    $sendParams=array();
    if (self :: $ldapServer['recoverPassword']['recoveryEmailSender']) {
      $sendParams['From']=self :: $ldapServer['recoverPassword']['recoveryEmailSender'];
    }

    if ($step==1) {
      if ($_SERVER['HTTPS']=='on') {
        $recovery_url='https://';
      }
      else {
        $recovery_url='http://';
      }
      $recovery_url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&recoveryHash='.$info;

      $subject = self :: $ldapServer['recoverPassword']['recoveryHashMail']['subject'];
      $msg = getFData(
        self :: $ldapServer['recoverPassword']['recoveryHashMail']['msg'],
        $recovery_url
      );
    }
    else {
      $subject = self :: $ldapServer['recoverPassword']['newPasswordMail']['subject'];
      $msg = getFData(
        self :: $ldapServer['recoverPassword']['newPasswordMail']['msg'],
        $info
      );
    }

    if (!sendMail($mail,$subject,$msg,$sendParams)) {
      LSlog :: debug("LSsession :: recoverPasswdSendMail($mail, $step) : error sending email.");
      LSerror :: addErrorCode('LSsession_20',4);
      return;
    }
    return true;
  }


  /**
   * Do first step of recovering password
   *
   * @param[in] $user LSldapObject The LSldapObject of the user
   *
   * @retval string|False The recory hash on success or False
   **/
  private static function recoverPasswdFirstStep($user) {
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
        return $recovery_hash;
      }
      else {
        // Erreur durant la mise à jour de l'objet
        LSlog :: error("LSsession :: recoverPasswdFirstStep($user) : error updating user.");
        LSerror :: addErrorCode('LSsession_20',6);
      }
    }
    else {
      // Erreur durant la validation du formulaire de modification de perte de password
      LSlog :: error("LSsession :: recoverPasswdFirstStep($user) : error validating form.");
      LSerror :: addErrorCode('LSsession_20',5);
    }
    return;
  }

  /**
   * Do second step of recovering password
   *
   * @param[in] $user LSldapObject The LSldapObject of the user
   *
   * @retval string|False The new password on success or False
   **/
  private static function recoverPasswdSecondStep($user) {
    $attr = $user -> attrs[self :: $ldapServer['authObjectTypeAttrPwd']];
    if ($attr instanceof LSattribute) {
      $mdp = generatePassword(
       $attr -> config['html_options']['chars'],
       $attr -> config['html_options']['lenght']
      );
      LSlog :: debug("LSsession :: recoverPasswdSecondStep($user) : new password = '$mdp'.");
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
          return $mdp;
        }
        else {
          // Erreur durant la mise à jour de l'objet
          LSlog :: error("LSsession :: recoverPasswdSecondStep($user) : error updating user.");
          LSerror :: addErrorCode('LSsession_20',3);
        }
      }
      else {
        // Erreur durant la validation du formulaire de modification de perte de password
        LSlog :: error("LSsession :: recoverPasswdSecondStep($user) : error validating form.");
        LSerror :: addErrorCode('LSsession_20',2);
      }
    }
    else {
      // l'attribut password n'existe pas
      LSlog :: error("LSsession :: recoverPasswdSecondStep($user) : password attribute '$attr' does not exists.");
      LSerror :: addErrorCode('LSsession_20',1);
    }
    return;
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
      'userLDAPcreds' => self :: $userLDAPcreds,
      'ldapServerId' => self :: $ldapServerId,
      'ldapServer' => self :: $ldapServer,
      'LSprofiles' => self :: $LSprofiles,
      'LSaccess' => self :: $LSaccess,
      'LSaddonsViewsAccess' => self :: $LSaddonsViewsAccess
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
      self :: loadLSaddonsViewsAccess();
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
  * le tableau LSconfig :: get('ldap_servers').
  *
  * @param[in] integer Index du serveur Ldap
  *
  * @retval boolean True sinon false.
  */
  public static function setLdapServer($id) {
    $conf = LSconfig :: get("ldap_servers.$id");
    if ( is_array($conf) ) {
      self :: $ldapServerId = $id;
      self :: $ldapServer = $conf;
      LSlang :: setLocale();
      self :: setGlobals();
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
      self :: includeFile(LSconfig :: get('NetLDAP2'), true);
      if (!self :: loadLSclass('LSldap')) {
        return;
      }
      if (self :: $dn && isset(self :: $ldapServer['useUserCredentials']) && self :: $ldapServer['useUserCredentials']) {
        LSldap :: reconnectAs(self :: $userLDAPcreds['dn'], self :: $userLDAPcreds['pwd'],self :: $ldapServer['ldap_config']);
      }
      else {
        LSldap :: connect(self :: $ldapServer['ldap_config']);
      }
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
   * Use this function to know if subDn is enabled for the curent LdapServer
   *
   * @retval boolean
   **/
  public static function subDnIsEnabled() {
    if (!isset(self :: $ldapServer['subDn'])) {
      return;
    }
    if ( !is_array(self :: $ldapServer['subDn']) ) {
      return;
    }
    return true;
  }

 /**
  * Retourne les sous-dns du serveur Ldap courant
  *
  * @retval mixed Tableau des subDn, false si une erreur est survenue.
  */
  public static function getSubDnLdapServer($login=false) {
    $login=(bool)$login;
    if (self :: cacheSudDn() && isset(self :: $_subDnLdapServer[self :: $ldapServerId][$login])) {
      return self :: $_subDnLdapServer[self :: $ldapServerId][$login];
    }
    if (!self::subDnIsEnabled()) {
      return;
    }
    $return=array();
    foreach(self :: $ldapServer['subDn'] as $subDn_name => $subDn_config) {
      if ($login && isset($subDn_config['nologin']) && $subDn_config['nologin']) continue;
      if ($subDn_name == 'LSobject') {
        if (is_array($subDn_config)) {
          foreach($subDn_config as $LSobject_name => $LSoject_config) {
            if (isset($LSoject_config['basedn']) && !empty($LSoject_config['basedn'])) {
              $basedn = $LSoject_config['basedn'];
            }
            else {
              $basedn = self::getRootDn();
            }
            if (isset($LSoject_config['displayName']) && !empty($LSoject_config['displayName'])) {
              $displayName = $LSoject_config['displayName'];
            }
            else {
              $displayName = NULL;
            }
            $sparams = array();
            $sparams['onlyAccessible'] = (isset($LSoject_config['onlyAccessible'])?$LSoject_config['onlyAccessible']:False);
            if( self :: loadLSobject($LSobject_name) ) {
              if ($subdnobject = new $LSobject_name()) {
                $tbl_return = $subdnobject -> getSelectArray(NULL,$basedn,$displayName,false,false,NULL,$sparams);
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
          $return[$subDn_config['dn']] = __($subDn_name);
        }
      }
    }
    if (self :: cacheSudDn()) {
      self :: $_subDnLdapServer[self :: $ldapServerId][$login]=$return;
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
  public static function getSortSubDnLdapServer($login=false) {
    $subDnLdapServer = self :: getSubDnLdapServer($login);
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
  public static function getSubDnLdapServerOptions($selected=NULL,$login=false) {
    $list = self :: getSubDnLdapServer($login);
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
    LStemplate :: assign('pagetitle',_('Connection'));
    if (isset($_GET['LSsession_logout'])) {
      LStemplate :: assign('loginform_action','index.php');
    }
    else {
      LStemplate :: assign('loginform_action',$_SERVER['REQUEST_URI']);
    }
    if (count(LSconfig :: get('ldap_servers'))==1) {
      LStemplate :: assign('loginform_ldapserver_style','style="display: none"');
    }
    LStemplate :: assign('loginform_label_ldapserver',_('LDAP server'));
    $ldapservers_name=array();
    $ldapservers_index=array();
    foreach(LSconfig :: get('ldap_servers') as $id => $infos) {
      $ldapservers_index[]=$id;
      $ldapservers_name[]=__($infos['name']);
    }
    LStemplate :: assign('loginform_ldapservers_name',$ldapservers_name);
    LStemplate :: assign('loginform_ldapservers_index',$ldapservers_index);

    LStemplate :: assign('loginform_label_level',_('Level'));
    LStemplate :: assign('loginform_label_user',_('Identifier'));
    LStemplate :: assign('loginform_label_pwd',_('Password'));
    LStemplate :: assign('loginform_label_submit',_('Connect'));
    LStemplate :: assign('loginform_label_recoverPassword',_('Forgot your password ?'));

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
    LStemplate :: assign('pagetitle',_('Recovery of your credentials'));
    LStemplate :: assign('recoverpasswordform_action','index.php?LSsession_recoverPassword');

    if (count(LSconfig :: get('ldap_servers'))==1) {
      LStemplate :: assign('recoverpasswordform_ldapserver_style','style="display: none"');
    }

    LStemplate :: assign('recoverpasswordform_label_ldapserver',_('LDAP server'));
    $ldapservers_name=array();
    $ldapservers_index=array();
    foreach(LSconfig :: get('ldap_servers') as $id => $infos) {
      $ldapservers_index[]=$id;
      $ldapservers_name[]=$infos['name'];
    }
    LStemplate :: assign('recoverpasswordform_ldapservers_name',$ldapservers_name);
    LStemplate :: assign('recoverpasswordform_ldapservers_index',$ldapservers_index);

    LStemplate :: assign('recoverpasswordform_label_user',_('Identifier'));
    LStemplate :: assign('recoverpasswordform_label_submit',_('Validate'));
    LStemplate :: assign('recoverpasswordform_label_back',_('Back'));

    $recoverpassword_step = 'start';
    $recoverpassword_msg = _('Please fill the identifier field to proceed recovery procedure');

    if (isset($recoveryPasswordInfos['recoveryHashMail'])) {
      $recoverpassword_step = 'token_sent';
      $recoverpassword_msg = getFData(
        _("An email has been sent to  %{mail}. " .
        "Please follow the instructions on it."),
        $recoveryPasswordInfos['recoveryHashMail']
      );
    }

    if (isset($recoveryPasswordInfos['newPasswordMail'])) {
      $recoverpassword_step = 'new_password_sent';
      $recoverpassword_msg = getFData(
        _("Your new password has been sent to %{mail}."),
        $recoveryPasswordInfos['newPasswordMail']
      );
    }

    LStemplate :: assign('recoverpassword_step',$recoverpassword_step);
    LStemplate :: assign('recoverpassword_msg',$recoverpassword_msg);

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
   * Add a JS script to load on page
   *
   * @param[in] $file string The JS filename
   * @param[in] $path string|null The sub-directory path that contain this file.
   *                              Keep for retro-compatibility : you could just
   *                              prefix the file name.
   *
   * @retval void
   */
  public static function addJSscript($file, $path=NULL) {
    if ($path)
      $file = $path.$file;
    if (!in_array($file, self :: $JSscripts))
      self :: $JSscripts[] = $file;
  }

  /**
   * Add a library JS file to load on page
   *
   * @param[in] $file string The JS filename
   *
   * @retval void
   */
  public static function addLibJSscript($file) {
    if (!in_array($file, self :: $LibsJSscripts))
      self :: $LibsJSscripts[] = $file;
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
  * Add a CSS file to load on page
  *
  * @param[in] $file string The CSS filename
  * @param[in] $path string|null The sub-directory path that contain this file.
  *                              Keep for retro-compatibility : you could just
  *                              prefix the file name.
  *
  * @retval void
  */
  public static function addCssFile($file, $path=NULL) {
    if ($path)
      $file = $path.$file;
    if (!in_array($file, self :: $CssFiles))
      self :: $CssFiles[] = $file;
  }

 /**
  * Add a library CSS file to load on page
  *
  * @param[in] $file string The CSS filename
  *
  * @retval void
  */
  public static function addLibCssFile($file) {
    if (!in_array($file, self :: $LibsCssFiles))
      self :: $LibsCssFiles[] = $file;
  }

 /**
  * Affiche le template Smarty
  *
  * Charge les dÃ©pendances et affiche le template Smarty
  *
  * @retval void
  */
  public static function displayTemplate() {
    $KAconf = LSconfig :: get('keepLSsessionActive');
    if (
          (
            (!isset(self :: $ldapServer['keepLSsessionActive']))
            &&
            (!($KAconf === false))
          )
          ||
          (self :: $ldapServer['keepLSsessionActive'])
        ) {
      self :: addJSconfigParam('keepLSsessionActive',ini_get('session.gc_maxlifetime'));
    }

    LStemplate :: assign('LSjsConfig',base64_encode(json_encode(self :: $_JSconfigParams)));

    // JS files
    $JSscripts = array();
    if (isset($GLOBALS['defaultJSscipts']) && is_array($GLOBALS['defaultJSscipts']))
      foreach ($GLOBALS['defaultJSscipts'] as $script)
        if (!in_array($script, $JSscripts))
          $JSscripts[] = $script;

    foreach (self :: $JSscripts as $script)
      if (!in_array($script, $JSscripts))
        $JSscripts[] = $script;
    LStemplate :: assign('JSscripts', $JSscripts);
    LStemplate :: assign('LibsJSscripts', self :: $LibsJSscripts);
    LStemplate :: assign('LSdebug', boolval(LSdebug));

    // CSS files
    self :: addCssFile("LSdefault.css");
    if (isset($GLOBALS['defaultCSSfiles']) && is_array($GLOBALS['defaultCSSfiles']))
      foreach ($GLOBALS['defaultCSSfiles'] as $file)
        if (!in_array($script, self :: $CssFiles))
          self :: addCssFile($file);
    LStemplate :: assign('CssFiles', self :: $CssFiles);
    LStemplate :: assign('LibsCssFiles', self :: $LibsCssFiles);

    // Access
    LStemplate :: assign('LSaccess', self :: getLSaccess());
    LStemplate :: assign('LSaddonsViewsAccess',self :: $LSaddonsViewsAccess);

    // Niveau
    $listTopDn = self :: getSubDnLdapServer();
    if (is_array($listTopDn)) {
      asort($listTopDn);
      LStemplate :: assign('label_level',self :: getSubDnLabel());
      LStemplate :: assign('_refresh',_('Refresh'));
      $LSsession_topDn_index = array();
      $LSsession_topDn_name = array();
      foreach($listTopDn as $index => $name) {
        $LSsession_topDn_index[]  = $index;
        $LSsession_topDn_name[]   = $name;
      }
      LStemplate :: assign('LSsession_subDn_indexes',$LSsession_topDn_index);
      LStemplate :: assign('LSsession_subDn_names',$LSsession_topDn_name);
      LStemplate :: assign('LSsession_subDn',self :: $topDn);
      LStemplate :: assign('LSsession_subDnName',self :: getSubDnName());
    }

    LStemplate :: assign('LSlanguages', LSlang :: getLangList());
    LStemplate :: assign('LSlang', LSlang :: getLang());
    LStemplate :: assign('LSencoding', LSlang :: getEncoding());

    LStemplate :: assign('displayLogoutBtn',LSauth :: displayLogoutBtn());
    LStemplate :: assign('displaySelfAccess',LSauth :: displaySelfAccess());

    // Infos
    if((!empty($_SESSION['LSsession_infos']))&&(is_array($_SESSION['LSsession_infos']))) {
      LStemplate :: assign('LSinfos',$_SESSION['LSsession_infos']);
      $_SESSION['LSsession_infos']=array();
    }

    if (self :: $ajaxDisplay) {
      LStemplate :: assign('LSerror_txt',LSerror :: getErrors());
      LStemplate :: assign('LSdebug_txt',LSdebug_print(true));
    }
    else {
      LSerror :: display();
      LSdebug_print();
    }
    if (!self :: $template)
      self :: setTemplate('empty.tpl');

    LStemplate :: assign('connected_as',_("Connected as"));

    LStemplate :: display(self :: $template);
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
      $data['LSdebug'] = LSdebug_print(true,false);
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
      LStemplate :: assign($name,$val);
    }
    return LStemplate :: fetch($template);
  }

  /**
   * Prend un tableau de LSobject et le réduit en utilisant un filtre de
   * recherche sur un autre type de LSobject.
   *
   * Si une erreur est présente dans le tableau de définition du filtre, un
   * tableau vide est renvoyé.
   *
   * @param[in] string $LSobject le type LSobject par défaut
   * @param[in] array $set tableau de LSobject
   * @param[in] array $filter_def définition du filtre de recherche pour la réduction
   * @param[in] string $basend basedn pour la recherche, null par défaut
   *
   * @retval array le nouveau tableau de LSobject
   */
  private static function reduceLdapSet($LSobject, $set, $filter_def, $basedn=null) {
    if (empty($set)) {
      return array();
    }

    if (! isset($filter_def['filter']) &&
          (! isset($filter_def['attr']) ||
           ! isset($filter_def['attr_value']))) {
      LSlog :: debug("LSsession :: reduceLdapSet() : LSobject LSprofil filter invalid : " . varDump($filter_def));
      return array();
    }

    LSlog :: debug('LSsession :: reduceLdapSet() : reducing set of');
    foreach ($set as $object) {
      LSdebug('LSsession :: -> ' . $object -> getDn());
    }

    $LSobject = isset($filter_def['LSObject']) ? $filter_def['LSobject'] : $LSobject;
    LSlog :: debug('LSsession :: reduceLdapSet() : LSobject = ' . $LSobject);
    $filters = array();
    foreach ($set as $object) {
      if (isset($filter_def['filter'])) {
        $filters[] = $object -> getFData($filter_def['filter']);
      }
      else {
        $value = $object -> getFData($filter_def['attr_value']);
        $filters[] = Net_LDAP2_Filter::create($filter_def['attr'], 'equals', $value);
      }
    }
    $filter = LSldap::combineFilters('or', $filters);
    $params = array(
      'basedn' => isset($filter_def['basedn']) ? $filter_def['basedn'] : $basedn,
      'filter' => $filter,
      'onlyAccessible' => False
    );
    if (isset($filter_def['params']) && is_array($filter_def['params'])) {
      $params = array_merge($filter_def['params'],$params);
    }
    $LSsearch = new LSsearch($LSobject,'LSsession :: loadLSprofiles',$params,true);
    $LSsearch -> run(false);

    $set = $LSsearch -> listObjects();
    LSlog :: debug('LSsession :: reduceLdapSet() : reduced set to');
    foreach ($set as $object) {
      LSlog :: debug('LSsession :: reduceLdapSet() : -> ' . $object -> getDn());
    }
    return $set;
  }

  /**
   * Charge les droits LS de l'utilisateur : uniquement du type LSobjects
   *
   * @param[in] string $
   *
   * @retval void
   */
  private static function loadLSprofilesLSobjects($profile, $LSobject, $listInfos) {
    if (! self :: loadLSclass('LSsearch')) {
      LSlog :: error('Fail to load class LSsearch');
      return;
    }
    # we are gonna grow a set of objects progressively, we start from the user
    $set = array(self :: getLSuserObject());
    $basedn = isset($listInfos['basedn']) ? $listInfos['basedn'] : null;
    $LSobject = isset($listInfos['LSobject']) ? $listInfos['LSobject'] : $LSobject;

    if (isset($listInfos['filters']) && is_array($listInfos['filters'])) {
      foreach ($listInfos['filters'] as $filter_def) {
        $set = self :: reduceLdapSet($LSobject, $set, $filter_def, $basedn);
      }
    }
    if (isset($listInfos['filter']) || (isset($listInfos['attr']) && isset($listInfos['attr_value']))) {
      # support legacy profile definition
      $set = self :: reduceLdapSet($LSobject, $set, $listInfos, $basedn);
    }

    $DNs = [];
    foreach ($set as $object) {
      $DNs[] = $object -> getDn();
    }
    if (!is_array(self :: $LSprofiles[$profile])) {
      self :: $LSprofiles[$profile]=$DNs;
    }
    else {
      foreach($DNs as $dn) {
        if (!in_array($dn,self :: $LSprofiles[$profile])) {
          self :: $LSprofiles[$profile][] = $dn;
        }
      }
    }
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
            // Do not handle 'label' key as a topDn
            if ($topDn == 'label') {
              continue;
            }
            /*
             * If $topDn == 'LSobject', we search for each LSobject type to find
             * all items on witch the user will have powers.
             */
            elseif ($topDn == 'LSobjects') {
              if (is_array($rightsInfos)) {
                foreach ($rightsInfos as $LSobject => $listInfos) {
                  LSlog :: debug('LSsession :: loadLSprofiles() : loading LSprofile ' . $profile . ' for LSobject ' . $LSobject . ' with params ' . var_export($listInfos, true));
                  self :: loadLSprofilesLSobjects($profile, $LSobject, $listInfos);
                }
              }
              else {
                LSlog :: warning('LSsession :: loadLSprofiles() : LSobjects => [] must be an array');
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
                          LSlog :: warning("LSsession :: loadLSprofiles() : fail to load DN '$dn'.");
                        }
                      }
                      else {
                        LSlog :: warning("LSsession :: loadLSprofiles() : fail to instanciate LSobject type '".$conf['LSobject']."'.");
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
      LSlog :: debug("LSsession :: loadLSprofiles() : LSprofiles = ".print_r(self :: $LSprofiles,1));
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
    if (isset(self :: $ldapServer['subDn']) && is_array(self :: $ldapServer['subDn'])) {
      foreach(self :: $ldapServer['subDn'] as $name => $config) {
        if ($name=='LSobject') {
          if (is_array($config)) {

            // Définition des subDns
            foreach($config as $objectType => $objectConf) {
              if (self :: loadLSobject($objectType)) {
                if ($subdnobject = new $objectType()) {
                  $tbl = $subdnobject -> getSelectArray(NULL,self::getRootDn(),NULL,NULL,false,NULL,array('onlyAccessible' => False));
                  if (is_array($tbl)) {
                    // Définition des accès
                    $access=array();
                    if (is_array($objectConf['LSobjects'])) {
                      foreach($objectConf['LSobjects'] as $type) {
                        if (self :: loadLSobject($type)) {
                          if (self :: canAccess($type)) {
                            $access[$type] = LSconfig :: get('LSobjects.'.$type.'.label');
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
                    $access[$objectType] = LSconfig :: get('LSobjects.'.$objectType.'.label');
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
                $access[$objectType] = LSconfig :: get('LSobjects.'.$objectType.'.label');
            }
          }
        }
        $LSaccess[self :: $topDn] = $access;
      }
    }
    if (LSauth :: displaySelfAccess()) {
      foreach($LSaccess as $dn => $access) {
        $LSaccess[$dn] = array_merge(
          array(
            'SELF' => 'My account'
          ),
          $access
        );
      }
    }
    self :: $LSaccess = $LSaccess;
    $_SESSION['LSsession']['LSaccess'] = $LSaccess;
  }

 /**
  * Get user access
  *
  * @param[in] $topDn string Top DN (optional, default : current)
  *
  * @retval array User's access
  **/
  public static function getLSaccess($topDn=null) {
    if (is_null($topDn)) $topDn = self :: $topDn;
    if (isset(self :: $LSaccess[self :: $topDn])) {
      return self :: $LSaccess[self :: $topDn];
    }
    return array();
  }

  /**
   * Load user access to LSaddons views
   *
   * @retval void
   */
  private static function loadLSaddonsViewsAccess() {
    $LSaddonsViewsAccess=array();
    foreach (self :: $LSaddonsViews as $addon => $conf) {
      foreach ($conf as $viewId => $viewConf) {
        if (self :: canAccessLSaddonView($addon,$viewId)) {
          $LSaddonsViewsAccess["$addon::$viewId"]=array (
            'LSaddon' => $addon,
            'id' => $viewId,
            'label' => $viewConf['label'],
            'showInMenu' => $viewConf['showInMenu']
          );
        }
      }
    }
    self :: $LSaddonsViewsAccess = $LSaddonsViewsAccess;
    $_SESSION['LSsession']['LSaddonsViewsAccess'] = $LSaddonsViewsAccess;
  }


  /**
   * Dit si l'utilisateur est du profil pour le DN spécifié
   *
   * @param[in] string $dn DN de l'objet
   * @param[in] string $profile Profil
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
   * Dit si l'utilisateur est d'au moins un des profils pour le DN spécifié
   *
   * @param[in] string $dn DN de l'objet
   * @param[in] string $profiles Profils
   *
   * @retval boolean True si l'utilisateur est d'au moins un profil sur l'objet, false sinon.
   */
  public static function isLSprofiles($dn,$profiles) {
    foreach ($profiles as $profile) {
      if (self :: isLSprofile($dn,$profile))
        return true;
    }
    return false;
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

    // Access always granted in CLI mode
    if (php_sapi_name() == "cli")
      return true;

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
        if (!self :: in_menu($LSobject,$obj -> subDnValue)) {
          return;
        }
      }
    }
    else {
      $objectdn=LSconfig :: get('LSobjects.'.$LSobject.'.container_dn').','.self :: $topDn;
      $whoami = self :: whoami($objectdn);
    }

    // Pour un attribut particulier
    if ($attr) {
      if ($attr=='rdn') {
        $attr=LSconfig :: get('LSobjects.'.$LSobject.'.rdn');
      }
      if (!is_array(LSconfig :: get('LSobjects.'.$LSobject.'.attrs.'.$attr))) {
        return;
      }

      $r = 'n';
      foreach($whoami as $who) {
        $nr = LSconfig :: get('LSobjects.'.$LSobject.'.attrs.'.$attr.'.rights.'.$who);
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
    $attrs_conf=LSconfig :: get('LSobjects.'.$LSobject.'.attrs');
    if (is_array($attrs_conf)) {
      if (($right=='r')||($right=='w')) {
        foreach($whoami as $who) {
          foreach ($attrs_conf as $attr_name => $attr_config) {
            if (isset($attr_config['rights'][$who]) && $attr_config['rights'][$who]==$right) {
              return true;
            }
          }
        }
      }
      else {
        foreach($whoami as $who) {
          foreach ($attrs_conf as $attr_name => $attr_config) {
            if ( (isset($attr_config['rights'][$who])) && ( ($attr_config['rights'][$who]=='r') || ($attr_config['rights'][$who]=='w') ) ) {
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
    if (!self :: loadLSobject($LSobject)) {
      return;
    }
    if (LSconfig :: get("LSobjects.$LSobject.disable_creation")) {
      return;
    }
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
    $relConf=LSconfig :: get('LSobjects.'.$LSobject.'.LSrelation.'.$relationName);
    if (!is_array($relConf))
      return;

    // Access always granted in CLI mode
    if (php_sapi_name() == "cli")
      return true;

    $whoami = self :: whoami($dn);

    if (($right=='w') || ($right=='r')) {
      $r = 'n';
      foreach($whoami as $who) {
        $nr = ((isset($relConf['rights'][$who]))?$relConf['rights'][$who]:'');
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
        if ((isset($relConf['rights'][$who])) && ( ($relConf['rights'][$who] == 'w') || ($relConf['rights'][$who] == 'r') ) ) {
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
   * Retourne le droit de l'utilisateur a executer une customAction
   *
   * @param[in] string $dn Le DN de l'objet
   * @param[in] string $LSobject Le type de l'objet
   * @param[in] string $customActionName Le nom de la customAction
   *
   * @retval boolean True si l'utilisateur peut executer cette customAction, false sinon
   */
  public static function canExecuteCustomAction($dn,$LSobject,$customActionName) {
    $conf=LSconfig :: get('LSobjects.'.$LSobject.'.customActions.'.$customActionName);
    if (!is_array($conf))
      return;

    // Access always granted in CLI mode
    if (php_sapi_name() == "cli")
      return true;

    $whoami = self :: whoami($dn);

    if (isset($conf['rights']) && is_array($conf['rights'])) {
      foreach($whoami as $who) {
        if (in_array($who,$conf['rights'])) {
          return True;
        }
      }
    }

    return;
  }

  /**
   * Retourne le droit de l'utilisateur a executer une customAction
   * sur une recherche
   *
   * @param[in] string $LSsearch L'objet LSsearch
   * @param[in] string $customActionName Le nom de la customAction
   *
   * @retval boolean True si l'utilisateur peut executer cette customAction, false sinon
   */
  public static function canExecuteLSsearchCustomAction($LSsearch,$customActionName) {
    $conf=LSconfig :: get('LSobjects.'.$LSsearch -> LSobject.'.LSsearch.customActions.'.$customActionName);
    if (!is_array($conf))
      return;

    // Access always granted in CLI mode
    if (php_sapi_name() == "cli")
      return true;

    $dn=$LSsearch -> basedn;
    if (is_null($dn)) $dn=self::getTopDn();

    $whoami = self :: whoami($dn);

    if (isset($conf['rights']) && is_array($conf['rights'])) {
      foreach($whoami as $who) {
        if (in_array($who,$conf['rights'])) {
          return True;
        }
      }
    }

    return;
  }

  /**
   * Return user right to access to a LSaddon view
   *
   * @param[in] string $LSaddon The LSaddon
   * @param[in] string $viewId The LSaddon view ID
   *
   * @retval boolean True if user is allowed, false otherwise
   */
  public static function canAccessLSaddonView($LSaddon,$viewId) {
    if (self :: loadLSaddon($LSaddon)) {
      if (!isset(self :: $LSaddonsViews[$LSaddon]) || !isset(self :: $LSaddonsViews[$LSaddon][$viewId]))
      return;
      if (!is_array(self :: $LSaddonsViews[$LSaddon][$viewId]['allowedLSprofiles'])) {
        return true;
      }
      $whoami = self :: whoami(self :: $topDn);

      if (isset(self :: $LSaddonsViews[$LSaddon][$viewId]['allowedLSprofiles']) && is_array(self :: $LSaddonsViews[$LSaddon][$viewId]['allowedLSprofiles'])) {
        foreach($whoami as $who) {
          if (in_array($who,self :: $LSaddonsViews[$LSaddon][$viewId]['allowedLSprofiles'])) {
            return True;
          }
        }
      }
    }
    return;
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
      $img_path = LS_TMP_DIR_PATH .rand().'.tmp';
      $fp = fopen($img_path, "w");
      fwrite($fp, $value);
      fclose($fp);
      self :: addTmpFile($value, $img_path);
      return $img_path;
    }
    else {
      return $exist;
    }
  }

  /**
   * Retourne l'URL du fichier temporaire
   *
   * Retourne l'URL du fichier temporaire qu'il créera à partir de la valeur
   * s'il n'existe pas déjà .
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $value La valeur du fichier
   *
   * @retval mixed
   **/
  public static function getTmpFileURL($value) {
    $path = self :: getTmpFile($value);
    if (substr($path, 0, strlen(LS_ROOT_DIR)) == LS_ROOT_DIR)
      return substr($path, strlen(LS_ROOT_DIR)+1);
    return False;
  }

  /**
   * Retourne le chemin du fichier temporaire à partir du nom du fichier (s'il existe)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $hash La valeur du fichier
   *
   * @retval mixed
   **/
  public static function getTmpFileByFilename($filename) {
    foreach(self :: $tmp_file as $filePath => $contentHash) {
      if (basename($filePath) == $filename) {
        return $filePath;
      }
    }
    return False;
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
    return ( (LSconfig :: get('cacheLSprofiles')) || (self :: $ldapServer['cacheLSprofiles']) );
  }

  /**
   * Retourne true si le cache des subDn est activé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True si le cache des subDn est activé, false sinon.
   */
  public static function cacheSudDn() {
    return ( (LSconfig :: get('cacheSubDn')) || (self :: $ldapServer['cacheSubDn']));
  }

  /**
   * Retourne true si le cache des recherches est activé
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True si le cache des recherches est activé, false sinon.
   */
  public static function cacheSearch() {
    return ( (LSconfig :: get('cacheSearch')) || (self :: $ldapServer['cacheSearch']));
  }

  /**
   * Return true if global search is enabled
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if global search is enabled, false instead
   */
  public static function globalSearch() {
    return LSconfig :: get('globalSearch', LSconfig :: get('globalSearch', true, 'bool'), 'bool', self :: $ldapServer);
  }

  /**
   * Retourne le label des niveaux pour le serveur ldap courant
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string Le label des niveaux pour le serveur ldap dourant
   */
  public static function getSubDnLabel() {
    return (self :: $ldapServer['subDnLabel']!='')?__(self :: $ldapServer['subDnLabel']):_('Level');
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
    if (self :: getSubDnLdapServer(false)) {
      if (isset(self :: $_subDnLdapServer[self :: $ldapServerId][false][$subDn])) {
        return self :: $_subDnLdapServer[self :: $ldapServerId][false][$subDn];
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
    if (isset(self :: $ldapServer['subDn']['LSobject']) && is_array(self :: $ldapServer['subDn']['LSobject'])) {
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
    return (isset(self :: $ldapServer['subDn']) && is_array(self :: $ldapServer['subDn']));
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
   * Redirect user to another URL
   *
   * /!\ DEPRECATED /!\ : please use LSurl :: redirect()
   *
   * @param[in] $url string The destination URL
   * @param[in] $exit boolean Unsed (keep for reto-compatibility)
   *
   * @retval void
   */
  public static function redirect($url, $exit=true) {
    LSerror :: addErrorCode('LSsession_27');
    LSurl :: redirect($url);
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
   * Redirect to default view (if defined)
   *
   * @retval void
   */
  public static function redirectToDefaultView($force=false) {
    if (isset(self :: $ldapServer['defaultView'])) {
      if (array_key_exists(self :: $ldapServer['defaultView'], self :: $LSaccess[self :: $topDn])) {
        LSurl :: redirect('object/'.self :: $ldapServer['defaultView']);
      }
      elseif (array_key_exists(self :: $ldapServer['defaultView'], self :: $LSaddonsViewsAccess)) {
        $addon = self :: $LSaddonsViewsAccess[self :: $ldapServer['defaultView']];
        LSurl :: redirect('addon/'.urlencode(self :: $LSaddonsViewsAccess[self :: $ldapServer['defaultView']]['LSaddon'])."/".urlencode(self :: $LSaddonsViewsAccess[self :: $ldapServer['defaultView']]['id']));
      }
    }
    if ($force)
     LSurl :: redirect('index.php');
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
      if (isset(self :: $_JSconfigParams['helpInfos'][$group]) && is_array(self :: $_JSconfigParams['helpInfos'][$group])) {
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
    _("LSsession : LDAP server's configuration data are invalid. Can't connect.")
    );
    LSerror :: defineError('LSsession_04',
    _("LSsession : Failed to load LSobject type %{type} : unknon type.")
    );
    LSerror :: defineError('LSsession_05',
    _("LSsession : Failed to load LSclass %{class}.")
    );
    LSerror :: defineError('LSsession_06',
    _("LSsession : Login or password incorrect.")
    );
    LSerror :: defineError('LSsession_07',
    _("LSsession : Impossible to identify you : Duplication of identities.")
    );
    LSerror :: defineError('LSsession_08',
    _("LSsession : Can't load class of authentification (%{class}).")
    );
    LSerror :: defineError('LSsession_09',
    _("LSsession : Can't connect to LDAP server.")
    );
    LSerror :: defineError('LSsession_10',
    _("LSsession : Impossible to authenticate you.")
    );
    LSerror :: defineError('LSsession_11',
    _("LSsession : Your are not authorized to do this action.")
    );
    LSerror :: defineError('LSsession_12',
    _("LSsession : Some informations are missing to display this page.")
    );
    LSerror :: defineError('LSsession_13',
    _("LSsession : The function of the custom action %{name} does not exists or is not configured.")
    );
    LSerror :: defineError('LSsession_14',
    _("LSsession : Fail to retreive user's LDAP credentials from LSauth.")
    );
    LSerror :: defineError('LSsession_15',
    _("LSsession : Fail to reconnect to LDAP server with user's LDAP credentials.")
    );
    LSerror :: defineError('LSsession_16',
    _("LSsession : No import/export format define for this object type.")
    );
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
    LSerror :: defineError('LSsession_21',
    _("LSsession : call function %{func} do not provided from LSaddon %{addon}.")
    );
    LSerror :: defineError('LSsession_22',
    _("LSsession : problem during initialisation.")
    );
    LSerror :: defineError('LSsession_23',
    _("LSsession : view function %{func} for LSaddon %{addon} doet not exist.")
    );
    LSerror :: defineError('LSsession_24',
    _("LSsession : invalid related object's DN pass in parameter.")
    );
    LSerror :: defineError('LSsession_25',
    _("LSsession : the LSaddon %{addon} keep using old-style addon view URL. Please upgrade it.")
    );
    LSerror :: defineError('LSsession_26',
    _("LSsession : You have been redirect from an old-style URL %{url}. Please upgrade this link.")
    );
    LSerror :: defineError('LSsession_27',
    _("LSsession : You still seen use LSsession :: redirect() in your custom code. Please upgrade it and use LSurl :: redirect().")
    );
  }

  /**
   * Ajax method when change ldapserver on login form
   *
   * @param[in] $data array The return data address
   *
   * @retval void
   **/
  public static function ajax_onLdapServerChangedLogin(&$data) {
    if ( isset($_REQUEST['server']) ) {
      self :: setLdapServer($_REQUEST['server']);
      $data = array();
      if ( self :: LSldapConnect() ) {
        if (session_id()=="") session_start();
        if (isset($_SESSION['LSsession_topDn'])) {
          $sel = $_SESSION['LSsession_topDn'];
        }
        else {
          $sel = NULL;
        }
        $list = self :: getSubDnLdapServerOptions($sel,true);
        if (is_string($list)) {
          $data['list_topDn'] = "<select name='LSsession_topDn' id='LSsession_topDn'>".$list."</select>";
          $data['subDnLabel'] = self :: getSubDnLabel();
        }
      }
      $data['recoverPassword'] = isset(self :: $ldapServer['recoverPassword']);
    }
  }

  /**
   * Ajax method when change ldapserver on recoverPassword form
   *
   * @param[in] $data array The return data address
   *
   * @retval void
   **/
  public static function ajax_onLdapServerChangedRecoverPassword(&$data) {
    if ( isset($_REQUEST['server']) ) {
      self :: setLdapServer($_REQUEST['server']);
      $data=array('recoverPassword' => isset(self :: $ldapServer['recoverPassword']));
    }
  }

  /**
   * Set globals from the ldap server
   *
   * @retval void
   */
  public static function setGlobals() {
    if ( isset(self :: $ldapServer['globals'])) {
      foreach(self :: $ldapServer['globals'] as $key => $value) {
        $GLOBALS[$key] = $value;
      }
    }
  }

  /**
   * Register a LSaddon view
   *
   * @param[in] $LSaddon string The LSaddon
   * @param[in] $viewId string The view ID
   * @param[in] $label string The view's label
   * @param[in] $viewFunction string The view's function name
   * @param[in] $allowedLSprofiles array|null Array listing allowed profiles.
   *                                          If null, no access control will
   *                                          be done for this view.
   * @param[in] $showInMenu boolean Show (or not) this view in menu
   *
   * @retval bool True is the view have been registred, false otherwise
   **/
  public static function registerLSaddonView($LSaddon,$viewId,$label,$viewFunction,$allowedLSprofiles=null,$showInMenu=True) {
    if (function_exists($viewFunction)) {
      $func = new ReflectionFunction($viewFunction);
      if (basename($func->getFileName())=="LSaddons.$LSaddon.php") {
        self :: $LSaddonsViews[$LSaddon][$viewId]=array (
          'LSaddon' => $LSaddon,
          'label' => $label,
          'function' => $viewFunction,
          'allowedLSprofiles' => $allowedLSprofiles,
          'showInMenu' => (bool)$showInMenu
        );
        return True;
      }
      else {
        LSerror :: addErrorCode('LSsession_21',array('func' => $func -> getName(),'addon' => $addon));
      }
    }
    else {
      LSerror :: addErrorCode('LSsession_23',array('func' => $viewFunction,'addon' => $LSaddon));
    }
    return False;
  }

  /**
   * Show LSaddon view
   *
   * @param[in] $LSaddon string The LSaddon
   * @param[in] $viewId string The view ID
   *
   * @retval void
   **/
  public static function showLSaddonView($LSaddon,$viewId) {
    if (self :: canAccessLSaddonView($LSaddon,$viewId)) {
      call_user_func(self :: $LSaddonsViews[$LSaddon][$viewId]['function']);
    }
  }

}
