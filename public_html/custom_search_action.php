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

  if ((isset($_GET['LSobject'])) && (isset($_GET['customAction']))) {
    $LSobject=urldecode($_GET['LSobject']);
    $customAction=urldecode($_GET['customAction']);

    if (LSsession :: loadLSclass('LSsearch')) {
        $LSsearch = new LSsearch($LSobject,'LSview');
        $LSsearch -> setParam('extraDisplayedColumns',True);
        $LSsearch -> setParamsFormPostData();

        if ( LSsession :: canExecuteLSsearchCustomAction($LSsearch,$customAction) ) {
          $config = LSconfig :: get('LSobjects.'.$LSobject.'.LSsearch.customActions.'.$customAction);
          if (isset($config['function']) && is_callable($config['function'])) {
            if (isset($config['label'])) {
              $title=__($config['label']);
            }
            else {
              $title=__($customAction);
            }
            if (isset($_GET['valid']) || $config['noConfirmation']) {
              LStemplate :: assign('pagetitle',$title);
              if (call_user_func($config['function'],$LSsearch)) {
                if ($config['disableOnSuccessMsg']!=true) {
                  if ($config['onSuccessMsgFormat']) {
                    LSsession :: addInfo(getFData(__($config['onSuccessMsgFormat']),$objectname));
                  }
                  else {
                    LSsession :: addInfo(getFData(_('The custom action %{title} have been successfully execute on this search.'),$title));
                  }
                }
                if (!isset($config['redirectToObjectList']) || $config['redirectToObjectList']) {
                  LSsession :: redirect('view.php?LSobject='.$LSobject.'&refresh');
                }
              }
              else {
                LSerror :: addErrorCode('LSsearch_16',$customAction);
              }
            }
            else {
              $question=(
	      	isset($config['question_format'])?
	      	getFData(__($config['question_format']),$title):
	      	getFData(_('Do you really want to execute custom action %{title} on this search ?'),$title)
	      );
              LStemplate :: assign('pagetitle',$title);
              LStemplate :: assign('question',$question);
              LStemplate :: assign('validation_url','custom_search_action.php?LSobject='.urlencode($LSobject).'&amp;customAction='.urlencode($customAction).'&amp;valid');
              LStemplate :: assign('validation_label',_('Validate'));
              LSsession :: setTemplate('question.tpl');
            }
          }
          else {
            LSerror :: addErrorCode('LSsession_13');
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_11');
        }
    }
    else {
      LSsession :: addErrorCode('LSsession_05','LSsearch');
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
