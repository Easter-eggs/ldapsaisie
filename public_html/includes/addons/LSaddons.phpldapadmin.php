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

// Error messages

// Support
LSerror :: defineError('PHPLDAPADMIN_SUPPORT_01',
  __("PhpLdapAdmin Support : The constant %{const} is not defined.")
);


/**
 * Verify support of PhpLdapAdmin by LdapSaisie
 * 
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval boolean true if is supported, false also
 */
function LSaddon_phpldapadmin_support() {
  $retval=true;

  $MUST_DEFINE_CONST= array(
    'LS_PHPLDAPADMIN_VIEW_OBJECT_URL_FORMAT'
  );

  foreach($MUST_DEFINE_CONST as $const) {
    if ( (!defined($const)) || (constant($const) == "")) {
      LSerror :: addErrorCode('PHPLDAPADMIN_SUPPORT_01',$const);
      $retval=false;
    }
  }

  return $retval;
}

/**
 * Redirect to PhpLdapAdmin view object page
 * 
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval boolean true in all cases
 */
function redirectToPhpLdapAdmin(&$ldapObject) {
  $url = $ldapObject->getFData(LS_PHPLDAPADMIN_VIEW_OBJECT_URL_FORMAT);
   LSsession::redirect($url);
   return true;
}
