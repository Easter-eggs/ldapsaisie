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
        if ( LSsession :: loadLSclass('LSimport')) {
          $object = new $LSobject();
          LStemplate :: assign('LSobject',$LSobject);

          $ioFormats=$object->listValidIOformats();
          if (is_array($ioFormats) && !empty($ioFormats)) {
            LStemplate :: assign('ioFormats',$ioFormats);
            if (LSimport::isSubmit()) {
              $result=LSimport::importFromPostData();
              LSdebug($result,1);
              if(is_array($result)) {
                LStemplate :: assign('result',$result);
              }
            }
          }
          else {
            LStemplate :: assign('ioFormats',array());
            LSerror :: addErrorCode('LSsession_16');
          }

          // Define page title
          LStemplate :: assign('pagetitle',_('Import').' : '.$object->getLabel());
          LSsession :: addCssFile('LSform.css');
          LSsession :: addCssFile('LSimport.css');
          LSsession :: setTemplate('import.tpl');
        }
        else {
          LSerror :: addErrorCode('LSsession_05','LSimport');
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
LSsession :: displayTemplate();
