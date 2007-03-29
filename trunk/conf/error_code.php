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

$GLOBALS['error_code'] = array (
  '-1' => array (
    'msg' => 'Erreur inconnue!',
    'level' => 'c'
  ),
  // LSldap
  1 => array (
    'msg' => 'LSldap : Erreur durant la connexion au serveur LDAP (%{msg}).',
    'level' => 'c'
  ),
  2 => array (
    'msg' => 'LSldap : Erreur durant la recherche LDAP (%{msg}).',
    'level' => 'c'
  ),
  
  // LSldapObject
  21 => array (
    'msg' => "LSldapObject : Type d'objet inconnu.",
    'level' => 'c'
  ),
  22 => array (
    'msg' => "LSldapObject : Formulaire de mise jour inconnu par l'objet %{msg}.",
    'level' => 'c'
  ),
  23 => array (
    'msg' => "LSldapObject : Aucun formulaire n'existe dans l'objet %{msg}.",
    'level' => 'c'
  ),
  24 => array (
    'msg' => "LSldapObject : La fonction %{func} pour valider l'attribut %{attr} de l'objet %{obj} est inconnue.",
    'level' => 'c'
  ),
  25 => array (
    'msg' => "LSldapObject : Des données de configuration sont manquant pour la validation de l'attribut %{attr} de l'objet %{obj}.",
    'level' => 'c'
  ),
  26 => array (
    'msg' => "LSldapObject : Erreur de configuration : L'objet %{obj} ne possède pas d'attribut %{attr}.",
    'level' => 'c'
  ),
  27 => array (
    'msg' => "LSldapObject : La fonction %{func} devant être executée avant l'enregistrement n'existe pas.",
    'level' => 'c'
  ),
  28 => array (
    'msg' => "LSldapObject : L'execution de la fonction %{func} devant être executée avant l'enregistrement a échouée.",
    'level' => 'c'
  ),
  29 => array (
    'msg' => "LSldapObject : La fonction %{func} devant être executée après l'enregistrement n'existe pas.",
    'level' => 'c'
  ),
  30 => array (
    'msg' => "LSldapObject : L'execution de la fonction %{func} devant être executée après l'enregistrement a échouée.",
    'level' => 'c'
  ),
  
  // LSldapObject
  41 => array (
    'msg' => "LSattribute : Type d'attribut (ldap // html) inconnu (ldap = %{ldap} | html = %{html}).",
    'level' => 'c'
  ),
  42 => array (
    'msg' => "LSattribute : La fonction %{func} pour afficher l'attribut %{attr} est inconnue.",
    'level' => 'c'
  ),
  43 => array (
    'msg' => "LSattribute : La règle %{rule} pour valider l'attribut %{attr} est inconnue.",
    'level' => 'c'
  ),
  44 => array (
    'msg' => "LSattribute : Les données de configuration pour vérifié l'attribut %{attr} sont incorrects.",
    'level' => 'c'
  ),
  45 => array (
    'msg' => "LSattribute : La fonction %{func} pour sauver l'attribut %{attr} est inconnue.",
    'level' => 'c'
  ),
  
  // LSattr_html
  101 => array (
    'msg' => "LSattr_html : La fonction addToForm() du type html de l'attribut %{attr} n'est pas définie.",
    'level' => 'c'
  ),
  102 => array (
    'msg' => "LSattr_html_select_list : Des données de configuration sont manquante pour la génération de la liste deroulante de l'attribut %{attr}.",
    'level' => 'c'
  ),
  
  // functions
  901 => array (
    'msg' => "Functions 'getFData' : La methode %{meth} de l'objet %{obj} n'existe pas.",
    'level' => 'c'
  ),
);
?>