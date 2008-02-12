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

  if ((isset($_GET['LSobject'])) && (isset($_GET['dn']))) {
    
    if ($GLOBALS['LSsession'] -> loadLSobject($_GET['LSobject'])) {
        if ( $GLOBALS['LSsession'] -> canRemove($_GET['LSobject'],$_GET['dn']) ) {
          $object = new $_GET['LSobject']();
          if ($object -> loadData($_GET['dn'])) {
            if (isset($_GET['valid'])) {
              $objectname=$object -> getDisplayValue();
              $GLOBALS['Smarty'] -> assign('pagetitle',_('Suppression').' : '.$objectname);
              if ($object -> remove()) {
                $GLOBALS['Smarty'] -> assign('question',$objectname.' '._('a bien été supprimé').'.');
              }
              else {
                $GLOBALS['LSerror'] -> addErrorCode(35,$objectname);
              }
            }
            else {
              // Définition du Titre de la page
              $GLOBALS['Smarty'] -> assign('pagetitle',_('Suppresion').' : '.$object -> getDisplayValue());
              $GLOBALS['Smarty'] -> assign('question',_('Voulez-vous vraiment supprimer').' <strong>'.$object -> getDisplayValue().'</strong> ?');
              $GLOBALS['Smarty'] -> assign('validation_url','remove.php?LSobject='.$_GET['LSobject'].'&amp;dn='.$_GET['dn'].'&amp;valid');
              $GLOBALS['Smarty'] -> assign('validation_txt',_('Valider'));
            }
            $GLOBALS['LSsession'] -> setTemplate('question.tpl');
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(1012);
          }
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
