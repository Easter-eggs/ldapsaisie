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
 * Element mail d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments textes des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_mail extends LSformElement_text {

  var $JSscripts = array(
    'LSmail.js',
    'LSsmoothbox.js',
    'LSconfirmBox.js',
    'LSformElement_mail.js'
  );
  var $CSSfiles = array(
    'LSsmoothbox.css',
    'LSconfirmBox.css'
  );
  
  var $fetchVariables = array(
    'uriClass' => 'LSformElement_mail',
    'uriPrefix' => 'mailto:'
  );
  
  var $fieldTemplate = 'LSformElement_uri_field.tpl';
}

?>
