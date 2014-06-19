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
   *                          - 'minValidRegex' : le nombre minimun d'expressions
   *                                              régulières à valider
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
      if (isset($options['params']['minValidRegex'])) {
        $options['params']['minValidRegex']=(int)$options['params']['minValidRegex'];
        if ($options['params']['minValidRegex']==0 || $options['params']['minValidRegex']>count($options['params']['regex'])) {
          $options['params']['minValidRegex']=count($options['params']['regex']);
        }
      }
      else {
        $options['params']['minValidRegex']=count($options['params']['regex']);
      }
      $valid=0;
      foreach($options['params']['regex'] as $regex) {
        if ($regex[0]!='/') {
          LSerror :: addErrorCode('LSformRule_password_01');
          continue;
        }
        if (preg_match($regex,$value))
          $valid++;
      }
      if ($valid<$options['params']['minValidRegex'])
        return;
    }

    if(isset($options['params']['prohibitedValues']) && is_array($options['params']['prohibitedValues'])) {
      if (in_array($value,$options['params']['prohibitedValues']))
        return;
    }
    
    return true;
  }
  
}


/*
 * Error Codes
 */
LSerror :: defineError('LSformRule_password_01',
_("LSformRule_password : Invalid regex configured : %{regex}. You must use PCRE (begining by '/' caracter).")
);
?>
