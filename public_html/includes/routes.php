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

/*
 * Handle image request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_image($request) {
  $img_path = LStemplate :: getImagePath($request -> image);
  if (is_file($img_path)) {
   dumpFile($img_path);
  }
  LSurl :: error_404($request);
}
LSurl :: add_handler('#^image/(?P<image>[^/]+)$#', 'handle_image', false);

/*
 * Handle LSaddon view request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_addon_view($request) {
  if (LSsession ::loadLSaddon($request -> LSaddon)) {
    if ( LSsession :: canAccessLSaddonView($request -> LSaddon, $request -> view) ) {
      LSsession :: showLSaddonView($request -> LSaddon, $request -> view);
      // Print template
      LSsession :: displayTemplate();
    }
    else {
      LSerror :: addErrorCode('LSsession_11');
    }
  }
}
LSurl :: add_handler('#^addon/(?P<LSaddon>[^/]+)/(?P<view>[^/]+)$#', 'handle_addon_view');

/*
 * Handle LSaddon view request old-URL for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_addon_view($request) {
 if ((isset($_GET['LSaddon'])) && (isset($_GET['view']))) {
   LSerror :: addErrorCode('LSsession_25', urldecode($_GET['LSaddon']));
   LSsession :: redirect('addon/'.$_GET['LSaddon'].'/'.$_GET['view']);
 }
 LSsession :: redirect();
}
LSurl :: add_handler('#^addon_view.php#', 'handle_old_addon_view');
