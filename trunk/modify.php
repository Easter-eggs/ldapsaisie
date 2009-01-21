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
  
  if (isset($_POST['LSform_objectdn'])) {
    $dn = $_POST['LSform_objectdn'];
  }
  else if (isset($_GET['dn'])) {
    $dn = $_GET['dn'];
  }

  if ((isset($dn)) && (isset($LSobject)) ) {
    // Création d'un LSobject
    if ($GLOBALS['LSsession'] -> loadLSobject($LSobject)) {
      if ( $GLOBALS['LSsession'] -> canEdit($LSobject,$dn) ) {
        $object = new $LSobject();
        if ($object -> loadData($dn)) {
          // Définition du Titre de la page
          $GLOBALS['Smarty'] -> assign('pagetitle',_('Modifier').' : '.$object -> getDisplayName());
          $form = $object -> getForm('modify');
          if ($form->validate()) {
            // MàJ des données de l'objet LDAP
            if ($object -> updateData('modify')) {
              if ($GLOBALS['LSerror']->errorsDefined()) {
                $GLOBALS['LSsession'] -> addInfo(_("L'objet a été modifié partiellement."));
              }
              else {
                $GLOBALS['LSsession'] -> addInfo(_("L'objet a bien été modifié."));
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
                else {
                  $GLOBALS['LSsession'] -> displayTemplate();
                }
              }
            }
            else {
              $GLOBALS['LSsession'] -> displayAjaxReturn (
                array(
                  'LSformErrors' => $form -> getErrors()
                )
              );
            }
          }
          else if (isset($_REQUEST['ajax']) && $form -> definedError()) {
            $GLOBALS['LSsession'] -> displayAjaxReturn (
              array(
                'LSformErrors' => $form -> getErrors()
              )
            );
          }
          else {
            $LSview_actions[] = array(
              'label' => _('Voir'),
              'url' =>'view.php?LSobject='.$LSobject.'&amp;dn='.$object -> getDn(),
              'action' => 'view'
            );
          
            if ($GLOBALS['LSsession'] -> canRemove($LSobject,$object -> getDn())) {
              $LSview_actions[] = array(
                'label' => _('Supprimer'),
                'url' => 'remove.php?LSobject='.$LSobject.'&amp;dn='.$object -> getDn(),
                'action' => 'delete'
              );
            }
            
            $GLOBALS['LSsession'] -> addJSscript('LSsmoothbox.js');
            $GLOBALS['LSsession'] -> addCssFile('LSsmoothbox.css');
            $GLOBALS['Smarty'] -> assign('LSview_actions',$LSview_actions);
            $GLOBALS['LSsession'] -> setTemplate('modify.tpl');
            $form -> display();
            $GLOBALS['LSsession'] -> displayTemplate();
          }
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode('LSsession_11');
        }
      }
      else {
        $GLOBALS['LSerror'] -> addErrorCode('LSsession_11');
      }
    }
  }
  else {
    $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
  }

}
else {
  $GLOBALS['LSsession'] -> setTemplate('login.tpl');
  $GLOBALS['LSsession'] -> displayTemplate();
}


?>
