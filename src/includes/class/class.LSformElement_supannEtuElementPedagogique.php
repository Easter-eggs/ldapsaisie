<?php
/*******************************************************************************
 * Copyright (C) 2021 Easter-eggs
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

LSsession :: loadLSclass('LSformElement_supannLabeledValue');
LSsession :: loadLSaddon('supann');

/**
 * Element supannEtuElementPedagogique d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannEtuElementPedagogique des formulaires.
 * Elle etant la classe basic LSformElement_supannLabeledValue.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannEtuElementPedagogique extends LSformElement_supannLabeledValue {

  var $supannNomenclatureTable = 'etuElementPedagogique';

}
