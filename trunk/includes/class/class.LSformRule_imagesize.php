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
   *                              - Largeur max : $options['param']['maxWidth']
   *                              - Largeur min : $options['param']['minWidth']
   *                              - Hauteur max : $options['param']['maxHeight']
   *                              - Hauteur min : $options['param']['minHeight']
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  function validate ($value,$options,$formElement) {
    $file = $GLOBALS['LSsession'] -> getTmpFile($value);
    debug('Verify : '.$file.' - Options : '.print_r($options,true));
    list($width, $height, $type, $attr) = getimagesize($file);
    
    if (is_int($options['param']['maxWidth'])) {
      if ($width > $options['param']['maxWidth']) {
        return;
      }
    }
    if (is_int($options['param']['minWidth'])) {
      if ($width < $options['param']['minWidth']) {
        return;
      }
    }
    if (is_int($options['param']['maxHeight'])) {
      if ($height > $options['param']['maxHeight']) {
        return;
      }
    }
    if (is_int($options['param']['minHeight'])) {
      if ($height < $options['param']['minHeight']) {
        return;
      }
    }
    
    return true;
  }
  
}

?>
