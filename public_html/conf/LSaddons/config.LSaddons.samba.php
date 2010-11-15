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

/*
 **************************************************
 * Données de configuration pour le support SAMBA *
 **************************************************
 */

// SID du domaine Samba géré
define('LS_SAMBA_DOMAIN_SID','S-1-5-21-2421470416-3566881284-3047381809');

// Nom du domaine Samba géré (sambaDomainName)
define('LS_SAMBA_DOMAIN_NAME','LS');

// Le DN de l'objet sambaDomain du domaine
define('LS_SAMBA_DOMAIN_OBJECT_DN','sambaDomainName=LS,o=ls');

// Nombre de base pour le calcul des sambaSID Utilisateur
define('LS_SAMBA_SID_BASE_USER',1000);

// Nombre de base pour le calcul des sambaSID Groupe
define('LS_SAMBA_SID_BASE_GROUP',1001); 

/**
 * NB : C'est deux nombres doivent être pour l'un paire et pour l'autre impaire
 * pour conserver l'unicité des SID
 **/

// Nom de l'attribut LDAP uidNumber
define('LS_SAMBA_UIDNUMBER_ATTR','uidNumber');

// Nom de l'attribut LDAP gidNumber
define('LS_SAMBA_GIDNUMBER_ATTR','gidNumber');

// Nom de l'attribut LDAP userPassword
define('LS_SAMBA_USERPASSWORD_ATTR','userPassword');

// Format du chemin du home
define('LS_SAMBA_HOME_PATH_FORMAT','\\SERVER\profiles');
?>
