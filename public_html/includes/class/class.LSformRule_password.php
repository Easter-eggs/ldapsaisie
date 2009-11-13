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
 * Règle de validation d'un mot de passe
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_password extends LSformRule {
 
  /**
   * Vérification de la valeur.
   *
   * @param string $values Valeur à vérifier
   * @param array $options Options de validation
   *                          - 'minLength' : la longueur maximale
   *                          - 'maxLength' : la longueur minimale
   *                          - 'prohibitedValues' : Un tableau de valeurs interdites
   *                          - 'regex' : une ou plusieurs expressions régulières
   *                                      devant matche
   * @param object $formElement L'objet formElement attaché
   *
   * @return boolean true si la valeur est valide, false sinon
   */ 
  function validate ($value,$options=array(),$formElement) {
    if(isset($options['params']['maxLength'])) {
      if (strlen($value)>$options['params']['maxLength'])
        return;
    }
    
    if(isset($options['params']['minLength'])) {
      if (strlen($value)<$options['params']['minLength'])
        return;
    }
    
    if(isset($options['params']['regex'])) {
      if (!is_array($options['params']['regex'])) {
        $options['params']['regex']=array($options['params']['regex']);
      }
      foreach($options['params']['regex'] as $regex) {
        if (!ereg($regex,$value)) 
          return;
      }
    }
    
    if(is_array($options['params']['prohibitedValues'])) {
      if (in_array($value,$options['params']['prohibitedValues'])) 
        return;
    }
    
    return true;
  }
  
}

?>
