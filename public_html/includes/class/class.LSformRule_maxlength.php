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
 * Règle de validation : longueur maximale de la valeur.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_maxlength extends LSformRule {

  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation : 
   *                              - Longueur max : $options['params']['limit']
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  function validate ($value,$options,$formElement) {
    if(!isset($options['params']['limit'])) {
      LSerror :: addErrorCode('LSformRule_01',array('type' => 'maxlength', 'param' => 'limit'));
      return;
    }
    return (strlen($value)<=$options['params']['limit']);
  }
  
}
