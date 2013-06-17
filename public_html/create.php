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
  
  if (isset($LSobject)) {
    // LSObject creation
    if (LSsession ::loadLSobject($LSobject)) {
      if ( LSsession :: canCreate($LSobject) ) {
        $object = new $LSobject();
        
        if (isset($_GET['load']) && $_GET['load']!='') {
          $form = $object -> getForm('create',urldecode($_GET['load']));
        }
        else {
          $form = $object -> getForm('create');
        }

        if (isset($_REQUEST['LSform_dataEntryForm'])) {
          $form -> applyDataEntryForm((string)$_REQUEST['LSform_dataEntryForm']);
          LStemplate :: assign('LSform_dataEntryForm',(string)$_REQUEST['LSform_dataEntryForm']);
        }

        LStemplate :: assign('listAvailableDataEntryForm',LSform :: listAvailableDataEntryForm($LSobject));
        LStemplate :: assign('DataEntryFormLabel',_('Data entry form'));

        if ($form->validate()) {
          // Data update for LDAP object
          if ($object -> updateData('create')) {
            if (!LSerror::errorsDefined()) {
              LSsession :: addInfo(_("Object has been added."));
            }
            if (isset($_REQUEST['ajax'])) {
              LSsession :: displayAjaxReturn (
                array(
                  'LSredirect' => 'view.php?LSobject='.$LSobject.'&dn='.urlencode($object -> getDn())
                )
              );
              exit();
            }
            else {
              if (!LSdebugDefined()) {
                LSsession :: redirect('view.php?LSobject='.$LSobject.'&dn='.urlencode($object -> getDn()));
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
              exit();
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
          exit();
        }
        // Define page title
        LStemplate :: assign('pagetitle',_('New').' : '.$object -> getLabel());
        LSsession :: setTemplate('create.tpl');
        $form -> display();
      }
      else {
        LSerror :: addErrorCode('LSsession_11');
      }
    }
    else {
      LSerror :: addErrorCode('LSldapObject_01');
    }
  }
  else {
    LSerror :: addErrorCode('LSsession_12');
  }

}
else {
  LSsession :: setTemplate('login.tpl');
}
LSsession :: displayTemplate();

?>
