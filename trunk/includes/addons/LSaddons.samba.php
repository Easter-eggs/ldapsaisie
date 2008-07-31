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
  * Données de configuration pour le support SAMBA
  */

      // SID du domaine Samba géré
      define('LS_SAMBA_DOMAIN_SID','S-1-5-21-2421470416-3566881284-3047381809');

      // Nombre de base pour le calcul des sambaSID Utilisateur
      define('LS_SAMBA_SID_BASE_USER',1000);

      // Nombre de base pour le calcul des sambaSID Groupe
      define('LS_SAMBA_SID_BASE_GROUP',1001); 

     /**
      * NB : C'est deux nombres doivent être pour l'un paire et pour l'autre impaire
      * pour conserver l'unicité des SID
      */
 
      // Nom de l'attribut LDAP uidNumber
      define('LS_SAMBA_UIDNUMBER_ATTR','uidNumber');

      // Nom de l'attribut LDAP gidNumber
      define('LS_SAMBA_GIDNUMBER_ATTR','gidNumber');

      // Nom de l'attribut LDAP userPassword
      define('LS_SAMBA_USERPASSWORD_ATTR','userPassword');

      // Message d'erreur

      $GLOBALS['error_code']['SAMBA_SUPPORT_01']= array (
        'msg' => _("SAMBA Support : la classe smHash ne peut pas être chargée."),
        'level' => 'c'
      );
      $GLOBALS['error_code']['SAMBA_SUPPORT_02']= array (
        'msg' => _("SAMBA Support : La constante %{const} n'est pas définie."),
        'level' => 'c'
      );

      $GLOBALS['error_code']['SAMBA_SUPPORT_03']= array (
        'msg' => _("SAMBA Support : Les constantes LS_SAMBA_SID_BASE_USER et LS_SAMBA_SID_BASE_GROUP ne doivent pas avoir la même parité pour l'unicité des sambaSID."),
        'level' => 'c'
      );


      $GLOBALS['error_code']['SAMBA_01']= array (
        'msg' => _("SAMBA Support : L'attribut %{dependency} est introuvable. Impossible de générer l'attribut %{attr}."),
        'level' => 'c'
      );
      
 /**
  * Fin des données de configuration
  */


 /**
  * Verification du support Samba par ldapSaisie
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  *
  * @retval boolean true si Samba est pleinement supporté, false sinon
  */
  function LSaddon_samba_support() {
    
    $retval=true;

    // Dependance de librairie
    if ( !class_exists('smbHash') ) {
      if ( ! @include_once(LS_LIB_DIR . 'class.smbHash.php') ) {
        $GLOBALS['LSerror'] -> addErrorCode('SAMBA_SUPPORT_O1');
        $retval=false;
      }
    }


    $MUST_DEFINE_CONST= array(
      'LS_SAMBA_DOMAIN_SID',
      'LS_SAMBA_SID_BASE_USER',
      'LS_SAMBA_SID_BASE_GROUP',
      'LS_SAMBA_UIDNUMBER_ATTR',
      'LS_SAMBA_GIDNUMBER_ATTR',
      'LS_SAMBA_USERPASSWORD_ATTR'
    );

    foreach($MUST_DEFINE_CONST as $const) {
      if ( constant($const) == '' ) {
        $GLOBALS['LSerror'] -> addErrorCode('SAMBA_SUPPORT_O2',$const);
        $retval=false;
      }
    }

    // Pour l'intégrité des SID
    if ( (LS_SAMBA_SID_BASE_USER % 2) == (LS_SAMBA_SID_BASE_GROUP % 2) ) {
        $GLOBALS['LSerror'] -> addErrorCode('SAMBA_SUPPORT_O3');
        $retval=false;
    }
    
    return $retval;
  }

 /**
  * Generation de sambaSID
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  * 
  *   Number   = LS_SAMBA_UIDNUMBER_ATTR * 2 + LS_SAMBA_SID_BASE_USER
  *   sambaSID = LS_SAMBA_DOMAIN_SID-Number
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string SambaSID ou false si il y a un problème durant la génération
  */
  function generate_sambaSID($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SAMBA_UIDNUMBER_ATTR ]) != 'LSattribute' ) {
      $GLOBALS['LSerror'] -> addErrorCode('SAMBA_01',array('dependency' => LS_SAMBA_UIDNUMBER_ATTR, 'attr' => 'sambaSID'));
      return;
    }

    $uidnumber_attr_val = $ldapObject -> attrs[ LS_SAMBA_UIDNUMBER_ATTR ] -> getValue();
    $uidnumber_attr_val = $uidnumber_attr_val[0];
    $uidNumber = $uidnumber_attr_val * 2 + LS_SAMBA_SID_BASE_USER;
    $sambaSID = LS_SAMBA_DOMAIN_SID . '-' . $uidNumber;

    return ($sambaSID);
  }

 /**
  * Generation de sambaPrimaryGroupSID
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  * 
  *   Number   = LS_SAMBA_GIDNUMBER_ATTR * 2 + LS_SAMBA_SID_BASE_GROUP
  *   sambaSID = LS_SAMBA_DOMAIN_SID-Number
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string sambaPrimaryGroupSID ou false si il y a un problème durant la génération
  */
  function generate_sambaPrimaryGroupSID($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SAMBA_GIDNUMBER_ATTR ]) != 'LSattribute' ) {
      $GLOBALS['LSerror'] -> addErrorCode('SAMBA_02',array('dependency' => LS_SAMBA_GIDNUMBER_ATTR, 'attr' => 'sambaPrimaryGroupSID'));
      return;
    }
    
    $gidNumber = $ldapObject -> attrs[ LS_SAMBA_GIDNUMBER_ATTR ] -> getValue();
    $gidNumber = $gidNumber[0] * 2 + LS_SAMBA_SID_BASE_GROUP;
    $sambaPrimaryGroupSID = LS_SAMBA_DOMAIN_SID . '-' . $gidNumber;

    return ($sambaPrimaryGroupSID);
  }

 /**
  * Generation de sambaNTPassword
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  * 
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string sambaNTPassword ou false si il y a un problème durant la génération
  */
  function generate_sambaNTPassword($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ]) != 'LSattribute' ) {
      $GLOBALS['LSerror'] -> addErrorCode('SAMBA_03',array('dependency' => LS_SAMBA_USERPASSWORD_ATTR, 'attr' => 'sambaNTPassword'));
      return;
    }

    $password = $ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ] -> ldap -> getClearPassword();
    debug('pwd : '.$password);
    $sambapassword = new smbHash;
    $sambaNTPassword = $sambapassword -> nthash($password);

    if($sambaNTPassword == '') {
      return;
    }
    return $sambaNTPassword;
  }

 /**
  * Generation de sambaLMPassword
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  * 
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string sambaLMPassword ou false si il y a un problème durant la génération
  */
  function generate_sambaLMPassword($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ]) != 'LSattribute' ) {
      $GLOBALS['LSerror'] -> addErrorCode('SAMBA_04',array('dependency' => LS_SAMBA_USERPASSWORD_ATTR, 'attr' => 'sambaLMPassword'));
      return;
    }

    $password = $ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ] -> ldap -> getClearPassword();
    $sambapassword = new smbHash;
    $sambaLMPassword = $sambapassword -> lmhash($password);

    if($sambaLMPassword == '') {
      return;
    }
    return $sambaLMPassword;
  }

?>
