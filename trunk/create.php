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
          if ($object -> updateData('create')) {
            if (!LSerror::errorsDefined()) {
              $GLOBALS['LSsession'] -> addInfo(_("L'objet a bien été ajouté."));
            }
            if (isset($_REQUEST['ajax'])) {
              $GLOBALS['LSsession'] -> displayAjaxReturn (
                array(
                  'LSredirect' => 'view.php?LSobject='.$LSobject.'&dn='.$object -> getDn()
                )
              );
              exit();
            }
            else {
              if (!LSdebugDefined()) {
                $GLOBALS['LSsession'] -> redirect('view.php?LSobject='.$LSobject.'&dn='.$object -> getDn());
              }
            }
          }
          else {
            $GLOBALS['LSsession'] -> displayAjaxReturn (
              array(
                'LSformErrors' => $form -> getErrors()
              )
            );
            exit();
          }
        }
        else if (isset($_REQUEST['ajax']) && $form -> definedError()) {
          $GLOBALS['LSsession'] -> displayAjaxReturn (
            array(
              'LSformErrors' => $form -> getErrors()
            )
          );
          exit();
        }
        // Définition du Titre de la page
        $GLOBALS['Smarty'] -> assign('pagetitle',_('Nouveau').' : '.$object -> getLabel());
        $GLOBALS['LSsession'] -> setTemplate('create.tpl');
        $form -> display();
      }
      else {
        LSerror::addErrorCode('LSsession_11');
      }
    }
    else {
      LSerror::addErrorCode('LSldapObject_01');
    }
  }
  else {
    LSerror::addErrorCode('LSsession_12');
  }

}
else {
  $GLOBALS['LSsession'] -> setTemplate('login.tpl');
}
$GLOBALS['LSsession'] -> displayTemplate();

?>
