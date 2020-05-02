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

/*
 * Common routing handlers
 */

/*
 * Handle index request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_index($request) {
  // Redirect to default view (if defined)
  LSsession :: redirectToDefaultView();

  // Define page title
  LStemplate :: assign('pagetitle', _('Home'));

  // Template
  LSsession :: setTemplate('accueil.tpl');

  // Display template
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^(index\.php)?$#', 'handle_index', true);
