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

require_once 'core.php';
if(LSsession :: initialize()) {
  if (isset($_GET['i'])) {
    $img_path=LStemplate :: getImagePath($_GET['i']);
    if (is_file($img_path)) {
      header('Content-type: '.mime_content_type($img_path));
      header('Cache-Control: public');
      header('Pragma: cache');
      header('Expires: '. gmdate('D, d M Y H:i:s', time() + 60*60*24*30)); // one month
      readfile($img_path); 
      exit(); 
    }
  }
  else {
    die(_('Missing parameter'));
  }
}

