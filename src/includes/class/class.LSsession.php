<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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
 * Manage user session
 *
 * This class manage user session
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSsession {

  /*
   * Class constants store and restore from PHP session
   */

  // Current LDAP server ID
  private static $ldapServerId = NULL;

  // LDAP servers subDns
  private static $_subDnLdapServer = array();

  // The current topDN
  private static $topDn = NULL;

  // The LSldapObject type of current connected user
  private static $LSuserObjectType = NULL;

  // Current connected user DN
  private static $dn = NULL;

  // Current connected user RDN value (his login)
  private static $rdn = NULL;

  // User LDAP credentials
  private static $userLDAPcreds = false;

  // Current connected user LSprofiles
  private static $LSprofiles = array();

  // Current connected user LSaccess (access rights)
  private static $LSaccess = array();

  // Current connected user LSaddonsViewsAccess (access on LSaddons views)
  private static $LSaddonsViewsAccess = array();

  // Temporary files
  private static $tmp_file = array();

  /*
   * Class constants not store in session
   */

  // Current LDAP server config
  public static $ldapServer = NULL;

  // The template to display
  private static $template = NULL;

  // Ajax display flag
  private static $ajaxDisplay = false;

  // JS files to load on page
  private static $JSscripts = array();

  // Libs JS files to load on page
  private static $LibsJSscripts = array();

  // CSS files to load on page
  private static $CssFiles = array();

  // Libs CSS files to load on page
  private static $LibsCssFiles = array();

  // The LSldapObject of connected user
  private static $LSuserObject = NULL;

  // The LSauht object of the session
  private static $LSauthObject = false;

  // Initialized flag
  private static $initialized = false;

  // List of currently loaded LSaddons
  private static $loadedAddons = array();

  // LSaddons views
  private static $LSaddonsViews = array();

  // API mode
  private static $api_mode = false;

  /**
   * Get session info by key
   *
   * @param[in] $key string The info
   *
   * @retval mixed The info or null
   */
  public static function get($key) {
    switch($key) {
      case 'top_dn':
        return self :: getTopDn();
      case 'root_dn':
        return self :: getRootDn();
      case 'sub_dn_name':
        return self :: getSubDnName();
      case 'sub_dn_label':
        return self :: getSubDnLabel();
      case 'authenticated_user_dn':
        return self :: $dn;
      case 'authenticated_user_type':
        return self :: $LSuserObjectType;
      case 'authenticated_user':
        return self :: getLSuserObject();
      case 'is_connected':
        return self :: isConnected();
      case 'global_search_enabled':
        return self :: globalSearch();
      case 'email_sender':
        return self :: getEmailSender();
      case 'api_mode':
        return boolval(self :: $api_mode);
    }
    return null;
  }

 /**
  * Include PHP file
  *
  * @param[in] $file      string  The path to the file to include :
  *                               - if $external == false : the path must be relative to LS_ROOT_DIR
  *                               - if $external == true : the path could be absolute or relative. If
  *                                 relative, it will be treated with PHP include path.
  * @param[in] $external  boolean If true, file consided as external (optional, default: false)
  * @param[in] $warn      boolean If true, a warning will be log if file not found (optional, default: true)
  *                               This warning will be emit using LSlog if it's already loaded or error_log()
  *                               otherwise.
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean True if file is loaded, false otherwise
  */
  public static function includeFile($file, $external=false, $warn=true) {
    $path = ($external?'':LS_ROOT_DIR."/").$file;
    $local_path = ($external?'':LS_ROOT_DIR."/").LS_LOCAL_DIR.$file;
    $path = (file_exists($local_path)?$local_path:$path);
    if (!isAbsolutePath($path)) {
      $found = stream_resolve_include_path($path);
      if ($found === false) {
        self :: log(
          ($warn?'WARNING':'TRACE'),
          "includeFile($file, external=$external) : file $path not found in include path."
        );
        return false;
      }
      else {
        self :: log_trace("includeFile($file, external=$external): file path found using include path => '$found'");
        $path = $found;
      }
    }
    else if (!file_exists($path)) {
      self :: log(
        ($warn?'WARNING':'TRACE'),
        "includeFile($file, external=$external): file not found ($local_path / $path)"
      );
      return false;
    }
    if (!include_once($path)) {
      // Always log as warning in this case
      self :: log_warning("includeFile($file, external=$external): include_once($path) not returned TRUE");
      return false;
    }
    return true;
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

  /*
   * Log a message via class logger (of other method if LSlog is not loaded)
   *
   * @param[in] $level string The log level (see LSlog)
   * @param[in] $message string The message to log
   *
   * @retval void
   **/
  protected static function log($level, $message) {
    if (class_exists('LSlog')) {
      LSlog :: get_logger(get_called_class()) -> logging($level, $message);
      return;
    }
    // Alternative logging if LSlog is not already started
    $formated_message = "LSsession - $level - $message";
    switch ($level) {
      case 'FATAL':
      case 'ERROR':
        error_log($formated_message);
        if ($level == 'FATAL')
          die($formated_message);
        break;
      default:
        LSdebug($formated_message);
    }
  }

  /**
   * Log an exception via class logger
   *
   * @param[in] $exception Exception The exception to log
   * @param[in] $prefix string|null Custom message prefix (optional, see self :: log_exception())
   * @param[in] $fatal boolean Log exception as a fatal error (optional, default: true)
   *
   * @retval void
   **/
  protected static function log_exception($exception, $prefix=null, $fatal=true) {
    if (class_exists('LSlog')) {
      LSlog :: get_logger(get_called_class()) -> exception($exception, $prefix, $fatal);
      return;
    }
    // Implement basic exception message formating
    $message = ($prefix?"$prefix :\n":"An exception occured :\n").
      "## ".$exception->getFile().":".$exception->getLine(). " : ". $exception->getMessage();
    self :: log(($fatal?'FATAL':'ERROR'), $message);
  }

  /**
   * Log a message with level TRACE
   *
   * @param[in] $message The message to log
   *
   * @retval void
   **/
  protected static function log_trace($message) {
    self :: log('TRACE', $message);
  }

  /**
   * Log a message with level DEBUG
   *
   * @param[in] $message The message to log
   *
   * @retval void
   **/
  protected static function log_debug($message) {
    self :: log('DEBUG', $message);
  }

  /**
   * Log a message with level INFO
   *
   * @param[in] $message The message to log
   *
   * @retval void
   **/
  protected static function log_info($message) {
    self :: log('INFO', $message);
  }

  /**
   * Log a message with level WARNING
   *
   * @param[in] $message The message to log
   *
   * @retval void
   **/
  protected static function log_warning($message) {
    self :: log('WARNING', $message);
  }

  /**
   * Log a message with level ERROR
   *
   * @param[in] $message The message to log
   *
   * @retval void
   **/
  protected static function log_error($message) {
    self :: log('ERROR', $message);
  }

  /**
   * Log a message with level FATAL
   *
   * @param[in] $message The message to log
   *
   * @retval void
   **/
  protected static function log_fatal($message) {
    self :: log('FATAL', $message);
  }

 /**
  * Start LSurl
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval True on success, false otherwise
  */
  private static function startLSurl() {
    if (self :: loadLSclass('LSurl') && self :: includeFile(LS_INCLUDE_DIR . "routes.php")) {
      return true;
    }
    return False;
  }

 /**
  * Start and initialize LStemplate
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval True on success, false otherwise
  */
  private static function startLStemplate() {
    if ( self :: loadLSclass('LStemplate') ) {
      if (!LStemplate :: start(
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
      ))
        return False;
      LStemplate :: addHelpInfo(
        'LSdefault',
        array(
          'copy_to_clipboard' => _('Copy to clipboard'),
          'copied' => _('Copied!'),
        )
      );
      return True;
    }
    return False;
  }

 /**
  * Retrieve current topDn (=DN scope browsed)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval string The current topDn
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
  * Retrieve current rootDn (=LDAP server root base DN)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval string The current rootDn
  */
  public static function getRootDn() {
    return self :: $ldapServer['ldap_config']['basedn'];
  }

 /**
  * Start LSerror
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval True on success, false otherwise
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
    if (self :: includeFile(LS_CLASS_DIR .'class.'.$class.'.php', false, $warn))
      return true;
    if ($warn)
      LSerror :: addErrorCode('LSsession_05', $class);
    return False;
  }

 /**
  * Load LSobject type
  *
  * @param[in] $object string Name of the LSobject type
  * @param[in] $warn boolean Set to false to avoid warning in case of loading error (optional, default: true)
  *
  * @retval boolean True if LSobject type loaded, false otherwise
  */
  public static function loadLSobject($object, $warn=true) {
    if(class_exists($object)) {
      return true;
    }
    $error = false;
    // Load LSldapObject class
    if (!self :: loadLSclass('LSldapObject')) {
      self :: log_error("loadLSobject($object): fail to load LSldapObject class");
      $error = true;
    }
    // Check LSobject type name
    elseif (!LSldapObject :: isValidTypeName($object)) {
      self :: log_error("loadLSobject($object): invalid LSobject type name");
      $error = true;
    }
    // Load config file
    elseif (!self :: includeFile( LS_OBJECTS_DIR . 'config.LSobjects.'.$object.'.php' ) || !isset($GLOBALS['LSobjects'][$object])) {
      self :: log_error("loadLSobject($object): Fail to include 'config.LSobjects.$object.php' file");
      $error = true;
    }
    // Check config file
    elseif (!isset($GLOBALS['LSobjects'][$object]) || !is_array($GLOBALS['LSobjects'][$object])) {
      self :: log_error("loadLSobject($object): \$GLOBALS['LSobjects'][$object] is not declared after loaded config file (or is not an array).");
      $error = true;
    }
    // Set LSobject type configuration
    elseif (!LSconfig :: set("LSobjects.$object", $GLOBALS['LSobjects'][$object])) {
      self :: log_error("loadLSobject($object): Fail to LSconfig :: set('LSobjects.$object', \$GLOBALS['LSobjects'][$object])");
      $error = true;
    }
    // Load LSaddons used by this LSobject type (if configured)
    else if (isset($GLOBALS['LSobjects'][$object]['LSaddons'])) {
      if (!is_array($GLOBALS['LSobjects'][$object]['LSaddons']))
        $GLOBALS['LSobjects'][$object]['LSaddons'] = array($GLOBALS['LSobjects'][$object]['LSaddons']);
      foreach ($GLOBALS['LSobjects'][$object]['LSaddons'] as $addon) {
        if (!self :: loadLSaddon($addon)) {
          self :: log_error("loadLSobject($object): Fail to load LSaddon '$addon'");
          $error = true;
        }
      }
    }
    // Load or declare corresponding PHP class (if no previous error occured)
    if (!$error && !self :: loadLSclass($object, 'LSobjects')) {
      self :: log_debug("loadLSobject($object): Fail to load $object class. Implement simple one.");
      eval("class $object extends LSldapObject {};");
    }
    // Warn on error (is enabled)
    if ($error && $warn)
      LSerror :: addErrorCode('LSsession_04', $object);
    return !$error;
  }

 /**
  * Load a LSaddon (if not already loaded)
  *
  * @param[in] $addon The addon name (ex: samba)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval boolean True if addon loaded, false otherwise
  */
  public static function loadLSaddon($addon) {
    if (in_array($addon, self :: $loadedAddons))
      return true;
    if(self :: includeFile(LS_ADDONS_DIR .'LSaddons.'.$addon.'.php')) {
      // Load LSaddon config file (without warning if not found)
      $conf_file = LS_CONF_DIR."LSaddons/config.LSaddons.".$addon.".php";
      if (self :: includeFile($conf_file, false, false))
        self :: log_debug("loadLSaddon($addon): config file '$conf_file' loaded.");
      else
        self :: log_debug("loadLSaddon($addon): config file '$conf_file' not found.");
      if (!call_user_func('LSaddon_'. $addon .'_support')) {
        LSerror :: addErrorCode('LSsession_02',$addon);
        return;
      }
      self :: $loadedAddons[] = $addon;
      return true;
    }
    return;
  }


 /**
  * Load an LdapSaisie resource file
  *
  * @param[in] $file              The resource file path/name to load, relative to LS_RESOURCE_DIR
  *                               (Example : supann/populations.php)
  * @param[in] $warn (Optionnel)  Trigger LSsession_22 error if an error occured loading this
  *                               resource file (Default: true)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval boolean true on success, otherwise false
  */
  public static function loadResourceFile($path, $warn=true) {
    if (self :: includeFile(LS_RESOURCE_DIR . $path, false, $warn))
      return true;
    if ($warn)
      LSerror :: addErrorCode('LSsession_22', $path);
    return False;
  }

 /**
  * Load LSauth
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval True on success, false otherwise
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
  * Load globally required LSaddons
  *
  * Load LSaddons list in $GLOBALS['LSaddons']['loads']
  *
  * @retval boolean True on success, False otherwise
  */
  public static function loadLSaddons() {
    $conf = LSconfig :: get('LSaddons.loads');
    if(!is_array($conf)) {
      LSerror :: addErrorCode('LSsession_01',"LSaddons['loads']");
      return;
    }

    $error = false;
    foreach ($conf as $addon) {
      if (!self :: loadLSaddon($addon))
        $false = true;
    }
    return !$error;
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
  * Start and initialize LdapSaisie session
  *
  * LSsession initialization :
  * - initiale LdapSaisie main components (LSerror, LSlog, LScli, LStemplate, ...)
  * - restore connected user info from session or trigger authentication (or password recovery)
  * - restore other session info from session (cache / tmp files)
  * - start LDAP connection
  * - handle logout (if $_GET['LSsession_logout'] is present)
  * - load connected user profiles and access (if connected)
  * - enable/disable global search
  *
  * @retval boolean True on intiatialization success and if user is authenticed, false otherwise.
  */
  public static function startLSsession() {
    if (!self :: initialize()) {
      return;
    }

    if(isset($_SESSION['LSsession']['LSuserObjectType']) && isset($_SESSION['LSsession']['dn']) && !isset($_GET['LSsession_recoverPassword'])) {
      self :: log_debug('existing session');
      // --------------------- Session existante --------------------- //
      self :: $topDn            = $_SESSION['LSsession']['topDn'];
      self :: $dn               = $_SESSION['LSsession']['dn'];
      self :: $LSuserObjectType = $_SESSION['LSsession']['LSuserObjectType'];
      self :: $rdn              = $_SESSION['LSsession']['rdn'];
      self :: $ldapServerId     = $_SESSION['LSsession']['ldapServerId'];
      self :: $tmp_file         = $_SESSION['LSsession']['tmp_file'];
      self :: $userLDAPcreds    = $_SESSION['LSsession']['userLDAPcreds'];

      if ( self :: cacheLSprofiles() && !isset($_REQUEST['LSsession_refresh']) ) {
        self :: setLdapServer(self :: $ldapServerId);
        if (!LSauth :: start()) {
          self :: log_error("startLSsession(): can't start LSauth -> stop");
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
          self :: log_error("startLSsession(): can't start LSauth -> stop");
          return;
        }
        if (!self :: LSldapConnect())
          return;
        self :: loadLSprofiles();
      }

      if ( self :: cacheSudDn() && (!isset($_REQUEST['LSsession_refresh'])) ) {
        self :: $_subDnLdapServer = ((isset($_SESSION['LSsession_subDnLdapServer']))?$_SESSION['LSsession_subDnLdapServer']:NULL);
      }

      if (!self :: loadLSobject(self :: $LSuserObjectType)) {
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
        LSurl :: redirect();
        return;
      }

      if ( !self :: cacheLSprofiles() || isset($_REQUEST['LSsession_refresh']) ) {
        self :: loadLSprofiles();
        self :: loadLSaccess();
        self :: loadLSaddonsViewsAccess();
        $_SESSION['LSsession']=self :: getContextInfos();
      }

      LStemplate :: assign('LSsession_username',self :: getLSuserObject() -> getDisplayName());

      if (isset($_POST['LSsession_topDn']) && $_POST['LSsession_topDn'])
        self :: setSubDn($_POST['LSsession_topDn']);

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
          self :: setSubDn($_POST['LSsession_topDn']);
        }
        else {
          self :: setSubDn(self :: $ldapServer['ldap_config']['basedn']);
        }

        if (!LSauth :: start()) {
          self :: log_error("startLSsession(): can't start LSauth -> stop");
          return;
        }

        if (isset($_GET['LSsession_recoverPassword'])) {
          $recoveryPasswordInfos = self :: recoverPasswd (
            (isset($_REQUEST['LSsession_user'])?$_REQUEST['LSsession_user']:''),
            (isset($_GET['recoveryHash'])?$_GET['recoveryHash']:'')
          );
        }
        else {
          $LSuserObject = LSauth :: forceAuthentication();
          if ($LSuserObject) {
            // Authentication successful
            self :: $LSuserObject = $LSuserObject;
            self :: $LSuserObjectType = $LSuserObject -> getType();
            self :: $dn = $LSuserObject->getValue('dn');
            self :: $rdn = $LSuserObject->getValue('rdn');
            if (
              isset(self :: $ldapServer['useUserCredentials']) &&
              self :: $ldapServer['useUserCredentials']
            ) {
              if (
                isset(self :: $ldapServer['useAuthzProxyControl']) &&
                self :: $ldapServer['useAuthzProxyControl']
              ) {
                if (!LSldap :: setAuthzProxyControl(self :: $dn)) {
                  return;
                }
              }
              else {
                self :: $userLDAPcreds = LSauth :: getLDAPcredentials($LSuserObject);
                if (!is_array(self :: $userLDAPcreds)) {
                  LSerror :: addErrorCode('LSsession_14');
                  self :: $userLDAPcreds = false;
                  return;
                }
                if (!LSldap :: reconnectAs(
                    self :: $userLDAPcreds['dn'],
                    self :: $userLDAPcreds['pwd'],
                    self :: $ldapServer['ldap_config']
                  )) {
                  LSerror :: addErrorCode('LSsession_15');
                  return;
                }
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
      if (isset($_GET['LSsession_recoverPassword'])) {
        self :: displayRecoverPasswordForm($recoveryPasswordInfos);
      }
      elseif(LSauth :: displayLoginForm()) {
        self :: displayLoginForm();
      }
      else {
        self :: setTemplate('base.tpl');
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
  private static function recoverPasswd($username, $recoveryHash) {
    // Check feature is enabled and LSmail available
    if (!isset(self :: $ldapServer['recoverPassword']) || !self :: loadLSaddon('mail')) {
      LSerror :: addErrorCode('LSsession_18');
      return;
    }

    // Start LSauth
    if (!LSauth :: start()) {
      self :: log_error("recoverPasswd(): can't start LSauth -> stop");
      return;
    }

    // Search user by recoveryHash or username
    if (!empty($recoveryHash)) {
      $users = array();
      $filter = Net_LDAP2_Filter::create(
        self :: $ldapServer['recoverPassword']['recoveryHashAttr'],
        'equals',
        $recoveryHash
      );
      foreach (LSauth :: getAuthObjectTypes() as $objType => $objParams) {
        if (!self :: loadLSobject($objType))
          return false;
        $authobject = new $objType();
        $users = array_merge(
          $users,
          $authobject -> listObjects($filter, self :: getTopDn(), array('onlyAccessible' => false))
        );
      }
    }
    elseif (!empty($username)) {
      $users = LSauth :: username2LSobjects($username);
      if (!is_array($users))
        return;
    }
    else {
      self :: log_debug('recoverPasswd(): no username or recoveryHash provided.');
      return;
    }

    // Check user found (and not duplicated)
    $nbresult = count($users);
    if ($nbresult == 0) {
      self :: log_debug('recoverPasswd(): incorrect hash/username');
      LSerror :: addErrorCode('LSsession_06');
      return;
    }
    elseif ($nbresult > 1) {
      self :: log_debug("recoverPasswd(): duplicated user found with hash='$recoveryHash' / username='$username'");
      LSerror :: addErrorCode('LSsession_07');
      return;
    }

    $user = array_pop($users);
    $rdn = $user -> getValue('rdn');
    $username = $rdn[0];
    self :: log_debug("recoverPasswd(): user found, username = '$username'");


    self :: log_debug("recoverPasswd(): start recovering password");
    $emailAddress = $user -> getValue(self :: $ldapServer['recoverPassword']['mailAttr']);
    $emailAddress = $emailAddress[0];

    if (!checkEmail($emailAddress)) {
      LSerror :: addErrorCode('LSsession_19');
      return;
    }
    self :: log_debug("recoverPasswd(): Email = '$emailAddress'");
    self :: $dn = $user -> getDn();

    //
    $recoveryPasswordInfos = array();

    // First step : send recoveryHash
    if (empty($recoveryHash)) {
      $hash = self :: recoverPasswdFirstStep($user);
      if ($hash) {
        if (self :: recoverPasswdSendMail($emailAddress, 1, $hash)) {
          // Recovery hash sent
          $recoveryPasswordInfos['recoveryHashMail'] = $emailAddress;
        }
      }
    }
    // Second step : generate and send new password
    else {
      $pwd = self :: recoverPasswdSecondStep($user);
      if ($pwd) {
        if (self :: recoverPasswdSendMail($emailAddress, 2, $pwd)) {
          // New password sent
          $recoveryPasswordInfos['newPasswordMail'] = $emailAddress;
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
      $subject = self :: $ldapServer['recoverPassword']['recoveryHashMail']['subject'];
      $msg = getFData(
        self :: $ldapServer['recoverPassword']['recoveryHashMail']['msg'],
        LSurl :: get_public_absolute_url('index')."?LSsession_recoverPassword&recoveryHash=$info"
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
      self :: log_debug("recoverPasswdSendMail($mail, $step): error sending email.");
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
        self :: log_error("recoverPasswdFirstStep($user): error updating user.");
        LSerror :: addErrorCode('LSsession_20',6);
      }
    }
    else {
      // Erreur durant la validation du formulaire de modification de perte de password
      self :: log_error("recoverPasswdFirstStep($user): error validating form.");
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
    $pwd_attr_name = LSauth :: getUserPasswordAttribute($user);
    if (array_key_exists($pwd_attr_name, $user -> attrs)) {
      $pwd_attr = $user -> attrs[$pwd_attr_name];
      $pwd = generatePassword(
        $pwd_attr -> getConfig('html_options.chars'),
        $pwd_attr -> getConfig('html_options.lenght')
      );
      self :: log_debug("recoverPasswdSecondStep($user): new password = '$pwd'.");
      $lostPasswdForm = $user -> getForm('lostPassword');
      $lostPasswdForm -> setPostData(
        array(
          self :: $ldapServer['recoverPassword']['recoveryHashAttr'] => array(''),
          $pwd_attr_name => array($pwd)
        )
        ,true
      );
      if($lostPasswdForm -> validate()) {
        if ($user -> updateData('lostPassword')) {
          return $pwd;
        }
        else {
          // Erreur durant la mise à jour de l'objet
          self :: log_error("recoverPasswdSecondStep($user): error updating user.");
          LSerror :: addErrorCode('LSsession_20',3);
        }
      }
      else {
        // Erreur durant la validation du formulaire de modification de perte de password
        self :: log_error("recoverPasswdSecondStep($user): error validating form.");
        LSerror :: addErrorCode('LSsession_20',2);
      }
    }
    else {
      // l'attribut password n'existe pas
      self :: log_error("recoverPasswdSecondStep($user): password attribute '$pwd_attr_name' does not exists.");
      LSerror :: addErrorCode('LSsession_20',1);
    }
    return;
  }

 /**
  * Retrieve context information (to store in PHP session)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval array Associative array of context information
  */
  private static function getContextInfos() {
    return array(
      'tmp_file' => self :: $tmp_file,
      'topDn' => self :: $topDn,
      'dn' => self :: $dn,
      'rdn' => self :: $rdn,
      'LSuserObjectType' => self :: $LSuserObjectType,
      'userLDAPcreds' => self :: $userLDAPcreds,
      'ldapServerId' => self :: $ldapServerId,
      'ldapServer' => self :: $ldapServer,
      'LSprofiles' => self :: $LSprofiles,
      'LSaccess' => self :: $LSaccess,
      'LSaddonsViewsAccess' => self :: $LSaddonsViewsAccess
    );
  }

  /**
  * Retrieve connected user LSobject (as reference)
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval LSldapObject|false Current connected user LSldapObject, or False in case of error
  */
  public static function &getLSuserObject($dn=null) {
    if ($dn) {
      self :: $dn = $dn;
    }
    if (!self :: $LSuserObject) {
      if (self :: $LSuserObjectType  && self :: loadLSobject(self :: $LSuserObjectType)) {
        self :: $LSuserObject = new self :: $LSuserObjectType();
        if (!self :: $LSuserObject -> loadData(self :: $dn)) {
          self :: $LSuserObject = null;
          self :: log_error(
            "getLSuserObject($dn): Fail to retrieve current connected user ".
            "information from LDAP"
          );
          return;
        }
      }
      else {
        self :: log_error(
          "getLSuserObject($dn): Current connected user object type not ".
          "defined or can not be loaded (".self :: $LSuserObjectType.")"
        );
        return;
      }
    }
    return self :: $LSuserObject;
  }

 /**
  * Check if user is connected
  *
  * @author Benjamin Renard <brenard@easter-eggs.com
  *
  * @retval boolean True if user connected, false instead
  */
  public static function isConnected() {
    if (self :: getLSuserObject())
      return true;
    return false;
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
  * Live change of the connected user
  *
  * @param[in] $object LSldapObject The new connected user object
  *
  * @retval boolean True on success, false otherwise
  */
 public static function changeAuthUser($object) {
  if(!($object instanceof LSldapObject)) {
    self :: log_error(
      "changeAuthUser(): An LSldapObject must be provided, not ".get_class($object)
    );
    return;
  }
  if(!array_key_exists($object -> getType(), LSauth :: getAuthObjectTypes())) {
    self :: log_error(
      "changeAuthUser(): Invalid object provided, must be one of following types (not a ".
      $object -> getType().') : '.implode(', ', array_keys(LSauth :: getAuthObjectTypes()))
    );
    return;
  }
  self :: log_info(
    "Change authenticated user info ('".self :: $dn."' -> '".$object -> getDn()."')"
  );
  self :: $dn = $object -> getDn();
  $rdn = $object -> getValue('rdn');
  if(is_array($rdn)) {
    $rdn = $rdn[0];
  }
  self :: $rdn = $rdn;
  self :: $LSuserObject = $object;
  self :: $LSuserObjectType = $object -> getType();

  if(self :: loadLSprofiles()) {
    self :: loadLSaccess();
    self :: loadLSaddonsViewsAccess();
    $_SESSION['LSsession']=self :: getContextInfos();
    self :: log_debug("changeAuthUser(): authenticated user successfully updated.");
    return true;
  }
  self :: log_error("Fail to reload LSprofiles after updating auth user info.");
  return;
 }

 /**
  * Set the LDAP server of the session
  *
  * Set the LDAP server of the session from its ID in configuration array
  * LSconfig :: get('ldap_servers').
  *
  * @param[in] $id integer Index of LDAP server
  * @param[in] $subDn integer SubDN of LDAP server (optional)
  *
  * @retval boolean True if set, false otherwise
  */
  public static function setLdapServer($id, $subDn=null) {
    $conf = LSconfig :: get("ldap_servers.$id");
    if ( is_array($conf) ) {
      self :: $ldapServerId = $id;
      self :: $ldapServer = $conf;
      LSlang :: setLocale();
      self :: setGlobals();

      if ($subDn)
        return self :: setSubDn($subDn);

      return true;
    }
    return false;
  }

 /**
  * Set the subDn of the session
  *
  * @param[in] $subDn string SubDN of LDAP server
  *
  * @retval boolean True if set, false otherwise
  */
  public static function setSubDn($subDn) {
    if (self :: validSubDnLdapServer($subDn)) {
      self :: $topDn = $subDn;
      $_SESSION['LSsession']['topDn'] = $subDn;
      return true;
    }
    return;
  }

 /**
  * Connexion au serveur Ldap
  *
  * @retval boolean True sinon false.
  */
  public static function LSldapConnect() {
    if (!self :: $ldapServer && !self :: setLdapServer(0)) {
      return;
    }
    if (self :: $ldapServer) {
      self :: includeFile(LSconfig :: get('NetLDAP2'), true);
      if (!self :: loadLSclass('LSldap')) {
        return;
      }
      if (
        self :: $dn && isset(self :: $ldapServer['useUserCredentials']) &&
        self :: $ldapServer['useUserCredentials']
      ) {
        if (
          isset(self :: $ldapServer['useAuthzProxyControl']) &&
          self :: $ldapServer['useAuthzProxyControl']
        ) {
          // Firstly connect using main config and after, set authz proxy control
          if (
            !LSldap :: connect(self :: $ldapServer['ldap_config']) ||
            !LSldap :: setAuthzProxyControl(self :: $dn)
          ) {
            LSerror :: addErrorCode('LSsession_15');
            return;
          }
        }
        else {
          LSldap :: reconnectAs(
            self :: $userLDAPcreds['dn'],
            self :: $userLDAPcreds['pwd'],
            self :: $ldapServer['ldap_config']
          );
        }
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
    if (
      self :: cacheSudDn() &&
      isset(self :: $_subDnLdapServer[self :: $ldapServerId][$login])
    ) {
      return self :: $_subDnLdapServer[self :: $ldapServerId][$login];
    }
    if (!self::subDnIsEnabled()) {
      return;
    }
    $return=array();
    foreach(self :: $ldapServer['subDn'] as $subDn_name => $subDn_config) {
      if ($login && isset($subDn_config['nologin']) && $subDn_config['nologin'])
        continue;
      if ($subDn_name == 'LSobject') {
        if (is_array($subDn_config)) {
          foreach($subDn_config as $LSobject_name => $LSoject_config) {
            if (
              isset($LSoject_config['basedn']) &&
              !empty($LSoject_config['basedn'])
            ) {
              $basedn = $LSoject_config['basedn'];
            }
            else {
              $basedn = self::getRootDn();
            }
            if (
              isset($LSoject_config['displayName']) &&
              !empty($LSoject_config['displayName'])
            ) {
              $displayNameFormat = $LSoject_config['displayName'];
            }
            else {
              $displayNameFormat = NULL;
            }
            $sparams = array();
            $sparams['onlyAccessible'] = (
              isset($LSoject_config['onlyAccessible'])?
              $LSoject_config['onlyAccessible']:
              False
            );
            if( self :: loadLSobject($LSobject_name) ) {
              if ($subdnobject = new $LSobject_name()) {
                $tbl_return = $subdnobject -> getSelectArray(
                  NULL, // pattern
                  $basedn, $displayNameFormat,
                  false, // approx
                  false, // cache
                  NULL, // filter
                  $sparams
                );
                if (is_array($tbl_return)) {
                  $return = array_merge($return, $tbl_return);
                }
                else {
                  LSerror :: addErrorCode('LSsession_17', 3);
                }
              }
              else {
                LSerror :: addErrorCode('LSsession_17', 2);
              }
            }
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_17',1);
        }
      }
      elseif (
        isCompatibleDNs(
          $subDn_config['dn'],
          self :: $ldapServer['ldap_config']['basedn']
        ) && $subDn_config['dn'] != ""
      ) {
        $return[$subDn_config['dn']] = __($subDn_name);
      }
    }
    if (self :: cacheSudDn()) {
      self :: $_subDnLdapServer[self :: $ldapServerId][$login] = $return;
      $_SESSION['LSsession_subDnLdapServer'] = self :: $_subDnLdapServer;
    }
    return $return;
  }

  /**
   * Retrieve currently used LDAP server subDn list sorted by depth
   * in the LDAP tree (descending order)
   *
   * @return array Sorted array of LDAP server subDns
   */
  public static function getSortSubDnLdapServer($login=false) {
    $subDnLdapServer = self :: getSubDnLdapServer($login);
    if (!$subDnLdapServer) {
      return array();
    }
    uksort($subDnLdapServer, "compareDn");
    return $subDnLdapServer;
  }

 /**
  * Retrieve HTML options of current LDAP server topDNs
  *
  * @retval string HTML options of current LDAP server topDNs
  */
  public static function getSubDnLdapServerOptions($selected=NULL, $login=false) {
    $list = self :: getSubDnLdapServer($login);
    if (!$list)
      return;
    asort($list);
    $options = array();
    foreach($list as $dn => $txt) {
      $options[] = (
        "<option value=\"$dn\"".(
          $selected && $selected == $dn?
          " selected":
          ""
        ).">$txt</option>"
      );
    }
    return implode('', $options);
  }

 /**
  * Check a subDn is valid
  *
  * @param[in] string The subDn to check
  *
  * @retval boolean True if subDn is valid, False otherwise
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
  * Check a user password from an LSobject and a password
  *
  * Try to bind on LDAP server using the provided LSobject DN and password.
  *
  * @param[in] LSobject The user LSobject
  * @param[in] string The password to check
  *
  * @retval boolean True on authentication success, false otherwise.
  */
  public static function checkUserPwd($object, $pwd) {
    return LSldap :: checkBind($object -> getValue('dn'), $pwd);
  }

 /**
  * Display login form
  *
  * Define template information allowing to display login form.
  *
  * @retval void
  */
  public static function displayLoginForm() {
    LStemplate :: assign('pagetitle', _('Connection'));
    $ldapservers = array();
    foreach(LSconfig :: get('ldap_servers') as $id => $infos)
      $ldapservers[$id] = __($infos['name']);
    LStemplate :: assign('ldapservers', $ldapservers);
    LStemplate :: assign('ldapServerId', (self :: $ldapServerId?self :: $ldapServerId:0));
    self :: setTemplate('login.tpl');
    LStemplate :: addJSscript('LSsession_login.js');
  }

 /**
  * Display password recovery form
  *
  * Define template information allowing to display password recovery form.
  *
  * @param[in] $infos array() Password recovery process state information
  *
  * @retval void
  */
  public static function displayRecoverPasswordForm($recoveryPasswordInfos) {
    LStemplate :: assign('pagetitle', _('Recovery of your credentials'));

    $ldapservers = array();
    foreach(LSconfig :: get('ldap_servers') as $id => $infos)
      $ldapservers[$id] = __($infos['name']);
    LStemplate :: assign('ldapservers', $ldapservers);
    LStemplate :: assign('ldapServerId', (self :: $ldapServerId?self :: $ldapServerId:0));

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

    LStemplate :: assign('recoverpassword_step', $recoverpassword_step);
    LStemplate :: assign('recoverpassword_msg', $recoverpassword_msg);

    self :: setTemplate('recoverpassword.tpl');
    LStemplate :: addJSscript('LSsession_recoverPassword.js');
  }

 /**
  * Set the template file that will display
  *
  * Note: template files are normally store in templates directory.
  *
  * @param[in] string The name of the template file
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
   * @deprecated
   * @see LStemplate :: addJSscript()
   *
   * @retval void
   */
  public static function addJSscript($file, $path=NULL) {
    if ($path)
      $file = $path.$file;
    LStemplate :: addJSscript($file);
    LSerror :: addErrorCode(
      'LSsession_27',
      array(
        'old' => 'LSsession :: addJSscript()',
        'new' => 'LStemplate :: addJSscript()',
        'context' => LSlog :: get_debug_backtrace_context(),
      )
    );
  }

  /**
   * Add a library JS file to load on page
   *
   * @param[in] $file string The JS filename
   * @deprecated
   * @see LStemplate :: addLibJSscript()
   *
   * @retval void
   */
  public static function addLibJSscript($file) {
    LStemplate :: addLibJSscript($file);
    LSerror :: addErrorCode(
      'LSsession_27',
      array(
        'old' => 'LSsession :: addLibJSscript()',
        'new' => 'LStemplate :: addLibJSscript()',
        'context' => LSlog :: get_debug_backtrace_context(),
      )
    );
  }

 /**
  * Add Javascript configuration parameter
  *
  * @param[in] $name string The name of the JS config paramenter
  * @param[in] $val mixed The value of the JS config paramenter
  * @deprecated
  * @see LStemplate :: addJSconfigParam()
  *
  * @retval void
  */
  public static function addJSconfigParam($name, $val) {
    LStemplate :: addJSconfigParam($name, $val);
    LSerror :: addErrorCode(
      'LSsession_27',
      array(
        'old' => 'LSsession :: addJSconfigParam()',
        'new' => 'LStemplate :: addJSconfigParam()',
        'context' => LSlog :: get_debug_backtrace_context(),
      ),
      false
    );
  }

 /**
  * Add a CSS file to load on page
  *
  * @param[in] $file string The CSS filename
  * @param[in] $path string|null The sub-directory path that contain this file.
  * @deprecated
  * @see LStemplate :: addCssFile()
  *
  * @retval void
  */
  public static function addCssFile($file, $path=NULL) {
    if ($path)
      $file = $path.$file;
    LStemplate :: addCssFile($file);
    LSerror :: addErrorCode(
      'LSsession_27',
      array(
        'old' => 'LSsession :: addCssFile()',
        'new' => 'LStemplate :: addCssFile()',
        'context' => LSlog :: get_debug_backtrace_context(),
      )
    );
  }

 /**
  * Add a library CSS file to load on page
  *
  * @param[in] $file string The CSS filename
  * @deprecated
  * @see LStemplate :: addLibCssFile()
  *
  * @retval void
  */
  public static function addLibCssFile($file) {
    LStemplate :: addLibCssFile($file);
    LSerror :: addErrorCode(
      'LSsession_27',
      array(
        'old' => 'LSsession :: addLibCssFile()',
        'new' => 'LStemplate :: addLibCssFile()',
        'context' => LSlog :: get_debug_backtrace_context(),
      )
    );
  }

 /**
  * Show the template
  *
  * Load dependencies of show the previously selected template file
  *
  * @retval void
  */
  public static function displayTemplate() {
    if (self :: $api_mode)
      return self :: displayAjaxReturn();
    $KAconf = LSconfig :: get('keepLSsessionActive');
    if (
      (
        (!isset(self :: $ldapServer['keepLSsessionActive']))
        &&
        (!($KAconf === false))
      ) || self :: $ldapServer['keepLSsessionActive']
    ) {
      LStemplate :: addJSconfigParam(
        'keepLSsessionActive', ini_get('session.gc_maxlifetime')
      );
    }

    // Access
    LStemplate :: assign('LSaccess', self :: getLSaccess());
    LStemplate :: assign('LSaddonsViewsAccess', self :: $LSaddonsViewsAccess);

    // Niveau
    $listTopDn = self :: getSubDnLdapServer();
    if (is_array($listTopDn)) {
      asort($listTopDn);
      LStemplate :: assign('LSsession_subDn_level', self :: getSubDnLabel());
      LStemplate :: assign('LSsession_subDn_refresh', _('Refresh'));
      $LSsession_topDn_index = array();
      $LSsession_topDn_name = array();
      foreach($listTopDn as $index => $name) {
        $LSsession_topDn_index[] = $index;
        $LSsession_topDn_name[] = $name;
      }
      LStemplate :: assign('LSsession_subDn_indexes', $LSsession_topDn_index);
      LStemplate :: assign('LSsession_subDn_names', $LSsession_topDn_name);
      LStemplate :: assign('LSsession_subDn', self :: $topDn);
      LStemplate :: assign('LSsession_subDnName', self :: getSubDnName());
    }

    LStemplate :: assign('LSlanguages', LSlang :: getLangList());
    LStemplate :: assign('LSlang', LSlang :: getLang());
    LStemplate :: assign('LSencoding', LSlang :: getEncoding());

    LStemplate :: assign('displayLogoutBtn', LSauth :: displayLogoutBtn());
    LStemplate :: assign('displaySelfAccess', LSauth :: displaySelfAccess());

    // Infos
    LStemplate :: assign(
      'LSinfos',
      base64_encode(
        json_encode(
          isset($_SESSION['LSsession_infos']) && is_array($_SESSION['LSsession_infos'])?
          $_SESSION['LSsession_infos']:
          array()
        )
      )
    );
    $_SESSION['LSsession_infos'] = array();

    // Errors
    LSerror :: display();

    // LSdebug
    LSdebug_print();

    if (!self :: $template)
      self :: setTemplate('base_connected.tpl');

    LStemplate :: display(self :: $template);
  }

 /**
  * Set Ajax display mode
  *
  * @param[in] $val boolean True to enable Ajax display mode (optional, default: true)
  *
  * @retval void
  */
  public static function setAjaxDisplay($val=true) {
    self :: $ajaxDisplay = (boolean)$val;
  }

 /**
  * Check if Ajax display mode is enabled
  *
  * @retval boolean True if Ajax display mode is enabled, False otherwise
  */
  public static function getAjaxDisplay() {
    return (boolean)self :: $ajaxDisplay;
  }

 /**
  * Show Ajax return
  *
  * @retval void
  */
  public static function displayAjaxReturn($data=array(), $pretty=false) {
    // Adjust content-type
    header('Content-Type: application/json');

    // Adjust HTTP error code on unsuccessfull request
    if (isset($data['success']) && !$data['success'] && http_response_code() == 200)
      http_response_code(400);

    // If redirection set, just redirect user and not handling messages/errors to
    // keep it in session and show it on next page
    if (!isset($data['LSredirect']) || LSdebugDefined()) {
      if (!self :: $api_mode && class_exists('LStemplate'))
        $data['LSjsConfig'] = LStemplate :: getJSconfigParam();

      // Infos
      if(
        !empty($_SESSION['LSsession_infos']) &&
        is_array($_SESSION['LSsession_infos'])
      ) {
        $data['messages'] = $_SESSION['LSsession_infos'];
        $_SESSION['LSsession_infos'] = array();
      }

      if (LSerror :: errorsDefined()) {
        $data['errors'] = LSerror :: getErrors(self :: $api_mode);
      }

      if (!self :: $api_mode && LSdebugDefined()) {
        $data['LSdebug'] = LSdebug_print(true);
      }
    }

    if (!self :: $api_mode && isset($_REQUEST['imgload'])) {
      $data['imgload'] = $_REQUEST['imgload'];
    }

    echo json_encode(
      $data,
      (
        $pretty || isset($_REQUEST['pretty'])?
        JSON_PRETTY_PRINT:
        0
      )
    );
    return;

    echo json_encode($data, (($pretty||isset($_REQUEST['pretty']))?JSON_PRETTY_PRINT:0));
  }

  /**
   * Set API mode
   *
   * @param[in] $val boolean True to enable API mode (optional, default: true)
   *
   * @retval void
   */
  public static function setApiMode($val=true) {
    self :: $api_mode = (boolean)$val;
  }

 /**
  * Fetch builded template
  *
  * @param[in] string $template The template file to build
  * @param[in] array $variables Template variables to set before building
  *
  * @retval string The template builded HTML code
  */
  public static function fetchTemplate($template,$variables=array()) {
    foreach($variables as $name => $val) {
      LStemplate :: assign($name,$val);
    }
    return LStemplate :: fetch($template);
  }

  /**
   *
   * Takes an array of LSobject and reduce it using a search filter on
   * another type of LSobject.
   *
   * If an error is present in the filter definition array, an empty
   * array is returned.
   *
   * @param[in] string $LSobject The default LSobject type
   * @param[in] array $set Array of LSobjects
   * @param[in] array $filter_def Definition of the search filter for reduction
   * @param[in] string $basend Base DN for search, null by default
   *
   * @retval array The reduced array of LSobjects
   */
  private static function reduceLdapSet($LSobject, $set, $filter_def, $basedn=null) {
    if (empty($set)) {
      return array();
    }

    if (! isset($filter_def['filter']) &&
          (! isset($filter_def['attr']) ||
           ! isset($filter_def['attr_value']))) {
      self :: log_debug("reduceLdapSet(): LSobject LSprofil filter invalid : " . varDump($filter_def));
      return array();
    }

    self :: log_debug('reduceLdapSet(): reducing set of');
    foreach ($set as $object) {
      LSdebug('LSsession :: -> ' . $object -> getDn());
    }

    $LSobject = isset($filter_def['LSObject']) ? $filter_def['LSobject'] : $LSobject;
    self :: log_debug('reduceLdapSet(): LSobject = ' . $LSobject);
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
    self :: log_debug('reduceLdapSet(): reduced set to');
    foreach ($set as $object) {
      self :: log_debug('reduceLdapSet(): -> ' . $object -> getDn());
    }
    return $set;
  }

  /**
   * Loading user's profiles: load profile on specific LSobject type
   *
   * Regarding configuration, user profile on specific list on the specified
   * LSobject type will be loaded.
   *
   * @param[in] string $profile The LSprofil
   * @param[in] string $LSobject The LSobject type
   * @param[in] string $LSobject The parameters to list of granted objects
   *
   * @retval void
   */
  private static function loadLSprofilesLSobjects($profile, $LSobject, $listInfos) {
    if (! self :: loadLSclass('LSsearch')) {
      self :: log_error('Fail to load class LSsearch');
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
    if (!isset(self :: $LSprofiles[$profile])) {
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
   * Loading user's profiles
   *
   * @retval boolean True on success, false otherwise
   **/
  private static function loadLSprofiles() {
    if (!is_array(self :: $ldapServer['LSprofiles'])) {
      self :: log_warning('loadLSprofiles(): Current LDAP server have no configured LSprofile.');
      return;
    }
    self :: log_trace("loadLSprofiles(): Current LDAP server LSprofile configuration: ".varDump(self :: $ldapServer['LSprofiles']));
    foreach (self :: $ldapServer['LSprofiles'] as $profile => $profileInfos) {
      if (!is_array($profileInfos)) {
        self :: log_warning("loadLSprofiles(): Invalid configuration for LSprofile '$profile' (must be an array).");
        continue;
      }
      foreach ($profileInfos as $topDn => $rightsInfos) {
        // Do not handle 'label' key as a topDn
        if ($topDn == 'label') {
          continue;
        }
        elseif ($topDn == 'LSobjects') {
          /*
           * If $topDn == 'LSobject', we search for each LSobject type to find
           * all items on witch the user will have powers.
           */
          if (!is_array($rightsInfos)) {
            self :: log_warning('loadLSprofiles(): LSobjects => [] must be an array');
            continue;
          }
          foreach ($rightsInfos as $LSobject => $listInfos) {
            self :: log_debug('loadLSprofiles(): loading LSprofile ' . $profile . ' for LSobject ' . $LSobject . ' with params ' . var_export($listInfos, true));
            self :: loadLSprofilesLSobjects($profile, $LSobject, $listInfos);
          }
        }
        else {
          /*
           * Otherwise, we are normally in case of $topDn == a base DN and
           * $rightsInfos is :
           *   - an array (see above)
           *   - a user DN
           */
          if (is_array($rightsInfos)) {
            /*
             * $rightsInfos is an array, so we could have :
             *  - users DNs as key and null as value
             *  - DN of an object as key and an array of parameters to list users from one
             *    of its attribute as value
             */
            foreach($rightsInfos as $dn => $conf) {
              if (is_array($conf) && isset($conf['attr']) && isset($conf['LSobject'])) {
                // We have to retrieve this LSobject and list one of its attribute to retrieve
                // users key info.
                if(!self :: loadLSobject($conf['LSobject'])) {
                  // Warning log message is already emited by self :: loadLSobject()
                  continue;
                }

                // Instanciate object and retrieve its data
                $object = new $conf['LSobject']();
                if (!$object -> loadData($dn)) {
                  self :: log_warning("loadLSprofiles(): fail to load DN '$dn'.");
                  continue;
                }

                // Retrieve users key info values from object attribute
                $list_users_key_values = $object -> getValue($conf['attr']);
                if (!is_array($list_users_key_values)) {
                  self :: log_warning("loadLSprofiles(): fail to retrieve values of attribute '".$conf['attr']."' of LSobject ".$conf['LSobject']." with DN='$dn'");
                  continue;
                }
                self :: log_trace("loadLSprofiles(): retrieved values of attribute '".$conf['attr']."' of LSobject ".$conf['LSobject']." with DN='$dn': '".implode("', '", $list_users_key_values)."'");

                // Retrieve current connected key value
                $user_key_value_format = (isset($conf['attr_value'])?$conf['attr_value']:'%{dn}');
                $user_key_value = self :: getLSuserObject() -> getFData($user_key_value_format);

                // Check current connected user is list in attribute values
                if (in_array($user_key_value, $list_users_key_values)) {
                  self :: log_trace("loadLSprofiles(): current connected user is present in attribute '".$conf['attr']."' of LSobject ".$conf['LSobject']." with DN='$dn' (user key value: '$user_key_value')");
                  self :: $LSprofiles[$profile][] = $topDn;
                }
                else
                  self :: log_trace("loadLSprofiles(): current connected user is not list in attribute '".$conf['attr']."' of LSobject ".$conf['LSobject']." with DN='$dn' (user key value: '$user_key_value')");
              }
              else {
                // $conf is not an array, users DNs could be the key $dn and we don't care
                // about $conf value (normally null)
                if (self :: $dn == $dn) {
                  self :: log_trace("loadLSprofiles(): current connected user DN is explicitly list in $profile LSprofile configuration");
                  self :: $LSprofiles[$profile][] = $topDn;
                }
                else
                  self :: log_trace("loadLSprofiles(): current connected user DN is NOT explicitly list in $profile LSprofile configuration");
              }
            }
          }
          else {
            // $rightsInfos is not an array => its could be a user DN
            if ( self :: $dn == $rightsInfos ) {
              self :: log_trace("loadLSprofiles(): current connected user DN is explicitly appointed as $profile LSprofile in configuration");
              self :: $LSprofiles[$profile][] = $topDn;
            }
            else
              self :: log_trace("loadLSprofiles(): current connected user DN is NOT explicitly appointed as $profile LSprofile in configuration");
          }
        } // fin else ($topDn == 'LSobjects' or 'label')
      } // fin foreach($profileInfos)
    } // fin foreach LSprofiles
    self :: log_debug("loadLSprofiles(): LSprofiles = ".print_r(self :: $LSprofiles,1));
    return true;
  }

  /**
   * Load user access rights to build interface menu
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
            if (self :: canAccess($objectType))
              $access[$objectType] = $objectType :: getLabel();
            else
              self :: log_debug("loadLSaccess(): authenticated user have no access to $objectType");
          }
        }
        $LSaccess[self :: getTopDn()] = $access;
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
    if (is_null($topDn)) $topDn = self :: getTopDn();
    if (isset(self :: $LSaccess[$topDn])) {
      return self :: $LSaccess[$topDn];
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
   * Check if user is a specified profile on specified DN
   *
   * @param[in] string $dn DN of the object to check
   * @param[in] string $profile The profile
   *
   * @retval boolean True if user is a specified profile on specified DN, false otherwise.
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
   * Check if user is at least one of specified profiles on specified DN
   *
   * @param[in] string $dn DN of the object to check
   * @param[in] string $profiles The profiles list
   *
   * @retval boolean True if user is at least one of specified profiles on specified DN, false otherwise.
   */
  public static function isLSprofiles($dn,$profiles) {
    foreach ($profiles as $profile) {
      if (self :: isLSprofile($dn,$profile))
        return true;
    }
    return false;
  }

  /**
   * Return connected user's LSprofiles on a specific object.
   *
   * @param[in] string The object's DN
   *
   * @retval array Array of LSprofiles of the connected user on the specified object
   */
  public static function whoami($dn) {
    $retval = array('user');

    if (self :: $LSuserObjectType)
      $retval[] = self :: $LSuserObjectType;

    foreach(self :: $LSprofiles as $profile => $infos) {
      if(self :: isLSprofile($dn, $profile)) {
        $retval[] = $profile;
        self :: log_trace("whoami($dn): is '$profile'");
      }
      else
        self :: log_trace("whoami($dn): is NOT '$profile'");
    }

    if (self :: $dn == $dn) {
      $retval[] = 'self';
    }

    self :: log_trace("whoami($dn): '".implode("', '", $retval)."'");
    return $retval;
  }

  /**
   * Return user access right to access to specify LSobject
   *
   * @param[in] $LSobject string The LSobject type
   * @param[in] $dn string The LSobject DN (optional, default: the container_dn of the LSobject type)
   * @param[in] $right string The requested access right ('r' or 'w', optional, default: 'r' or 'w')
   * @param[in] $attr string The requested attribute name (optional, default: any)
   *
   * @retval boolean True is user can access to the specify LSobject, False otherwise
   */
  public static function canAccess($LSobject, $dn=NULL, $right=NULL, $attr=NULL) {
    if (!self :: loadLSobject($LSobject)) {
      return;
    }

    // Access always granted in CLI mode
    if (php_sapi_name() == "cli")
      return true;

    if ($dn) {
      $whoami = self :: whoami($dn);
      if ($dn == self :: getLSuserObject() -> getValue('dn')) {
        if (!self :: in_menu('SELF')) {
          self :: log_trace("canAccess('$LSobject', '$dn', '$right', '$attr'): SELF not in menu");
          return;
        }
      }
      else {
        $obj = new $LSobject();
        $obj -> dn = $dn;
        if (!self :: in_menu($LSobject,$obj -> subDnValue)) {
          self :: log_trace("canAccess('$LSobject', '$dn', '$right', '$attr'): $LSobject (for subDN='".$obj -> subDnValue."') not in menu");
          return;
        }
      }
    }
    else {
      $objectdn=LSconfig :: get('LSobjects.'.$LSobject.'.container_dn').','.self :: getTopDn();
      self :: log_trace("canAccess('$LSobject', '$dn', '$right', '$attr'): use object $LSobject container DN => '$objectdn'");
      $whoami = self :: whoami($objectdn);
    }

    // On specific attribute
    if ($attr) {
      if ($attr=='rdn') {
        $attr=LSconfig :: get('LSobjects.'.$LSobject.'.rdn');
        self :: log_trace("canAccess('$LSobject', '$dn', '$right', 'rdn'): RDN attribute = $attr");
      }
      if (!is_array(LSconfig :: get('LSobjects.'.$LSobject.'.attrs.'.$attr))) {
        self :: log_warning("canAccess('$LSobject', '$dn', '$right', '$attr'): Attribute '$attr' doesn't exists");
        return;
      }

      $r = 'n';
      foreach($whoami as $who) {
        $nr = LSconfig :: get('LSobjects.'.$LSobject.'.attrs.'.$attr.'.rights.'.$who);
        if($nr == 'w') {
          self :: log_trace("canAccess('$LSobject', '$dn', '$right', '$attr'): grant WRITE access via LSprofile '$who'.");
          $r = 'w';
        }
        else if($nr == 'r') {
          if ($r=='n') {
            self :: log_trace("canAccess('$LSobject', '$dn', '$right', '$attr'): grant READ access via LSprofile '$who'.");
            $r='r';
          }
        }
      }
      self :: log_trace("canAccess($LSobject,$dn,$right,$attr): right detected = '$r'");

      if (($right=='r')||($right=='w')) {
        return self :: checkRight($right, $r);
      }
      else {
        if ( ($r=='r') || ($r=='w') ) {
          return true;
        }
        return;
      }
    }

    // On any attributes
    $attrs_conf=LSconfig :: get('LSobjects.'.$LSobject.'.attrs');
    if (is_array($attrs_conf)) {
      if (($right=='r')||($right=='w')) {
        foreach($whoami as $who) {
          foreach ($attrs_conf as $attr_name => $attr_config) {
            if (isset($attr_config['rights'][$who]) && self :: checkRight($right, $attr_config['rights'][$who])) {
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
   * Check a requested right against maximum right of a user
   * @param  string $requested  The requested right
   * @param  string $authorized The authorized maximum right
   * @return boolean
   */
  public function checkRight($requested, $authorized) {
    if ($requested == $authorized)
      return true;
    if ($requested == 'r' && $authorized == 'w')
      return true;
    return false;
  }

  /**
   * Check if user can edit a specified object
   *
   * @param[in] string $LSobject The LSobject type
   * @param[in] string $dn The DN of the object (optional, default: the container_dn of the LSobject type)
   * @param[in] string $attr The attribue name of attribute to check (optional, default: any attributes)
   *
   * @retval boolean True if user is granted, false otherwise
   */
  public static function canEdit($LSobject, $dn=NULL, $attr=NULL) {
    return self :: canAccess($LSobject, $dn, 'w', $attr);
  }

  /**
   * Check if user can remove a specified object
   *
   * @param[in] string $LSobject The LSobject type
   * @param[in] string $dn The DN of the object (optional, default: the container_dn of the LSobject type)
   *
   * @retval boolean True if user is granted, false otherwise
   */
  public static function canRemove($LSobject, $dn) {
    return self :: canAccess($LSobject, $dn, 'w', 'rdn');
  }

  /**
   * Check if user can create a specific object type
   *
   * @param[in] string $LSobject The LSobject type
   *
   * @retval boolean True if user is granted, false otherwise
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
   * Check user right to compute the result of a LSformat
   *
   * @param[in] $LSformat string The LSformat string to check
   * @param[in] $LSobject string The LSobject type
   * @param[in] $dn string The LSobject DN (optional, default: the container_dn of the LSobject type)
   *
   * @retval boolean True is user can compute the result of the LSformat, False otherwise
   */
  public static function canComputeLSformat($LSformat, $LSobject, $dn=NULL) {
    foreach (getFieldInFormat($LSformat) as $attr)
      if (!self :: canAccess($LSobject, $dn, 'r', $attr))
        return false;
    return true;
  }

  /**
   * Check user right to manage a specified relation of specified object
   *
   * @param[in] string $dn The LSobject DN (optional, default: the container_dn of the LSobject type)
   * @param[in] string $LSobject The LSobject type
   * @param[in] string $relationName The relation name of the object
   * @param[in] string $right The right to check (possible values: 'r' or 'w', optional, default: any)
   *
   * @retval boolean True if user is granted, false otherwise
   */
  public static function relationCanAccess($dn,$LSobject,$relationName,$right=NULL) {
    $relConf=LSconfig :: get('LSobjects.'.$LSobject.'.LSrelation.'.$relationName);
    if (!is_array($relConf)) {
      self :: log_trace("relationCanAccess($dn,$LSobject,$relationName,$right): unknown relation");
      return;
    }

    // Access always granted in CLI mode
    if (php_sapi_name() == "cli")
      return true;

    $whoami = self :: whoami($dn);
    self :: log_trace("relationCanAccess($dn,$LSobject,$relationName,$right): whoami = ".varDump($whoami));

    if (($right=='w') || ($right=='r')) {
      $r = 'n';
      foreach($whoami as $who) {
        $nr = ((isset($relConf['rights'][$who]))?$relConf['rights'][$who]:'');
        if($nr == 'w') {
          self :: log_trace("relationCanAccess($dn,$LSobject,$relationName,$right): grant WRITE access via LSprofile '$who'.");
          $r = 'w';
        }
        else if($nr == 'r') {
          if ($r=='n') {
            self :: log_trace("relationCanAccess($dn,$LSobject,$relationName,$right): grant READ access via LSprofile '$who'.");
            $r='r';
          }
        }
      }
      self :: log_trace("relationCanAccess($dn,$LSobject,$relationName,$right): right detected = '$r'");

      if (self :: checkRight($right, $r)) {
        return true;
      }
    }
    else {
      foreach($whoami as $who) {
        if ((isset($relConf['rights'][$who])) && ( ($relConf['rights'][$who] == 'w') || ($relConf['rights'][$who] == 'r') ) ) {
          self :: log_trace("relationCanAccess($dn,$LSobject,$relationName,$right): granted via LSprofile '$who'.");
          return true;
        }
      }
    }
    return;
  }

  /**
   * Check user right to edit a specified relation of specified object
   *
   * @param[in] string $dn The LSobject DN (optional, default: the container_dn of the LSobject type)
   * @param[in] string $LSobject The LSobject type
   * @param[in] string $relationName The relation name of the object
   *
   * @retval boolean True if user is granted, false otherwise
   */
  public static function relationCanEdit($dn, $LSobject, $relationName) {
    return self :: relationCanAccess($dn, $LSobject, $relationName, 'w');
  }

  /**
   * Check user right to execute a customAction on specified object
   *
   * @param[in] string $dn The LSobject DN
   * @param[in] string $LSobject The LSobject type
   * @param[in] string $customActionName The customAction name
   *
   * @retval boolean True if user is granted, false otherwise
   */
  public static function canExecuteCustomAction($dn, $LSobject, $customActionName) {
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
   * Check user right to execute a customAction on a specifed search
   *
   * @param[in] string $LSsearch The LSsearch search
   * @param[in] string $customActionName The customAction name
   *
   * @retval boolean True if user is granted, false otherwise
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
   * @retval boolean True if user is granted, false otherwise
   */
  public static function canAccessLSaddonView($LSaddon,$viewId) {
    if (self :: loadLSaddon($LSaddon)) {
      if (!isset(self :: $LSaddonsViews[$LSaddon]) || !isset(self :: $LSaddonsViews[$LSaddon][$viewId]))
      return;
      if (!is_array(self :: $LSaddonsViews[$LSaddon][$viewId]['allowedLSprofiles'])) {
        return true;
      }
      $whoami = self :: whoami(self :: getTopDn());

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
   * Add a temporary file that stored a specifed value
   *
   * @param[in] string $value The value stored in the temporary file
   * @param[in] string $filePath The temporary file path
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   **/
  public static function addTmpFile($value, $filePath) {
    $hash = mhash(MHASH_MD5,$value);
    self :: $tmp_file[$filePath] = $hash;
    $_SESSION['LSsession']['tmp_file'][$filePath] = $hash;
  }

  /**
   * Return the path of a temporary file that store the specified value (is exists)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $value The value stored in the temporary file
   *
   * @retval string|false The temporary file path if exists, False otherwise
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
   * Return the path of a temporary file that store the specified value
   *
   * The temporary file will be created if not already exists.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $value The value to store in the temporary file
   *
   * @retval string|false The path of the temporary file, false in case of error
   **/
  public static function getTmpFile($value) {
    $path = self :: tmpFileExist($value);
    if (!$path) {
      $path = LS_TMP_DIR_PATH .rand().'.tmp';
      $fp = fopen($path, "w");
      fwrite($fp, $value);
      fclose($fp);
      self :: addTmpFile($value, $path);
    }
    return $path;
  }

  /**
   * Return the URL of a temporary file that store the specified value
   *
   * The temporary file will be created if not already exists.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $value The value to store in the temporary file
   *
   * @retval string|false The URL of the temporary file, false in case of error
   **/
  public static function getTmpFileURL($value) {
    $path = self :: getTmpFile($value);
    if ($path && is_file($path))
      return "tmp/".basename($path);
    return False;
  }

  /**
   * Return the path of a temporary file specified by its filename (if exists)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $filename The filename
   *
   * @retval string|false The path of the temporary file if found, false otherwise
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
   * Delete one or all temporary files
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] string $filePath A specific temporary file path to delete
   *                             (optional, default: all temporary files wil be deleted)
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
   * Check if LSprofiles cache is enabled
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if LSprofiles cache is enabled, false otherwise.
   */
  public static function cacheLSprofiles() {
    return LSconfig :: get(
      'cacheLSprofiles',
      LSconfig :: get('cacheLSprofiles', false, 'bool'), // Default
      'bool',
      self :: $ldapServer
    );
  }

  /**
   * Check if subDn cache is enabled
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if subDn cache is enabled, false otherwise.
   */
  public static function cacheSudDn() {
    return LSconfig :: get(
      'cacheSubDn',
      LSconfig :: get('cacheSubDn', false, 'bool'), // Default
      'bool',
      self :: $ldapServer
    );
  }

  /**
   * Check if searchs cache is enabled
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if searchs cache is enabled, false otherwise.
   */
  public static function cacheSearch() {
    return LSconfig :: get(
      'cacheSearch',
      LSconfig :: get('cacheSearch', false, 'bool'), // Default
      'bool',
      self :: $ldapServer
    );
  }

  /**
   * Check if global search is enabled
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean True if global search is enabled, false instead
   */
  public static function globalSearch() {
    return LSconfig :: get(
      'globalSearch',
      LSconfig :: get('globalSearch', true, 'bool'), // Default
      'bool',
      self :: $ldapServer
    );
  }

  /**
   * Retrieve label of current LDAP server subDn
   *
   * Note: the label is returned untranslated.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval string The label of current LDAP server subDn
   */
  public static function getSubDnLabel() {
    return __(
      LSconfig :: get(
        'subDnLabel',
        ___('Level'), // default value (to translate)
        'string',
        self :: $ldapServer
      )
    );
  }

  /**
   * Return the name of a specifed subDn
   *
   * @param[in] $subDn string The subDn (optional, default: the current one)
   *
   * @retval string The name of the current subDn if found or an empty string otherwise
   */
  public static function getSubDnName($subDn=false) {
    if (!$subDn) {
      $subDn = self :: getTopDn();
    }
    $subDns = self :: getSubDnLdapServer(false);
    if (is_array($subDns)) {
      if (isset($subDns[$subDn])) {
        return $subDns[$subDn];
      }
    }
    return '';
  }

  /**
   * Check if object type is used to list current LDAP server subDns
   *
   * @param[in] $type string The LSobject type
   *
   * @retval boolean True if specified object type is used to list current LDAP server subDns, false otherwise
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
   * Check if specified LSobject type is in current interface menu
   *
   * @param[in] $type string The LSobject type
   * @param[in] $topDn string The topDn to check (optional, default: current one)
   *
   * @retval boolean True if specified LSobject type is in current interface menu, false otherwise
   */
  public static function in_menu($LSobject, $topDn=NULL) {
    if (!$topDn) {
      $topDn = self :: getTopDn();
    }
    return isset(self :: $LSaccess[$topDn][$LSobject]);
  }

  /**
   * Check if current LDAP server have subDns
   *
   * @retval boolean True if current LDAP server have subDns, false otherwise
   */
  public static function haveSubDn() {
    return (isset(self :: $ldapServer['subDn']) && is_array(self :: $ldapServer['subDn']));
  }

  /**
   * Add an information to display to user (on next displayed page or in API result)
   *
   * @param[in] $msg string The message
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
    LSerror :: addErrorCode(
      'LSsession_27',
      array(
        'old' => 'LSsession :: redirect()',
        'new' => 'LSurl :: redirect()',
        'context' => LSlog :: get_debug_backtrace_context(),
      )
    );
    LSurl :: redirect($url);
  }

  /**
   * Return the sender email address configured for the current LDAP server
   *
   * @retval string The sender email address (if configured), false otherwise
   */
  public static function getEmailSender() {
    return (
      is_array(self :: $ldapServer) && isset(self :: $ldapServer['emailSender']) && self :: $ldapServer['emailSender']?
      self :: $ldapServer['emailSender']:
      null
    );
  }

  /**
   * Redirect to default view (if defined)
   *
   * @retval void
   */
  public static function redirectToDefaultView($force=false) {
    if (isset(self :: $ldapServer['defaultView'])) {
      if (array_key_exists(self :: $ldapServer['defaultView'], self :: $LSaccess[self :: getTopDn()])) {
        LSurl :: redirect('object/'.self :: $ldapServer['defaultView']);
      }
      elseif (array_key_exists(self :: $ldapServer['defaultView'], self :: $LSaddonsViewsAccess)) {
        $addon = self :: $LSaddonsViewsAccess[self :: $ldapServer['defaultView']];
        LSurl :: redirect('addon/'.urlencode(self :: $LSaddonsViewsAccess[self :: $ldapServer['defaultView']]['LSaddon'])."/".urlencode(self :: $LSaddonsViewsAccess[self :: $ldapServer['defaultView']]['id']));
      }
    }
    if ($force)
     LSurl :: redirect();
  }

  /**
   * Add help info
   *
   * @param[in] $group string The group name of this information
   * @param[in] $info array Array of the information to add (name => value)
   *
   * @retval void
   */
  public static function addHelpInfos($group, $info) {
    LStemplate :: addHelpInfo($group, $info);
    LSerror :: addErrorCode(
      'LSsession_27',
      array(
        'old' => 'LStemplate :: addHelpInfo()',
        'new' => 'LStemplate :: addHelpInfo()',
        'context' => LSlog :: get_debug_backtrace_context(),
      )
    );
  }

 /**
  * Define error codes relative to LSsession PHP class
  *
  * Note: could not be directly defined after PHP class declaration (like in othe class files)
  * because LSerror is not already loaded and initialized. It's done on self :: startLSerror().
  *
  * @retval void
  */
  private static function defineLSerrors() {
    /*
     * Error Codes
     */
    LSerror :: defineError('LSsession_01',
    ___("LSsession : The constant '%{const}' is not defined.")
    );
    LSerror :: defineError('LSsession_02',
    ___("LSsession : The addon '%{addon}' support is uncertain. Verify system compatibility and the add-on configuration.")
    );
    LSerror :: defineError('LSsession_03',
    ___("LSsession : LDAP server's configuration data are invalid. Can't connect.")
    );
    LSerror :: defineError('LSsession_04',
    ___("LSsession : Failed to load LSobject type '%{type}' : unknon type.")
    );
    LSerror :: defineError('LSsession_05',
    ___("LSsession : Failed to load LSclass '%{class}'.")
    );
    LSerror :: defineError('LSsession_06',
    ___("LSsession : Login or password incorrect.")
    );
    LSerror :: defineError('LSsession_07',
    ___("LSsession : Impossible to identify you : Duplication of identities.")
    );
    LSerror :: defineError('LSsession_08',
    ___("LSsession : Can't load class of authentification (%{class}).")
    );
    LSerror :: defineError('LSsession_09',
    ___("LSsession : Can't connect to LDAP server.")
    );
    LSerror :: defineError('LSsession_10',
    ___("LSsession : Impossible to authenticate you.")
    );
    LSerror :: defineError('LSsession_11',
    ___("LSsession : Your are not authorized to do this action.")
    );
    LSerror :: defineError('LSsession_12',
    ___("LSsession : Some informations are missing to display this page.")
    );
    LSerror :: defineError('LSsession_13',
    ___("LSsession : The function '%{function}' of the custom action '%{customAction}' does not exists or is not configured.")
    );
    LSerror :: defineError('LSsession_14',
    ___("LSsession : Fail to retrieve user's LDAP credentials from LSauth.")
    );
    LSerror :: defineError('LSsession_15',
    ___("LSsession : Fail to reconnect to LDAP server with user's LDAP credentials.")
    );
    LSerror :: defineError('LSsession_16',
    ___("LSsession : No import/export format define for this object type.")
    );
    LSerror :: defineError('LSsession_17',
    ___("LSsession : Error during creation of list of levels. Contact administrators. (Code : %{code})")
    );
    LSerror :: defineError('LSsession_18',
    ___("LSsession : The password recovery is disabled for this LDAP server.")
    );
    LSerror :: defineError('LSsession_19',
    ___("LSsession : Some informations are missing to recover your password. Contact administrators.")
    );
    LSerror :: defineError('LSsession_20',
    ___("LSsession : Error during password recovery. Contact administrators.(Step : %{step})")
    );
    LSerror :: defineError('LSsession_21',
    ___("LSsession : The function '%{func}' configured for the view '%{view}' of the LSaddon '%{addon}' is not declared in the LSaddon file.")
    );
    LSerror :: defineError('LSsession_22',
    ___("LSsession : Failed to load resource file '%{file}'.")
    );
    LSerror :: defineError('LSsession_23',
    ___("LSsession : The function '%{func}' configured for the view '%{view}' of the LSaddon '%{addon}' doesn't exist.")
    );
    LSerror :: defineError('LSsession_24',
    ___("LSsession : invalid related object's DN pass in parameter.")
    );
    LSerror :: defineError('LSsession_25',
    ___("LSsession : the LSaddon %{addon} keep using old-style addon view URL. Please upgrade it.")
    );
    LSerror :: defineError('LSsession_26',
    ___("LSsession : You have been redirect from an old-style URL %{url}. Please upgrade this link.")
    );
    LSerror :: defineError('LSsession_27',
    ___("LSsession : You always seem to use %{old} in your custom code: Please upgrade it and use %{new}.<pre>\nContext:\n%{context}</pre>")
    );
  }

  /**
   * Ajax method when change ldapserver on login/recoveryPassword form
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
        LSerror :: addErrorCode(
          'LSsession_21',
          array(
            'func' => $func -> getName(),
            'addon' => $addon,
            'view' => $viewId,
          )
        );
      }
    }
    else {
      LSerror :: addErrorCode(
        'LSsession_23',
        array(
          'func' => $viewFunction,
          'addon' => $addon,
          'view' => $viewId,
        )
      );
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
