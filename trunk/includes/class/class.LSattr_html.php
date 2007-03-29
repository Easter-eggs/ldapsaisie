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
 * Base d'un type d'attribut HTML
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_html {
  
  var $name;
  var $config;
  
  function LSattr_html ($name,$config) {
    $this -> name = $name;
    $this -> config = $config;
    return true;
  }
  
  function getLabel() {
    return $this -> config['label'];
  }
  
  function addToForm (&$form,$idForm) {
    $GLOBALS['LSerror'] -> addErrorCode(101,$this -> name);
  }
  
}

?>