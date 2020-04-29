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
 * Règle de validation : mime_type d'un fichier
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_mimetype extends LSformRule {

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
  public static function validate ($value,$options,$formElement) {
    $file = LSsession :: getTmpFile($value);
    $real_mimetype = mime_content_type($file);

    $mimetype = LSconfig :: get('params.mimeType', null, null, $options);
    if (!is_null($mimetype)) {
      if (is_array($mimetype)) {
        if (!in_array($real_mimetype, $mimetype))
          return;
      }
      else {
        if ($real_mimetype != $mimetype)
          return;
      }
    }

    $mimeTypeRegEx = LSconfig :: get('params.mimeTypeRegEx', null, 'string', $options);
    if (is_string($mimeTypeRegEx) && !preg_match($mimeTypeRegEx, $real_mimetype))
      return false;

    return true;
  }

}
