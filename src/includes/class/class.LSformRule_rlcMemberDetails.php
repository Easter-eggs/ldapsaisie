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
 * LSformRule to check rlcMemberDetails attribute value
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_rlcMemberDetails extends LSformRule {

  // Validate values one by one or all together
  public const validate_one_by_one = False;

  /**
   * Validate rlcMemberDetails attribute value
   *
   * @param string $values The value to validate
   * @param array $options Validation options
   * @param object $formElement The related formElement object
   *
   * @return boolean true if the value is valide, false if not
   */
  public static function validate($value, $options=array(), &$formElement) {
    $members = array();
    foreach($value as $json_value) {
      if (is_empty($json_value)) {
        self :: log_error("validate($json_value): Empty value are not authorized");
        return false;
      }
      $v = json_decode($json_value);
      if (is_null($v)) {
        self :: log_error("validate($json_value): fail to decode JSON value");
        return false;
      }

      if (!isset($v->role) || is_empty($v->role) || !isset($v->uid) || is_empty($v->uid)) {
        self :: log_error("validate($json_value): invalid value (no uid or role)");
        return false;
      }

      if (!in_array($v->role, array('facilitator', 'governing_board_referent', 'contributor', 'observer'))) {
        self :: log_error("validate($json_value): invalid role $v->role");
        return false;
      }

      if (!array_key_exists($v->role, $members))
        $members[$v->role] = array();
      if (in_array($v->uid, $members[$v->role])) {
        self :: log_error("validate($json_value): member $v->uid duplicated as $v->role");
        return false;
      }
      $members[$v->role][] = $v->uid;
    }
    self :: log_debug("validate(): members: ".print_r($members, true));
    foreach (array('facilitator', 'governing_board_referent', 'contributor') as $role) {
      if (!array_key_exists($role, $members)) {
        self :: log_error("validate(): no $role");
        return false;
      }
    }
    if (count($members['governing_board_referent']) > 1) {
      self :: log_error("validate(): more than one governing_board_referent found");
      return false;
    }
    return True;
  }
}
