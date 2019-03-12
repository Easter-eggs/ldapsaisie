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
 * Règle de validation : taille d'une image
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_imagesize extends LSformRule {

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
  public static function validate ($value,$options,$formElement) {
    $file = LSsession :: getTmpFile($value);
    list($width, $height, $type, $attr) = getimagesize($file);
    
    if (is_int($options['params']['maxWidth'])) {
      if ($width > $options['params']['maxWidth']) {
        return;
      }
    }
    if (is_int($options['params']['minWidth'])) {
      if ($width < $options['params']['minWidth']) {
        return;
      }
    }
    if (is_int($options['params']['maxHeight'])) {
      if ($height > $options['params']['maxHeight']) {
        return;
      }
    }
    if (is_int($options['params']['minHeight'])) {
      if ($height < $options['params']['minHeight']) {
        return;
      }
    }
    
    return true;
  }
  
}

