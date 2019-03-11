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

/**
 * Type d'attribut HTML password
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_html_password extends LSattr_html {

  /**
   * Ajoute l'attribut au formualaire passer en paramètre
   *
   * @param[in] &$form LSform Le formulaire
   * @param[in] $idForm L'identifiant du formulaire
   * @param[in] $data Valeur du champs du formulaire
   *
   * @retval LSformElement L'element du formulaire ajouté
   */ 
  function addToForm (&$form,$idForm,$data=NULL) {
    $element=$form -> addElement('password', $this -> name, $this -> getLabel(), $this -> config, $this);
    if(!$element) {
      LSerror :: addErrorCode('LSform_06',$this -> name);
      return;
    }

    if (count($data)>1) {
      LSerror :: addErrorCode('LSattr_html_03','password');
    }
    
    if ($data) {
      if(is_array($data)) {
        $element -> setValue($data[0]);
      }
      else {
        $element -> setValue($data);
      }
    }
    return $element;
  }
  
}

