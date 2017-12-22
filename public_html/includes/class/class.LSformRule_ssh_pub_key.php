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
 * LSformRule to check SSH public key
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_ssh_pub_key extends LSformRule {
  
  /**
   * Validate SSH public key value
   *
   * @param string $values The value to validate
   * @param array $options Validation options
   * @param object $formElement The related formElement object
   *
   * @return boolean true if the value is valide, false if not
   */ 
  function validate($value,$options,$formElement) {
    if (preg_match('/^(ssh-[a-z0-9]+) +([^ ]+) +(.*)$/', $value, $m)) {
      $data=@base64_decode($m[2]);
      if (is_string($data))
        return true;
    }
    return false;
  }

}
