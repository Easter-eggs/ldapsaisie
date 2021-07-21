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
 * Règle de validation : taille d'une image
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_imagesize extends LSformRule {

  // CLI parameters autocompleters
  protected static $cli_params_autocompleters = array(
    'minWidth' => array('LScli', 'autocomplete_int'),
    'maxWitdh' => array('LScli', 'autocomplete_int'),
    'minHeight' => array('LScli', 'autocomplete_int'),
    'maxHeight' => array('LScli', 'autocomplete_int'),
  );

  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation :
   *                              - Largeur max : $options['params']['maxWidth']
   *                              - Largeur min : $options['params']['minWidth']
   *                              - Hauteur max : $options['params']['maxHeight']
   *                              - Hauteur min : $options['params']['minHeight']
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  public static function validate($value, $options=array(), &$formElement) {
    $file = LSsession :: getTmpFile($value);
    list($width, $height, $type, $attr) = getimagesize($file);
    self :: log_debug("validate(): image size is $width x $height, type=$type, attr='$attr'");

    $maxWidth = LSconfig :: get('params.maxWidth', null, 'int', $options);
    if ($maxWidth && $width > $maxWidth) {
      self :: log_debug("validate(): max width error ($width > $maxWidth)");
      return;
    }

    $minWidth = LSconfig :: get('params.minWidth', null, 'int', $options);
    if ($minWidth && $width < $minWidth) {
      self :: log_debug("validate(): min width error ($width < $minWidth)");
      return;
    }

    $maxHeight = LSconfig :: get('params.maxHeight', null, 'int', $options);
    if ($maxHeight && $height > $maxHeight) {
      self :: log_debug("validate(): max height error ($height > $maxHeight)");
      return;
    }

    $minHeight = LSconfig :: get('params.minHeight', null, 'int', $options);
    if ($minHeight && $height < $minHeight) {
      self :: log_debug("validate(): min height error ($height < $minHeight)");
      return;
    }

    return true;
  }

}
