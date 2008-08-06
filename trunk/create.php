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

  if (isset($_POST['LSform_objecttype'])) {
    $LSobject = $_POST['LSform_objecttype'];
  }
  else if (isset($_GET['LSobject'])) {
    $LSobject = $_GET['LSobject'];
  }
  
  if (isset($LSobject)) {
    // Création d'un LSobject
    if ($GLOBALS['LSsession'] -> loadLSobject($LSobject)) {
      if ( $GLOBALS['LSsession'] -> canCreate($LSobject) ) {
        $object = new $LSobject();
        
        if ($_GET['load']!='') {
          $form = $object -> getForm('create',$_GET['load']);
        }
        else {
          $form = $object -> getForm('create');
        }
        if ($form->validate()) {
          // MàJ des données de l'objet LDAP
          if (($object -> updateData('create'))&&(!$GLOBALS['LSerror']->errorsDefined())) {
            header('Location: view.php?LSobject='.$LSobject.'&dn='.$object -> getDn());
          }
        }
        // Définition du Titre de la page
        $GLOBALS['Smarty'] -> assign('pagetitle',_('Nouveau').' : '.$object -> getLabel());
        $GLOBALS['LSsession'] -> setTemplate('create.tpl');
        $form -> display();
      }
      else {
        $GLOBALS['LSerror'] -> addErrorCode(1011);
      }
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(21);
    }
  }
  else {
    $GLOBALS['LSerror'] -> addErrorCode(1012);
  }

}
else {
  $GLOBALS['LSsession'] -> setTemplate('login.tpl');
}

// Affichage des retours d'erreurs
$GLOBALS['LSsession'] -> displayTemplate();
?>
