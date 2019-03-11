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

  if ((isset($_GET['LSobject'])) && (isset($_GET['dn']))) {
    $LSobject=urldecode($_GET['LSobject']);
    $dn=urldecode($_GET['dn']);
    
    if (LSsession ::loadLSobject($LSobject)) {
        if ( LSsession :: canRemove($LSobject,$dn) ) {
          $object = new $LSobject();
          if ($object -> loadData($dn)) {
            if (isset($_GET['valid'])) {
              $objectname=$object -> getDisplayName();
              LStemplate :: assign('pagetitle',_('Deleting').' : '.$objectname);
              if ($object -> remove()) {
                LSsession :: addInfo($objectname.' '._('has been deleted successfully').'.');
                LSsession :: redirect('view.php?LSobject='.$LSobject.'&refresh');
              }
              else {
                LSerror :: addErrorCode('LSldapObject_15',$objectname);
              }
            }
            else {
              // Définition du Titre de la page
              LStemplate :: assign('pagetitle',_('Deleting').' : '.$object -> getDisplayName());
              LStemplate :: assign('question',_('Do you really want to delete').' <strong>'.$object -> getDisplayName().'</strong> ?');
              LStemplate :: assign('validation_url','remove.php?LSobject='.$LSobject.'&amp;dn='.urlencode($dn).'&amp;valid');
              LStemplate :: assign('validation_label',_('Validate'));
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

