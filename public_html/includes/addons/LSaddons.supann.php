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

// Messages d'erreur

// Support
LSerror :: defineError('SUPANN_SUPPORT_01',
  _("SUPANN Support : The constant %{const} is not defined.")
);

// Autres erreurs
LSerror :: defineError('SUPANN_01',
  _("SUPANN Support : The attribute %{dependency} is missing. Unable to forge the attribute %{attr}.")
);
      
 /**
  * Verification du support SUPANN par ldapSaisie
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si SUPANN est pleinement supporté, false sinon
  */
  function LSaddon_supann_support() {
    $retval = true;
        
    $MUST_DEFINE_CONST= array(
      'LS_SUPANN_LASTNAME_ATTR',
      'LS_SUPANN_FIRSTNAME_ATTR'
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( (!defined($const)) || (constant($const) == "")) {
        LSerror :: addErrorCode('SUPANN_SUPPORT_01',$const);
        $retval=false;
      }
    }
    
    return $retval;
  }

 /**
  * Generation du displayName
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string Le displayName ou false si il y a un problème durant la génération
  */
  function generate_displayName($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_LASTNAME_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_LASTNAME_ATTR, 'attr' => 'cn'));
      return;
    }
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_FIRSTNAME_ATTR, 'attr' => 'cn'));
      return;
    }

    $noms = $ldapObject -> attrs[ LS_SUPANN_LASTNAME_ATTR ] -> getValue();
    $prenoms = $ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ] -> getValue();

    return ($prenoms[0].' '.$noms[0]);
  }
  
 /**
  * Generation du CN
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string Le CN ou false si il y a un problème durant la génération
  */
  function generate_cn($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_LASTNAME_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_LASTNAME_ATTR, 'attr' => 'cn'));
      return;
    }
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_FIRSTNAME_ATTR, 'attr' => 'cn'));
      return;
    }

    $noms = $ldapObject -> attrs[ LS_SUPANN_LASTNAME_ATTR ] -> getValue();
    $prenoms = $ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ] -> getValue();

    return (replaceAccents($noms[0]).' '.replaceAccents($prenoms[0]));
  }
  
  
 /**
  * Supprime les accents d'une chaine
  * 
  * @param[in] $string La chaine originale
  * 
  * @retval string La chaine sans les accents
  */
  function replaceAccents($string){
    $replaceAccent = Array(
      "à" => "a",
      "á" => "a",
      "â" => "a",
      "ã" => "a",
      "ä" => "a",
      "ç" => "c",
      "è" => "e",
      "é" => "e",
      "ê" => "e",
      "ë" => "e",
      "ì" => "i",
      "í" => "i",
      "î" => "i",
      "ï" => "i",
      "ñ" => "n",
      "ò" => "o",
      "ó" => "o",
      "ô" => "o",
      "õ" => "o",
      "ö" => "o",
      "ù" => "u",
      "ú" => "u",
      "û" => "u",
      "ü" => "u",
      "ý" => "y",
      "ÿ" => "y",
      "À" => "A",
      "Á" => "A",
      "Â" => "A",
      "Ã" => "A",
      "Ä" => "A",
      "Ç" => "C",
      "È" => "E",
      "É" => "E",
      "Ê" => "E",
      "Ë" => "E",
      "Ì" => "I",
      "Í" => "I",
      "Î" => "I",
      "Ï" => "I",
      "Ñ" => "N",
      "Ò" => "O",
      "Ó" => "O",
      "Ô" => "O",
      "Õ" => "O",
      "Ö" => "O",
      "Ù" => "U",
      "Ú" => "U",
      "Û" => "U",
      "Ü" => "U",
      "Ý" => "Y"
    );
    return strtr($string, $replaceAccent);
  }
?>
