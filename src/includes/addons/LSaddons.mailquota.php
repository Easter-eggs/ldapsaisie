<?php
/*******************************************************************************
 * Copyright (C) 2019 Easter-eggs
 * http://ldapsaisie.org
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

// Messages d'erreur

// Support
LSerror :: defineError('MAILQUOTA_SUPPORT_01',
  ___("MAILQUOTA Support : The constant %{const} is not defined.")
);
LSerror :: defineError('MAILQUOTA_SUPPORT_02',
  ___("MAILQUOTA Support : The IMAP PHP module is not available.")
);

// Other errors
LSerror :: defineError('MAILQUOTA_01',
  ___("MAILQUOTA : Fail to connect on IMAP server : %{error}")
);
LSerror :: defineError('MAILQUOTA_02',
  ___("MAILQUOTA : Unexpected error occured retreiving mailbox quota usage.")
);
LSerror :: defineError('MAILQUOTA_03',
  ___("MAILQUOTA : Fail to compose IMAP mailbox username.")
);
LSerror :: defineError('MAILQUOTA_04',
  ___("MAILQUOTA : No quota information retrieved from IMAP server. Make sure quota is enabled on this IMAP server or/and for this mailbox.")
);

/**
 * Check support of this addons
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @retval boolean True if this addons is fully supported, false otherwise
 */
function LSaddon_mailquota_support() {
  $retval=True;

  $MUST_DEFINE_CONST= array(
    'MAILQUOTA_IMAP_MAILBOX',
    'MAILQUOTA_IMAP_MASTER_USER',
    'MAILQUOTA_IMAP_MASTER_USER_PWD',
    'MAILQUOTA_IMAP_MASTER_USER_FORMAT',
    'MAILQUOTA_IMAP_QUOTA_ROOT_MAILBOX',
  );

  foreach($MUST_DEFINE_CONST as $const) {
    if ( (!defined($const)) || (constant($const) == "")) {
      LSerror :: addErrorCode('MAILQUOTA_SUPPORT_01',$const);
      $retval=false;
    }
  }

  if (!function_exists('imap_open')) {
    LSerror :: addErrorCode('MAILQUOTA_SUPPORT_02');
      $retval=false;
  }

  return $retval;

}

/**
 * Get IMAP mailbox usage
 *
 * @param[in] $ldapobject LSldapObject The LDAP object
 *
 * @retval array|false Array with mailbox usage and quota, or false
 **/
function mailquota_get_usage(&$LSldapObject) {
  $logger = LSlog :: get_logger("LSaddon_mailquota");
  try {
    $LSldapObject -> registerOtherValue('masteruser', MAILQUOTA_IMAP_MASTER_USER);
    $imap_login = $LSldapObject -> getFData(MAILQUOTA_IMAP_MASTER_USER_FORMAT);
    if (empty($imap_login)) {
      LSerror :: addErrorCode('MAILQUOTA_03');
      return false;
    }
    $imap_mailbox = $LSldapObject -> getFData(MAILQUOTA_IMAP_MAILBOX);
    $logger -> debug("Connect on IMAP mailbox '$imap_mailbox' with user $imap_login:xxxxxxx");
    $mbox = @imap_open(
      $imap_mailbox,
      $imap_login,
      MAILQUOTA_IMAP_MASTER_USER_PWD,
      OP_HALFOPEN
    );
    if ($mbox) {
      $quota_values = imap_get_quotaroot($mbox, MAILQUOTA_IMAP_QUOTA_ROOT_MAILBOX);
      $logger -> debug("IMAP mailbox root quota:\n".varDump($quota_values));
      if(isset($quota_values['usage'])) {
        return array (
          'usage' => intval($quota_values['usage']*1024),
          'limit' => intval($quota_values['limit']*1024),
        );
      }
      LSerror :: addErrorCode('MAILQUOTA_04');
    }
    else {
      LSerror :: addErrorCode('MAILQUOTA_01', imap_last_error());
    }
  }
  catch (Exception $e) {
    LSerror :: addErrorCode('MAILQUOTA_02');
  }
  return false;
}

/**
 * Custom action that could be use to show mailbox quota usage
 *
 * This custom action just show mailbox quota usage via LSinfo.
 *
 * The custom action could be configured on LSldapObject as following :
 *
 * 'customActions' => array (
 *       'showmailquotausage' => array (
 *               'function' => 'mailquota_show_usage',
 *               'label' => 'Show mail quota usage',
 *               'noConfirmation' => true,
 *               'disableOnSuccessMsg' => true,
 *               'icon' => 'mail',
 *               'rights' => array (
 *                       'admin'
 *               )
 *       ),
 *       [...]
 * );
 *
 * @param[in] $ldapobject LSldapObject The LDAP object
 *
 * @retval true in any case
 **/
function mailquota_show_usage(&$LSldapObject) {
  $quota = mailquota_get_usage($LSldapObject);
  if (is_array($quota)) {
    $msg = __("Mailbox quota usage : %{usage} / %{limit}");
    $infos = array('usage' => mailquota_formatValue($quota['usage']));
    if ($quota['limit']) {
      $infos['limit'] = mailquota_formatValue($quota['limit']);
      $infos['perc'] = number_format($quota['usage'] * 100 / $quota['limit'], 2);
      $msg .= " (%{perc}%)";
    }
    else {
      $infos['limit'] = __('Unlimited');
    }
    LSsession :: addInfo(getFData($msg, $infos));
    return true;
  }
  return false;
}

function mailquota_formatValue($value) {
  $sizeFacts = array(
    1073741824  => 'Go',
    1048576     => 'Mo',
    1024        => 'Ko',
    1           => 'o',
  );
  krsort($sizeFacts);
  foreach($sizeFacts as $sill => $label) {
    if ($value >= $sill) {
      if ($value % $sill == 0) {
        return $value/$sill.$label;
      }
      else {
        return number_format($value/$sill, 1).$label;
      }
    }
  }
  return $value."o";
}
