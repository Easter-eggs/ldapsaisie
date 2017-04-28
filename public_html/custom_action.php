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

  if ((isset($_GET['LSobject'])) && (isset($_GET['dn'])) && (isset($_GET['customAction']))) {
    $LSobject=urldecode($_GET['LSobject']);
    $dn=urldecode($_GET['dn']);
    $customAction=urldecode($_GET['customAction']);

    if (LSsession ::loadLSobject($LSobject)) {
        if ( LSsession :: canExecuteCustomAction($dn,$LSobject,$customAction) ) {
          $object = new $LSobject();
          if ($object -> loadData($dn)) {
            $config = LSconfig :: get('LSobjects.'.$LSobject.'.customActions.'.$customAction);
            if (isset($config['function']) && is_callable($config['function'])) {
              if (isset($config['label'])) {
                $title=__($config['label']);
              }
              else {
                $title=__($customAction);
              }
              if (isset($_GET['valid']) || $config['noConfirmation']) {
                $objectname=$object -> getDisplayName();
                LStemplate :: assign('pagetitle',$title.' : '.$objectname);
                if (call_user_func_array($config['function'],array(&$object))) {
                  if ($config['disableOnSuccessMsg']!=true) {
                    if ($config['onSuccessMsgFormat']) {
                      LSsession :: addInfo(getFData(__($config['onSuccessMsgFormat']),$objectname));
                    }
                    else {
                      LSsession :: addInfo(getFData(_('The custom action %{customAction} have been successfully execute on %{objectname}.'),array('objectname' => $objectname,'customAction' => $customAction)));
                    }
                  }
                  if ($config['redirectToObjectList']) {
                    LSsession :: redirect('view.php?LSobject='.$LSobject.'&refresh');
                  }
                  else if (!isset($config['noRedirect']) || !$config['noRedirect']) {
                    LSsession :: redirect('view.php?LSobject='.$LSobject.'&dn='.urlencode($dn));
                  }
                }
                else {
                  LSerror :: addErrorCode('LSldapObject_31',array('objectname' => $objectname,'customAction' => $customAction));
                }
              }
              else {
                $objectname=$object -> getDisplayName();
                $question=(
			isset($config['question_format'])?
			getFData(__($config['question_format']),$objectname):
			getFData(
				_('Do you really want to execute custom action %{customAction} on %{objectname} ?'),
				array(
					'objectname' => $objectname,
					'customAction' => $customAction
				)
			)
		);
                LStemplate :: assign('pagetitle',$title.' : '.$objectname);
                LStemplate :: assign('question',$question);
                LStemplate :: assign('validation_url','custom_action.php?LSobject='.urlencode($LSobject).'&amp;dn='.urlencode($dn).'&amp;customAction='.urlencode($customAction).'&amp;valid');
                LStemplate :: assign('validation_label',_('Validate'));
                LSsession :: setTemplate('question.tpl');
              }
            }
            else {
              LSerror :: addErrorCode('LSsession_13');
            }
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
