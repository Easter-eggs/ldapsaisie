<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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
 * Règle de validation : longueur maximale et minimale de la valeur.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_rangelength extends LSformRule {

  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation :
   *                              - Longueur min : $options['params']['limits'][0]
   *                              - Longueur max : $options['params']['limits'][1]
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  public static function validate($value, $options=array(), &$formElement) {
    $limits = LSconfig :: get('params.limits', null, null, $options);
    if(!is_array($limits) || count($limits) != 2) {
      LSerror :: addErrorCode('LSformRule_01',array('type' => 'rangelength', 'param' => 'limits'));
      return;
    }
    $len = strlen($value);
    if ($len < $limits[0])
      throw new LSformRuleException(
        getFData(_('Value is too short (minimum: %{limit}).'), $limits[0])
      );
    if ($len > $limits[1])
      throw new LSformRuleException(
        getFData(_('Value is too long (maximum: %{limit}).'), $limits[1])
      );
    return True;
  }

}
