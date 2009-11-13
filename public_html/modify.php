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

if(LSsession :: startLSsession()) {

  if (isset($_POST['LSform_objecttype'])) {
    $LSobject = $_POST['LSform_objecttype'];
  }
  else if (isset($_GET['LSobject'])) {
    $LSobject = $_GET['LSobject'];
  }
  
  if (isset($_POST['LSform_objectdn'])) {
    $dn = $_POST['LSform_objectdn'];
  }
  else if (isset($_GET['dn'])) {
    $dn = $_GET['dn'];
  }

  if ((isset($dn)) && (isset($LSobject)) ) {
    // Création d'un LSobject
    if (LSsession :: loadLSobject($LSobject)) {
      if ( LSsession :: canEdit($LSobject,$dn) ) {
        $object = new $LSobject();
        if ($object -> loadData($dn)) {
          // Définition du Titre de la page
          $GLOBALS['Smarty'] -> assign('pagetitle',_('Modify').' : '.$object -> getDisplayName());
          $form = $object -> getForm('modify');
          if ($form->validate()) {
            // MàJ des données de l'objet LDAP
            if ($object -> updateData('modify')) {
              if (LSerror::errorsDefined()) {
                LSsession :: addInfo(_("The object has been partially modified."));
              }
              else {
                LSsession :: addInfo(_("The object has been modified successfully."));
              }
              if (isset($_REQUEST['ajax'])) {
                LSsession :: displayAjaxReturn (
                  array(
                    'LSredirect' => 'view.php?LSobject='.$LSobject.'&dn='.$object -> getDn()
                  )
                );
                exit();
              }
              else {
                if (!LSdebugDefined()) {
                  LSsession :: redirect('view.php?LSobject='.$LSobject.'&dn='.$object -> getDn());
                }
                else {
                  LSsession :: displayTemplate();
                }
              }
            }
            else {
              if (isset($_REQUEST['ajax'])) {
                LSsession :: displayAjaxReturn (
                  array(
                    'LSformErrors' => $form -> getErrors()
                  )
                );
              }
              else {
                LSsession :: displayTemplate();
              }
            }
          }
          else if (isset($_REQUEST['ajax']) && $form -> definedError()) {
            LSsession :: displayAjaxReturn (
              array(
                'LSformErrors' => $form -> getErrors()
              )
            );
          }
          else {
            $LSview_actions[] = array(
              'label' => _('View'),
              'url' =>'view.php?LSobject='.$LSobject.'&amp;dn='.$object -> getDn(),
              'action' => 'view'
            );
          
            if (LSsession :: canRemove($LSobject,$object -> getDn())) {
              $LSview_actions[] = array(
                'label' => _('Delete'),
                'url' => 'remove.php?LSobject='.$LSobject.'&amp;dn='.$object -> getDn(),
                'action' => 'delete'
              );
            }
            
            $GLOBALS['Smarty'] -> assign('LSview_actions',$LSview_actions);
            LSsession :: setTemplate('modify.tpl');
            $form -> display();
            LSsession :: displayTemplate();
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_11');
        }
      }
      else {
        LSerror :: addErrorCode('LSsession_11');
      }
    }
  }
  else {
    LSerror :: addErrorCode('LSsession_12');
  }

}
else {
  LSsession :: setTemplate('login.tpl');
  LSsession :: displayTemplate();
}


?>
