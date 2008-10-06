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
  * Données de configuration pour le support SUPANN
  */
      // Nom de l'attribut LDAP nom
      define('LS_SUPANN_LASTNAME_ATTR','sn');
      
      // Nom de l'attribut LDAP prenom
      define('LS_SUPANN_FIRSTNAME_ATTR','givenname');
      
      
      // Message d'erreur
      $GLOBALS['LSerror_code']['SUPANN_SUPPORT_01']= array (
        'msg' => _("SUPANN Support : La constante %{const} n'est pas définie."),
        'level' => 'c'
      );
      
      $GLOBALS['LSerror_code']['SUPANN_01']= array (
        'msg' => _("SUPANN Support : L'attribut %{dependency} est introuvable. Impossible de générer l'attribut %{attr}."),
        'level' => 'c'
      );
      
 /**
  * Fin des données de configuration
  */


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
      if ( constant($const) == '' ) {
        $GLOBALS['LSerror'] -> addErrorCode('SUPANN_SUPPORT_01',$const);
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
      $GLOBALS['LSerror'] -> addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_LASTNAME_ATTR, 'attr' => 'cn'));
      return;
    }
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ]) != 'LSattribute' ) {
      $GLOBALS['LSerror'] -> addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_FIRSTNAME_ATTR, 'attr' => 'cn'));
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
      $GLOBALS['LSerror'] -> addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_LASTNAME_ATTR, 'attr' => 'cn'));
      return;
    }
    if ( get_class($ldapObject -> attrs[ LS_SUPANN_FIRSTNAME_ATTR ]) != 'LSattribute' ) {
      $GLOBALS['LSerror'] -> addErrorCode('SUPANN_01',array('dependency' => LS_SUPANN_FIRSTNAME_ATTR, 'attr' => 'cn'));
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
    return strtr($string, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
                          'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
  }
  

?>
