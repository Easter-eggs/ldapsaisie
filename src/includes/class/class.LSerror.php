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
   * Define an error
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $code string The error code
   * @param[in] $msg LSformat The LSformat of the error message
   * @param[in] $escape boolean Set to false if you don't want the message
   *                            to be escaped on display (Default: true)
   *
   * @retval void
   */
  public static function defineError($code=-1, $msg='', $escape=True) {
    self :: $_errorCodes[$code] = array(
      'msg' => $msg,
      'escape' => boolval($escape),
    );
  }

  /**
   * Add an error
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $code string The error code
   * @param[in] $msg mixed If error code is not specified (or not defined), it could content the
   *                       the error message. If error code is provided (and defined), this parameter
   *                       will be used to format registred error message (as LSformat). In this case,
   *                       it could be any of data support by getFData function as $data parameter.
   *
   * @param[in] $escape boolean Set to false if you don't want the message
   *                            to be escaped on display (Default: true)
   *
   * @retval void
   */
  public static function addErrorCode($code=null, $msg=null, $escape=true) {
    $_SESSION['LSerror'][] = self :: formatError($code, $msg, $escape);
    if (class_exists('LSlog'))
      LSlog :: error(self :: formatError($code, $msg, $escape, 'addslashes'));
  }

  /**
   * Show errors
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $return boolean True if you want to retreived errors message. If false,
   *                            (default), LSerrors template variable will be assigned
   *                            with errors message.
   *
   * @retval string|null
   */
  public static function display($return=False) {
    $errors = self :: getErrors();
    if ($errors) {
      if ($return) {
        return $errors;
      }
      LStemplate :: assign('LSerrors', $errors);
    }
    return;
  }

  /**
   * Print errors and stop LdapSaisie
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $code Error code (see addErrorCode())
   * @param[in] $msg Error msg (see addErrorCode())
   * @param[in] $escape boolean (see addErrorCode())
   *
   * @retval void
   */
  public static function stop($code=-1, $msg='', $escape=true) {
    if(!empty($_SESSION['LSerror'])) {
      print "<h1>"._('Errors')."</h1>\n";
      print self :: display(true);
    }
    print "<h1>"._('Stop')."</h1>\n";
    exit (self :: formatError($code, $msg, $escape));
  }

 /**
  * Format current errors message
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat string Le texte des erreurs
  */
  public static function getErrors() {
    if(!empty($_SESSION['LSerror'])) {
      $txt = '';
      foreach ($_SESSION['LSerror'] as $error)
        $txt .= $error."<br />\n";
      self :: resetError();
      return $txt;
    }
    return;
  }

 /**
  * Format error message
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat string Le texte des erreurs
  */
  private static function formatError($code=null, $message=null, $escape=True, $escape_method=null) {
    if ($code && array_key_exists($code, self :: $_errorCodes)) {
      $message = getFData(__(self :: $_errorCodes[$code]['msg']), $message);
      if (!self :: $_errorCodes[$code]['escape'] === false)
        $escape = false;
    }
    else if (!$message) {
      if ($code)
        $message = $code;
      else
        $message = _("Unknown error");
    }

    if ($escape !== false) {
      if (is_null($escape_method) || !is_callable($escape_method))
        $escape_method = 'htmlentities';
      $code = call_user_func($escape_method, $code);
      $message = call_user_func($escape_method, $message);
    }

    return ($code?"(Code $code) ":"").$message;
  }

 /**
  * Check if some errors are defined
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retvat boolean
  */
  public static function errorsDefined() {
    return !empty($_SESSION['LSerror']);
  }

 /**
  * Clear current errors
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
      LSerror :: addErrorCode(0, $data -> getMessage());
      return true;
    }
    return false;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError(-1, ___("Unknown error : %{error}"));
