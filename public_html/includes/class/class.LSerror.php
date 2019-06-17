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
 * Gestion des erreurs pour LdapSaisie
 *
 * Cette classe gère les retours d'erreurs.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSerror {

  private static $_errorCodes = array(
    '0' => array('msg' => "%{msg}")
  );

  /**
   * Défini une erreur
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $code numeric Le code de l'erreur
   * @param[in] $msg LSformat Le format paramètrable du message de l'erreur
   *
   * @retval void
   */ 
  public static function defineError($code=-1,$msg='') {
    self :: $_errorCodes[$code] = array(
      'msg' => $msg
    );
  }
  
  /**
   * Ajoute une erreur
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $code numeric Le code de l'erreur
   * @param[in] $msg mixed Un tableau ou une chaine pour la construction du message d'erreur
   *                       Tableau : '[clef]' => 'valeur' 
   *                                    La clef sera utilisé dans le format de message d'erreur
   *                                    dans le fichier 'error_code.php'.
   *
   * @retval void
   */ 
  public static function addErrorCode($code=-1,$msg='') {
    $_SESSION['LSerror'][] = array($code,$msg);
    LSlog('[ERROR] '.self::getError(array($code,$msg)));
  }
  
  /**
   * Affiche les erreurs
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $return boolean True pour que le texte d'erreurs soit retourné
   * 
   * @retval void
   */
  public static function display($return=False) {
    $errors = self::getErrors();
    if ($errors) {
      if ($return) {
        return $errors;
      }
      LStemplate :: assign('LSerrors',$errors);
    }
  }

  /**
   * Print errors and stop LdapSaisie
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $code Error code (Goto : addErrorCode())
   * @param[in] $msg Error msg (Goto : addErrorCode())
   * 
   * @retval void
   */
  public static function stop($code=-1,$msg='') {
    if(!empty($_SESSION['LSerror'])) {
      print "<h1>"._('Errors')."</h1>\n";
      print self::display(true);
    }
    print "<h1>"._('Stop')."</h1>\n";
    exit (self::getError(array($code,$msg)));
  }

 /**
  * Retourne le texte des erreurs
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat string Le texte des erreurs
  */
  public static function getErrors() {
    if(!empty($_SESSION['LSerror'])) {
      $txt='';
      foreach ($_SESSION['LSerror'] as $error) {
        $txt.=self::getError($error)."<br />\n";
      }
      self::resetError();
      return $txt;
    }
    return;
  }
  
 /**
  * Retourne le texte d'une erreur
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat string Le texte des erreurs
  */
  private static function getError($error) {
    return ($error[0]?"(Code ".htmlentities($error[0]).") ":"").htmlentities(getFData(self :: $_errorCodes[$error[0]]['msg'],$error[1]));
  }
  
 /**
  * Définir si il y a des erreurs
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat boolean
  */
  public static function errorsDefined() {
    return !empty($_SESSION['LSerror']);
  }
  
 /**
  * Efface les erreurs sotckés
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat void
  */
  private static function resetError() {
    unset ($_SESSION['LSerror']);
  }
  
  /**
   * Check if is Net_LDAP2 error and display possible error message
   * 
   * @param[in] $data mixed Data
   * 
   * @retval boolean True if it's an error or False
   **/
  public static function isLdapError($data) {
    if (Net_LDAP2::isError($data)) {
      LSerror :: addErrorCode(0,$data -> getMessage());
      return true;
    }
    return;
  }

  /**
   * PHP error handler (see set_error_handler() function doc)
   **/
  public static function errorHandler($errno, $errstr, $errfile, $errline) {
    self :: addErrorCode(-2,"Error ".self :: convertPHPErrorNo($errno)." occured in file $errfile (line : $errline) : $errstr");
    if ( E_RECOVERABLE_ERROR===$errno ) {
      throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
    return False;
  }

  /**
   * Convert PHP error number to string
   * 
   * @param[in] $errno int Error number
   * 
   * @retval string Corresponding error name
   **/
  public static function convertPHPErrorNo($errno) {
    $error_levels=array (
      1       => "ERROR",
      2       => "WARNING",
      4       => "PARSE",
      8       => "NOTICE",
      16      => "CORE_ERROR",
      32      => "CORE_WARNING",
      64      => "COMPILE_ERROR",
      128     => "COMPILE_WARNING",
      256     => "USER_ERROR",
      512     => "USER_WARNING",
      1024    => "USER_NOTICE",
      2048    => "STRICT",
      4096    => "RECOVERABLE_ERROR",
      8192    => "DEPRECATED",
      16384   => "USER_DEPRECATED",
      32767   => "ALL",
    );
    if (isset($error_levels[$errno])) {
      return $error_levels[$errno];
    }
    else {
      return "UNKNOWN_ERROR";
    }
  }

}

/*
 * Error Codes
 */
LSerror :: defineError(-1,_("Unknown error : %{error}"));
LSerror :: defineError(-2,_("PHP error : %{error}"));

