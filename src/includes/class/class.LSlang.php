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

class LSlang {


  // Current lang and encoding
  private static $lang = NULL;
  private static $encoding = NULL;

  /**
   * Define current locale (and encoding)
   *
   * @param[in] $lang string|null     The lang (optional, default: default current LDAP
   *                                  server lang, or default lang)
   * @param[in] $encoding string|null The encoding (optional, default: default current LDAP
   *                                  server encoding, or default encoding)
   *
   * @retval void
   */
   public static function setLocale($lang=null, $encoding=null) {
     // Handle $lang parameter
     if (is_null($lang)) {
       if (isset($_REQUEST['lang'])) {
         $lang = $_REQUEST['lang'];
       }
       elseif (isset($_SESSION['LSlang'])) {
         $lang = $_SESSION['LSlang'];
       }
       elseif (isset(LSsession :: $ldapServer['lang'])) {
         $lang = LSsession :: $ldapServer['lang'];
       }
       else {
         $lang = LSconfig :: get('lang');
       }
     }

     // Handle $encoding parameter
     if (is_null($encoding)) {
       if (isset($_REQUEST['encoding'])) {
         $encoding = $_REQUEST['encoding'];
       }
       elseif (isset($_SESSION['LSencoding'])) {
         $encoding = $_SESSION['LSencoding'];
       }
       elseif (isset(LSsession :: $ldapServer['encoding'])) {
         $encoding = LSsession :: $ldapServer['encoding'];
       }
       else {
         $encoding = LSconfig :: get('encoding');
       }
     }

     // Set session and self variables
     $_SESSION['LSlang'] = self :: $lang = $lang;
     $_SESSION['LSencoding'] = self :: $encoding = $encoding;

     // Check
     if (self :: localeExist($lang, $encoding)) {
       LSlog :: debug("LSsession :: setLocale() : Use local '$lang.$encoding'");
       if ($encoding) {
         $lang .= '.'.$encoding;
       }
       // Gettext firstly look the LANGUAGE env variable, so set it
       putenv("LANGUAGE=$lang");

       // Set the locale
       if (setlocale(LC_ALL, $lang) === false)
         LSlog :: error("An error occured setting locale to '$lang'");

       // Configure and set the text domain
       $fullpath = bindtextdomain(LS_TEXT_DOMAIN, LS_I18N_DIR_PATH);
       LSlog :: debug("Text domain fullpath is '$fullpath'.");
       LSlog :: debug("Text domain is : ".textdomain(LS_TEXT_DOMAIN));

       // Include local translation file
       LSsession :: includeFile(LS_I18N_DIR.'/'.$lang.'/lang.php');

       // Include other local translation file(s)
       foreach(array(LS_I18N_DIR_PATH.'/'.$lang, LS_LOCAL_DIR.'/'.LS_I18N_DIR.'/'.$lang) as $lang_dir) {
         if (is_dir($lang_dir)) {
           foreach (listFiles($lang_dir, '/^lang.+\.php$/') as $file) {
             $path = "$lang_dir/$file";
             LSlog :: debug("LSession :: setLocale() : Local '$lang.$encoding' : load translation file '$path'");
             include($path);
           }
         }
       }
     }
     else {
       if ($encoding && $lang) $lang .= '.'.$encoding;
       LSlog :: error("The local '$lang' does not exists, use default one.");
     }
   }

  /**
   * Return list of available languages
   *
   * @retval array List of available languages.
   **/
   public static function getLangList() {
     $list = array('en_US');
     if (self :: $encoding) {
       $regex = '/^([a-zA-Z_]*)\.'.self :: $encoding.'$/';
     }
     else {
       $regex = '/^([a-zA-Z_]*)$/';
     }
     foreach(array(LS_I18N_DIR_PATH, LS_LOCAL_DIR.'/'.LS_I18N_DIR) as $lang_dir) {
       if (!is_dir($lang_dir))
       continue;
       if ($handle = opendir($lang_dir)) {
         while (false !== ($file = readdir($handle))) {
           if(is_dir("$lang_dir/$file")) {
             if (preg_match($regex, $file, $regs)) {
               if (!in_array($regs[1], $list)) {
                 $list[]=$regs[1];
               }
             }
           }
         }
       }
     }
     return $list;
   }

  /**
   * Return current language
   *
   * @param[in] boolean If true, only return the two first characters of the language
   *                    (For instance, 'fr' for 'fr_FR')
   *
   * @retval string The current language (ex: fr_FR, or fr if $short==true)
   **/
   public static function getLang($short=false) {
     if ($short) {
       return strtolower(substr(self :: $lang, 0, 2));
     }
     return self :: $lang;
   }

  /**
   * Return current encoding
   *
   * @retval string The current encoding (ex: UTF8)
   **/
   public static function getEncoding() {
     return self :: $encoding;
   }

  /**
   * Check a locale exists
   *
   * @param[in] $lang string The language (ex: fr_FR)
   * @param[in] $encoding string The encoding (ex: UTF8)
   *
   * @retval boolean True if the locale is available, False otherwise
   **/
   public static function localeExist($lang, $encoding) {
     if ( !$lang && !$encoding ) {
       return;
     }
     $locale=$lang.(($encoding)?'.'.$encoding:'');
     if ($locale == 'en_US.UTF8') {
       return true;
     }
     foreach(array(LS_I18N_DIR_PATH, LS_LOCAL_DIR.'/'.LS_I18N_DIR) as $lang_dir)
       if (is_dir("$lang_dir/$locale"))
         return true;
     return false;
   }

}
