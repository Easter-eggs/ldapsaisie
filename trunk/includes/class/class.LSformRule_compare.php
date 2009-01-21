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
 * Règle de validation par comparaison de valeurs.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_compare extends LSformRule {
  
  /**
   * Retourne l'operateur de comparaison.
   *
   * @access private
   * @param  string  Nom de l'operateur
   *
   * @return string  Operateur à utiliser
   */
  function _findOperator($operator_name) {

    $_operators = array(
        'eq'  => '==',
        'neq' => '!=',
        'gt'  => '>',
        'gte' => '>=',
        'lt'  => '<',
        'lte' => '<='
    );

    if (empty($operator_name)) {
      return '==';
    } elseif (isset($this->_operators[$operator_name])) {
      return $this->_operators[$operator_name];
    } elseif (in_array($operator_name, $this->_operators)) {
      return $operator_name;
    } else {
      return '==';
    }
  }

  /**
   * Vérification des valeurs.
   *
   * @param string $values Valeurs à vérifier
   * @param array $options Options de validation : 
   *                              - Operateur : $options['params']['operator']
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  function validate ($values,$options=array(),$formElement) {
    if (!isset($options['params']['operator'])) {
      LSerror::addErrorCode('LSformRule_01',array('type' => 'compare', 'param' => 'operator');
      return;
    }
    $operator = LSformRule_compare :: _findOperator($options['params']['operator']);
    if ('==' != $operator && '!=' != $operator) {
      $compareFn = create_function('$a, $b', 'return floatval($a) ' . $operator . ' floatval($b);');
    }
    else {
      $compareFn = create_function('$a, $b', 'return $a ' . $operator . ' $b;');
    }
    return $compareFn($values[0], $values[1]);
  }
  
}

?>
