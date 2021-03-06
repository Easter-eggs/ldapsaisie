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

LSsession :: loadLSclass('LSformElement_select_box');

/**
 * Select box form element for LdapSaisie
 *
 * This class define select box form element.
 * It's an extention of LSformElement_select class.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_sambaAcctFlags extends LSformElement_select_box {

 /**
  * Return display data of this element
  *
  * This method return display data of this element
  *
  * @retval array
  */
  public function isMultiple(){
    return true;
  }

}
