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

require_once 'includes/functions.php';
require_once 'includes/class/class.LSsession.php';

$GLOBALS['LSsession'] = new LSsession();

if($LSsession -> startLSsession()) {

  // D�finition du Titre de la page
  $GLOBALS['Smarty'] -> assign('pagetitle',_('Modifier'));

  // Cr�ation d'un LSobject
  if (class_exists($_GET['LSobject'])) {
    debug('me : '.$GLOBALS['LSsession'] -> whoami($_GET['dn']));
    if ( $GLOBALS['LSsession'] -> whoami($_GET['dn']) != 'user' ) {
      $object = new $_GET['LSobject']();
      if ($object -> loadData($_GET['dn'])) {
        $form = $object -> getForm('test');
        if ($form->validate()) {
          // M�J des donn�es de l'objet LDAP
          $object -> updateData('test');
        }
        $form -> display();
      }
      else debug('erreur durant le chargement du dn');
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(1011);
    }
  }
  else {
    $GLOBALS['LSerror'] -> addErrorCode(21);
  }

  // Template
  $GLOBALS['LSsession'] -> setTemplate('modify.tpl');
}
else {
  $GLOBALS['LSsession'] -> setTemplate('login.tpl');
}

// Affichage des retours d'erreurs
$GLOBALS['LSsession'] -> displayTemplate();
?>
