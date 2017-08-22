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
 * Validation rule for an integer value
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_integer extends LSformRule{
  
  /**
   * Verification value.
   *
   * @param string $values The value
   * @param array $options Validation options
   *                              - Maximum value : $options['params']['max']
   *                              - Minimum value : $options['params']['min']
   *                              - Allow only negative value : $options['params']['negative']
   *                              - Allow only positive value : $options['params']['positive']
   * @param object $formElement The formElement object
   *
   * @return boolean true if the value is valided, false otherwise
   */
  function validate ($value,$options=array(),$formElement) {
    if($options['params']['max'] && $value > $options['params']['max']) {
      return;
    }
    if($options['params']['min'] && $value < $options['params']['min']) {
      return;
    }
    if($options['params']['negative']) {
      $regex = '/^-[0-9]*$/';
    }
    elseif($options['params']['positive']) {
      $regex = '/^[0-9]*$/';
    }
    else {
      $regex = '/^-?[0-9]*$/';
    }
    LSsession :: loadLSclass('LSformRule_regex');
    return LSformRule_regex :: validate($value,$regex,$formElement);
  }
  
}

