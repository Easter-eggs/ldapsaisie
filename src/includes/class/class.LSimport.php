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

LSsession :: loadLSclass('LSlog_staticLoggerClass');
LSsession::loadLSclass('LSioFormat');

/**
 * Manage Import LSldapObject
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSimport extends LSlog_staticLoggerClass {

  /**
   * Check if the form was posted by check POST data
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true if the form was posted, false otherwise
   */
  public static function isSubmit() {
    if (isset($_POST['validate']) && ($_POST['validate']=='LSimport'))
      return true;
    return;
  }


  /**
   * Retrieve the post file
   *
   * @retval mixed The path of the temporary file, false on error
   */
  public static function getPostFile() {
    if (is_uploaded_file($_FILES['importfile']['tmp_name'])) {
      $fp = fopen($_FILES['importfile']['tmp_name'], "r");
      $buf = fread($fp, filesize($_FILES['importfile']['tmp_name']));
      fclose($fp);
      $tmp_file = LS_TMP_DIR_PATH.'importfile'.'_'.rand().'.tmp';
      if (move_uploaded_file($_FILES['importfile']['tmp_name'],$tmp_file)) {
        LSsession :: addTmpFile($buf,$tmp_file);
      }
      return $tmp_file;
    }
    return false;
  }

  /**
   * Retreive POST data
   *
   * This method retrieve and format POST data.
   *
   * The POST data are return as an array containing :
   *  - LSobject : The LSobject type if this import
   *  - ioFormat : The IOformat name choose by user
   *  - justTry : Boolean defining wether the user has chosen to enable
   *              just try mode (no modification)
   *  - updateIfExists : Boolean defining wether the user has chosen to
   *                     allow update on existing object.
   *  - importfile : The path of the temporary file to import
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval mixed Array of POST data, false on error
   */
  public static function getPostData() {
    if (isset($_REQUEST['LSobject']) && isset($_POST['ioFormat'])) {
      $file=self::getPostFile();
      if ($file) {
        return array (
          'LSobject' => $_REQUEST['LSobject'],
          'ioFormat' => $_POST['ioFormat'],
          'justTry' => ($_POST['justTry']=='yes'),
          'updateIfExists' => ($_POST['updateIfExists']=='yes'),
          'importfile' => $file
        );
      }
    }
    return False;
  }

  /**
   * Import objects form POST data
   *
   * This method retreive, validate and import POST data.
   *
   * If import could start, the return value is an array :
   *
   *
   *   array (
   *     'imported' => array (
   *       '[object1 dn]' => '[object1 display name]',
   *       '[object2 dn]' => '[object2 display name]',
   *       [...]
   *     ),
   *     'updated' => array (
   *       '[object3 dn]' => '[object3 display name]',
   *       '[object4 dn]' => '[object4 display name]',
   *       [...]
   *     ),
   *     'errors' => array (
   *       array (
   *         'data' =>  array ([object data as read from source file]),
   *         'errors' => array (
   *           'globals' => array (
   *             // Global error of this object importation that not
   *             // concerning only one attribute)
   *           ),
   *           'attrs' => array (
   *             '[attr1]' => array (
   *               '[error 1]',
   *               '[error 2]',
   *               [...]
   *             )
   *           )
   *         )
   *       ),
   *       [...]
   *     )
   *   )
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean Array of the import result, false on error
   */
  public static function importFromPostData() {
    // Get data from $_POST
    $data = self::getPostData();
    $return = array(
      'success' => false,
      'imported' => array(),
      'updated' => array(),
      'errors' => array(),
    );
    if (!is_array($data)) {
      LSerror :: addErrorCode('LSimport_01');
      return $return;
    }
    self :: log_trace("importFromPostData(): POST data=".varDump($data));
    $return = array_merge($return, $data);
    // Load LSobject
    if (!isset($data['LSobject']) || !LSsession::loadLSobject($data['LSobject'])) {
      LSerror :: addErrorCode('LSimport_02');
      return $return;
    }

    $LSobject = $data['LSobject'];

    // Validate ioFormat
    $object = new $LSobject();
    if(!$object -> isValidIOformat($data['ioFormat'])) {
      LSerror :: addErrorCode('LSimport_03',$data['ioFormat']);
      return $return;
    }

    // Create LSioFormat object
    $ioFormat = new LSioFormat($LSobject,$data['ioFormat']);
    if (!$ioFormat -> ready()) {
      LSerror :: addErrorCode('LSimport_04');
      return $return;
    }

    // Load data in LSioFormat object
    if (!$ioFormat -> loadFile($data['importfile'])) {
      LSerror :: addErrorCode('LSimport_05');
      return $return;
    }
    self :: log_debug("importFromPostData(): file loaded");

    // Retreive object from ioFormat
    $objectsData = $ioFormat -> getAll();
    $objectsInError = array();
    self :: log_trace("importFromPostData(): objects data=".varDump($objectsData));

    // Browse inputed objects
    foreach($objectsData as $objData) {
      $globalErrors = array();
      // Instanciate an LSobject
      $object = new $LSobject();
      // Instanciate a creation LSform (in API mode)
      $form = $object -> getForm('create', null, true);
      // Set form data from inputed data
      if (!$form -> setPostData($objData, true)) {
        self :: log_debug('importFromPostData(): Failed to setPostData on: '.print_r($objData,True));
        $globalErrors[] = _('Failed to set post data on creation form.');
      }
      // Validate form
      else if (!$form -> validate(true)) {
        self :: log_debug('importFromPostData(): Failed to validate form on: '.print_r($objData,True));
        self :: log_debug('importFromPostData(): Form errors: '.print_r($form->getErrors(),True));
        $globalErrors[] = _('Error validating creation form.');
      }
      // Validate data (just check mode)
      else if (!$object -> updateData('create', True)) {
        self :: log_debug('importFromPostData(): fail to validate object data: '.varDump($objData));
        $globalErrors[] = _('Failed to validate object data.');
      }
      else {
        self :: log_debug('importFromPostData(): Data is correct, retreive object DN');
        $dn = $object -> getDn();
        if (!$dn) {
          self :: log_debug('importFromPostData(): fail to generate for this object: '.varDump($objData));
          $globalErrors[] = _('Failed to generate DN for this object.');
        }
        else {
          // Check if object already exists
          if (!LSldap :: exists($dn)) {
            // Creation mode
            self :: log_debug('importFromPostData(): New object, perform creation');
            if ($data['justTry'] || $object -> updateData('create')) {
              self :: log_info('Object '.$object -> getDn().' imported');
              $return['imported'][$object -> getDn()] = $object -> getDisplayName();
              continue;
            }
            else {
              self :: log_error('Failed to updateData on : '.print_r($objData,True));
              $globalErrors[]=_('Error creating object on LDAP server.');
            }
          }
          // This object already exist, check 'updateIfExists' mode
          elseif (!$data['updateIfExists']) {
            self :: log_debug('importFromPostData(): Object '.$dn.' already exist');
            $globalErrors[] = getFData(_('An object already exist on LDAP server with DN %{dn}.'),$dn);
          }
          else {
            self :: log_info('Object '.$object -> getDn().' exist, perform update');

            // Restart import in update mode

            // Instanciate a new LSobject and load data from it's DN
            $object = new $LSobject();
            if (!$object -> loadData($dn)) {
              self :: log_debug('importFromPostData(): Failed to load data of '.$dn);
              $globalErrors[] = getFData(_("Failed to load existing object %{dn} from LDAP server. Can't update object."));
            }
            else {
              // Instanciate a modify form (in API mode)
              $form = $object -> getForm('modify', null, true);
              // Set form data from inputed data
              if (!$form -> setPostData($objData,true)) {
                self :: log_debug('importFromPostData(): Failed to setPostData on update form : '.print_r($objData,True));
                $globalErrors[] = _('Failed to set post data on update form.');
              }
              // Validate form
              else if (!$form -> validate(true)) {
                self :: log_debug('importFromPostData(): Failed to validate update form on : '.print_r($objData,True));
                self :: log_debug('importFromPostData(): Form errors : '.print_r($form->getErrors(),True));
                $globalErrors[] = _('Error validating update form.');
              }
              // Update data on LDAP server
              else if ($data['justTry'] || $object -> updateData('modify')) {
                self :: log_info('Object '.$object -> getDn().' updated');
                $return['updated'][$object -> getDn()] = $object -> getDisplayName();
                continue;
              }
              else {
                self :: log_error('Object '.$object -> getDn().': Failed to updateData (modify) on : '.print_r($objData,True));
                $globalErrors[] = _('Error updating object on LDAP server.');
              }
            }
          }
        }
      }
      $objectsInError[] = array(
        'data' => $objData,
        'errors' => array (
          'globals' => $globalErrors,
          'attrs' => $form->getErrors()
        )
      );
    }
    $return['errors'] = $objectsInError;
    $return['success'] = empty($objectsInError);
    return $return;
  }

}


/*
 * LSimport_implodeValues template function
 *
 * This function permit to implode field values during
 * template processing. This function take as parameters
 * (in $params) :
 * - $values : the field's values to implode
 *
 * @param[in] $params The template function parameters
 * @param[in] $template Smarty object
 *
 * @retval void
 **/
function LSimport_implodeValues($params, $template) {
  extract($params);

  if (isset($values) && is_array($values)) {
    echo implode(',',$values);
  }
}
LStemplate :: registerFunction('LSimport_implodeValues','LSimport_implodeValues');


LSerror :: defineError('LSimport_01',
___("LSimport : Post data not found or not completed.")
);
LSerror :: defineError('LSimport_02',
___("LSimport : object type invalid.")
);
LSerror :: defineError('LSimport_03',
___("LSimport : input/output format %{format} invalid.")
);
LSerror :: defineError('LSimport_04',
___("LSimport : Fail to initialize input/output driver")
);
LSerror :: defineError('LSimport_05',
___("LSimport : Fail to load objects's data from input file")
);
