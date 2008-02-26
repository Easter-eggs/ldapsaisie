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
 * Base d'une r�gle de validation de donn�es
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule {
  
  /**
   * Constructeur
  *
  * @author Benjamin Renard <brenard@easter-eggs.com>*
  */
  function LSformRule () {
    return true;
  }
  
  /**
   * Validation de donn�es
  *
  * @param  mixed $value Donn�es � valider
  * @param array $options Options de validation
  * @param object $formElement L'objet formElement attach�
  *
  * @return boolean True si les donn�es sont valide, False sinon.
  */
  function validate($value,$options=NULL,$formElement) {
    return true;
  }
}

?>
