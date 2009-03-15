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
LSerror :: defineError('SAMBA_SUPPORT_01',
  _("SAMBA Support : Unable to load smbHash class.")
);
LSerror :: defineError('SAMBA_SUPPORT_02',
  _("SAMBA Support : The constant %{const} is not defined.")
);

LSerror :: defineError('SAMBA_SUPPORT_03',
  _("SAMBA Support : The constants LS_SAMBA_SID_BASE_USER and LS_SAMBA_SID_BASE_GROUP must'nt have the same parity to keep SambaSID's unicity.")
);

// Autres erreurs
LSerror :: defineError('SAMBA_01',
  _("SAMBA Support : The attribute%{dependency} is missing. Unable to forge the attribute %{attr}.")
);

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
      if ( !LSsession::includeFile(LS_LIB_DIR . 'class.smbHash.php') ) {
        LSerror :: addErrorCode('SAMBA_SUPPORT_01');
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
      if ( (!defined($const)) || (constant($const) == "")) {
        LSerror :: addErrorCode('SAMBA_SUPPORT_02',$const);
        $retval=false;
      }
    }

    // Pour l'intégrité des SID
    if ( (LS_SAMBA_SID_BASE_USER % 2) == (LS_SAMBA_SID_BASE_GROUP % 2) ) {
        LSerror :: addErrorCode('SAMBA_SUPPORT_03');
        $retval=false;
    }
    
    return $retval;
  }

 /**
  * Generation de sambaSID d'un utilisateur
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
  function generate_sambaUserSID($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SAMBA_UIDNUMBER_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SAMBA_01',array('dependency' => LS_SAMBA_UIDNUMBER_ATTR, 'attr' => 'sambaSID'));
      return;
    }

    $uidnumber_attr_val = $ldapObject -> attrs[ LS_SAMBA_UIDNUMBER_ATTR ] -> getValue();
    $uidnumber_attr_val = $uidnumber_attr_val[0];
    $uidNumber = $uidnumber_attr_val * 2 + LS_SAMBA_SID_BASE_USER;
    $sambaSID = LS_SAMBA_DOMAIN_SID . '-' . $uidNumber;

    return ($sambaSID);
  }
  
 /**
  * Generation de sambaSID d'un groupe
  * 
  * @author Benjamin Renard <brenard@easter-eggs.com>
  * 
  *   Number   = LS_SAMBA_GIDNUMBER_ATTR * 2 + LS_SAMBA_SID_BASE_GROUP
  *   sambaSID = LS_SAMBA_DOMAIN_SID-Number
  *
  * @param[in] $ldapObject L'objet ldap
  *
  * @retval string SambaSID ou false si il y a un problème durant la génération
  */
  function generate_sambaGroupSID($ldapObject) {
    if ( get_class($ldapObject -> attrs[ LS_SAMBA_GIDNUMBER_ATTR ]) != 'LSattribute' ) {
      LSerror :: addErrorCode('SAMBA_01',array('dependency' => LS_SAMBA_GIDNUMBER_ATTR, 'attr' => 'sambaSID'));
      return;
    }

    $gidnumber_attr_val = $ldapObject -> attrs[ LS_SAMBA_GIDNUMBER_ATTR ] -> getValue();
    $gidnumber_attr_val = $gidnumber_attr_val[0];
    $gidNumber = $gidnumber_attr_val * 2 + LS_SAMBA_SID_BASE_GROUP;
    $sambaSID = LS_SAMBA_DOMAIN_SID . '-' . $gidNumber;

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
      LSerror :: addErrorCode('SAMBA_02',array('dependency' => LS_SAMBA_GIDNUMBER_ATTR, 'attr' => 'sambaPrimaryGroupSID'));
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
      LSerror :: addErrorCode('SAMBA_03',array('dependency' => LS_SAMBA_USERPASSWORD_ATTR, 'attr' => 'sambaNTPassword'));
      return;
    }

    $password = $ldapObject -> attrs[ LS_SAMBA_USERPASSWORD_ATTR ] -> ldap -> getClearPassword();
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
      LSerror :: addErrorCode('SAMBA_04',array('dependency' => LS_SAMBA_USERPASSWORD_ATTR, 'attr' => 'sambaLMPassword'));
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
