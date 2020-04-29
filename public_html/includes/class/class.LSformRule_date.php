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
 * Base d'une règle de validation d'une date
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_date extends LSformRule {

  /**
   * Validation de données
  *
  * @param  mixed $value Données à valider
  * @param array $options Options de validation
  *                         $options['params']['format']: le format de la date
  * @param object $formElement L'objet formElement attaché
  *
  * @return boolean True si les données sont valide, False sinon.
  */
  public static function validate($value,$options=NULL,$formElement) {
    $format = LSconfig :: get('params.format', null, 'string', $options);
    if (is_null($format)) {
      LSerror :: addErrorCode('LSformRule_date_01');
      return;
    }
    $date = strptime($value, $format);
    if(is_array($date)) {
      $res = mktime($date['tm_hour'],$date['tm_min'],$date['tm_sec'],$date['tm_mon']+1,$date['tm_mday'],$date['tm_year']+1900);
      if ((is_int($res)) && ($res != -1) && ($res !== False)) {
        return true;
      }
    }
    return;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSformRule_date_01',
_("LSformRule_date : No date format specify.")
);
