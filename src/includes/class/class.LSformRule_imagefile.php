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

LSsession :: loadLSclass('LSformRule_mimetype');

/**
 * Règle de validation : fichier de type image
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_imagefile extends LSformRule {

  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation :
   *                              - Type MIME : $options['params']['mimeType']
   *                              - Type MIME (regex) : $options['params']['mimeTypeRegEx']
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */
  public static function validate($value, $options=array(), &$formElement) {
    $file = LSsession :: getTmpFile($value);

    $mimetype = mime_content_type($file);

    $mimeType = LSconfig :: get('params.mimeType', null, null, $options);
    $mimeTypeRegEx = LSconfig :: get('params.mimeTypeRegEx', null, null, $options);
    if ( is_null($mimeType) && is_null($mimeTypeRegEx)) {
      $options = array(
        'params' => array(
          'mimeTypeRegEx' => '/image\/.*/'
        )
      );
    }

    return LSformRule_mimetype :: validate($value,$options,$formElement);
  }

}
