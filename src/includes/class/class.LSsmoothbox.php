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

class LSsmoothbox {

 /*
  * Méthode chargeant les dépendances d'affichage
  *
  * @retval void
  */
  public static function loadDependenciesDisplay() {
    if (LSsession :: loadLSclass('LSconfirmBox')) {
      LSconfirmBox :: loadDependenciesDisplay();
    }
    LSsession :: addJSscript('LSsmoothbox.js');
    LSsession :: addCssFile('LSsmoothbox.css');

    LSsession :: addJSconfigParam('LSsmoothbox_labels', array(
      'close_confirm_text'    => _('Are you sure to want to close this window and lose all changes ?'),
      'validate'              => _('Validate')
    ));
  }

}
