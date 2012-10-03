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

LSsession :: loadLSclass('LSformElement_supannLabeledValue');
LSsession :: loadLSaddon('supann');

/**
 * Element supannTypeEntite d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannTypeEntite des formulaires.
 * Elle etant la classe basic LSformElement_supannLabeledValue.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannTypeEntite extends LSformElement_supannLabeledValue {

 /**
  * Traduit une valeur
  *
  * @param[in] $value La valeur
  *
  * @retval array Un tableau cle->valeur contenant translated et label ou False
  **/
  function translate($value) {
    return supannTranslateRoleEntiteValue('type',$value); 
  }


}

