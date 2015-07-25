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
 * Rule to validate LSformRule_LSformElement_select valid values
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_LSformElement_select_validValue extends LSformRule {
  
  /**
   * Validate value
   *
   * @param string $values The value to validate
   * @param array $options Validation options
   * @param object $formElement The related formElement object
   *
   * @return boolean true if the value is valide, false if not
   */ 
  function validate($value,$option,$formElement) {
    $ret = $formElement -> isValidValue($value);
    if ($ret===False) return False;
    return True;
  }

}
