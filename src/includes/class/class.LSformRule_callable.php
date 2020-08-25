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
 * LSform rule to check a value using a callable object (function, method, ...)
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_callable extends LSformRule {

  /**
   * Check the value using the callable object
   *
   * @param mixed $value The value to check
   * @param array $options Validation option
   *                              - $options['params']['callable'] : the function use to check the value
   *
   * The callable object will be run to check the value. The given parameters are the
   * same of this method.
   *
   * @param object $formElement The LSformElement object
   *
   * @return boolean true if the value is valid, false otherwise
   */
  public static function validate($value,$options,$formElement) {
    $callable = LSconfig :: get('params.callable', null, null, $options);
    if (is_callable($callable))
      return call_user_func_array(
        $callable,
        array(
          $value,
          LSconfig :: get('params', array(), null, $options),
          &$formElement
        )
      );

    LSerror :: addErrorCode('LSformRule_callable_01');
    return False;
  }

}

/*
 * Error codes
 */
LSerror :: defineError('LSformRule_callable_01',
___("LSformRule_callable : The given callable option is not callable")
);
