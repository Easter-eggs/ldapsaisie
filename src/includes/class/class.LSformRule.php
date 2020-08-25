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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Base d'une règle de validation de données
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule extends LSlog_staticLoggerClass {

  /**
   * Validation de données
  *
  * @param  mixed $value Données à valider
  * @param array $options Options de validation
  * @param object $formElement L'objet formElement attaché
  *
  * @return boolean True si les données sont valide, False sinon.
  */
  public static function validate($value,$options=NULL,$formElement) {
    return true;
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSformRule_01',
___("LSformRule_%{type} : Parameter %{param} is not found.")
);
