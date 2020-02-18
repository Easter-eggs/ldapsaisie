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
 * Règle de validation : taille d'un fichier
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_filesize extends LSformRule {

  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation :
   *                              - Taille max (en octet) : $options['params']['maxSize']
   *                              - Taille min (en octet) : $options['params']['minSize']
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  public static function validate ($value,$options,$formElement) {
    $file = LSsession :: getTmpFile($value);

    $size = filesize($file);

    $maxSize = LSconfig :: get('params.maxSize', null, 'int', $options);
    if (is_int($maxSize) && $size > $maxSize)
      return;

    $minSize = LSconfig :: get('params.minSize', null, 'int', $options);
    if (is_int($minSize) && $size < $minSize)
      return;

    return true;
  }

}

