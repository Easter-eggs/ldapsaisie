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

if(LSsession :: startLSsession()) {

  if ((isset($_GET['LSobject'])) && (isset($_GET['dn']))) {
    
    if (LSsession ::loadLSobject($_GET['LSobject'])) {
        if ( LSsession :: canRemove($_GET['LSobject'],$_GET['dn']) ) {
          $object = new $_GET['LSobject']();
          if ($object -> loadData($_GET['dn'])) {
            if (isset($_GET['valid'])) {
              $objectname=$object -> getDisplayName();
              $GLOBALS['Smarty'] -> assign('pagetitle',_('Suppression').' : '.$objectname);
              if ($object -> remove()) {
                LSsession :: addInfo($objectname.' '._('a bien été supprimé').'.');
                LSsession :: redirect('view.php?LSobject='.$_GET['LSobject'].'&refresh');
              }
              else {
                LSerror :: addErrorCode('LSldapObject_15',$objectname);
              }
            }
            else {
              // Définition du Titre de la page
              $GLOBALS['Smarty'] -> assign('pagetitle',_('Suppresion').' : '.$object -> getDisplayName());
              $GLOBALS['Smarty'] -> assign('question',_('Voulez-vous vraiment supprimer').' <strong>'.$object -> getDisplayName().'</strong> ?');
              $GLOBALS['Smarty'] -> assign('validation_url','remove.php?LSobject='.$_GET['LSobject'].'&amp;dn='.$_GET['dn'].'&amp;valid');
              $GLOBALS['Smarty'] -> assign('validation_txt',_('Valider'));
            }
            LSsession :: setTemplate('question.tpl');
          }
          else {
            LSerror :: addErrorCode('LSsession_12');
          }
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

// Affichage des retours d'erreurs
LSsession :: displayTemplate();
?>
