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
    'msg' => _("Erreur inconnue!"),
    'level' => 'c'
  ),
  // LSldap
  1 => array (
    'msg' => _("LSldap : Erreur durant la connexion au serveur LDAP (%{msg})."),
    'level' => 'c'
  ),
  2 => array (
    'msg' => _("LSldap : Erreur durant la recherche LDAP (%{msg})."),
    'level' => 'c'
  ),
  3 => array (
    'msg' => _("LSldap : Type d'objet inconnu."),
    'level' => 'c'
  ),
  4 => array (
    'msg' => _("LSldap : Erreur durant la r�cup�ration de l'entr�e Ldap."),
    'level' => 'c'
  ),
  5 => array (
    'msg' => _("LSldap : Erreur durant la mise � jour de l'entr�e Ldap (DN : %{dn})."),
    'level' => 'c'
  ),
  
  // LSldapObject
  21 => array (
    'msg' => _("LSldapObject : Type d'objet inconnu."),
    'level' => 'c'
  ),
  22 => array (
    'msg' => _("LSldapObject : Formulaire de mise jour inconnu par l'objet %{msg}."),
    'level' => 'c'
  ),
  23 => array (
    'msg' => _("LSldapObject : Aucun formulaire n'existe dans l'objet %{msg}."),
    'level' => 'c'
  ),
  24 => array (
    'msg' => _("LSldapObject : La fonction %{func} pour valider l'attribut %{attr} de l'objet %{obj} est inconnue."),
    'level' => 'c'
  ),
  25 => array (
    'msg' => _("LSldapObject : Des donn�es de configuration sont manquant pour la validation de l'attribut %{attr} de l'objet %{obj}."),
    'level' => 'c'
  ),
  26 => array (
    'msg' => _("LSldapObject : Erreur de configuration : L'objet %{obj} ne poss�de pas d'attribut %{attr}."),
    'level' => 'c'
  ),
  27 => array (
    'msg' => _("LSldapObject : La fonction %{func} devant �tre execut�e avant l'enregistrement n'existe pas."),
    'level' => 'c'
  ),
  28 => array (
    'msg' => _("LSldapObject : L'execution de la fonction %{func} devant �tre execut�e avant l'enregistrement a �chou�e."),
    'level' => 'c'
  ),
  29 => array (
    'msg' => _("LSldapObject : La fonction %{func} devant �tre execut�e apr�s l'enregistrement n'existe pas."),
    'level' => 'c'
  ),
  30 => array (
    'msg' => _("LSldapObject : L'execution de la fonction %{func} devant �tre execut�e apr�s l'enregistrement a �chou�e."),
    'level' => 'c'
  ),
  31 => array (
    'msg' => _("LSldapObject : Il manque des informations de configuration du type d'objet %{obj} pour la cr�ation du nouveau DN."),
    'level' => 'c'
  ),
  32 => array (
    'msg' => _("LSldapObject : L'attribut %{attr} de l'objet n'est pas encore d�finis. Il est impossible de generer un nouveau DN."),
    'level' => 'c'
  ),
  33 => array (
    'msg' => _("LSldapObject : Sans DN, l'objet n'a put �tre modifi�."),
    'level' => 'c'
  ),
	34 => array (
		'msg' => _("LSldapObject : L'attribut %{attr_depend} d�pendant de l'attribut %{attr} n'existe pas."),
		'level' => 'w'
	),
  
  // LSldapObject
  41 => array (
    'msg' => _("LSattribute : Attribut %{attr} : Type d'attribut (ldap // html) inconnu (ldap = %{ldap} | html = %{html})."),
    'level' => 'c'
  ),
  42 => array (
    'msg' => _("LSattribute : La fonction %{func} pour afficher l'attribut %{attr} est inconnue."),
    'level' => 'c'
  ),
  43 => array (
    'msg' => _("LSattribute : La r�gle %{rule} pour valider l'attribut %{attr} est inconnue."),
    'level' => 'c'
  ),
  44 => array (
    'msg' => _("LSattribute : Les donn�es de configuration pour v�rifi� l'attribut %{attr} sont incorrects."),
    'level' => 'c'
  ),
  45 => array (
    'msg' => _("LSattribute : La fonction %{func} pour sauver l'attribut %{attr} est inconnue."),
    'level' => 'c'
  ),
  46 => array (
    'msg' => _("LSattribute : La valeur de l'attribut %{attr} ne peut pas �tre g�n�r�e."),
    'level' => 'c'
  ),
	47 => array (
    'msg' => _("LSattribute : La valeur de l'attribut %{attr} n'a pas put �tre g�n�r�e."),
    'level' => 'c'
  ),
	48 => array (
    'msg' => _("LSattribute : La g�n�ration de l'attribut %{attr} n'a pas retourn� une valeur correcte."),
    'level' => 'c'
  ),

  // LSattr_html
  101 => array (
    'msg' => _("LSattr_html : La fonction addToForm() du type html de l'attribut %{attr} n'est pas d�finie."),
    'level' => 'c'
  ),
  102 => array (
    'msg' => _("LSattr_html_select_list : Des donn�es de configuration sont manquante pour la g�n�ration de la liste deroulante de l'attribut %{attr}."),
    'level' => 'c'
  ),
  103 => array (
    'msg' => _("LSattr_html_%{type} : Les donn�es multiples ne sont pas g�r�s pour ce type d'attribut."),
    'level' => 'c'
  ),

	// LSform
	201 => array(
		'msg' => _("LSform : Erreur durant la recup�ration des valeurs du formulaire."),
		'level' => 'c'
	),
	202 => array(
		'msg' => _("LSform : Erreur durant la r�cup�ration de la valeur du formulaire du champ '%{element}'."),
		'level' => 'c'
	),
	203 => array(
		'msg' => _("LSform : Les donn�es du champ %{element} ne sont pas valides."),
		'level' => 'c'
	),
	204 => array(
		'msg' => _("LSform : Le champ %{element} n'existe pas."),
		'level' => 'c'
	),
	205 => array(
		'msg' => _("LSfom : Type de champ inconnu (%{type})."),
		'level' => 'c'
	),
	206 => array(
		'msg' => _("LSform : Erreur durant la cr�ation de l'�lement '%{element}'."),
		'level' => 'c'
	),
	207 => array(
		'msg' => _("LSform : Aucune valeur de rentr�e pour le champs '%{element}'."),
		'level' => 'c'
	),

	301 => array(
		'msg' => _("LSformRule : Aucune regex n'a �t� fournis pour la validation des donn�es."),
		'level' => 'w'
	),
  
  // functions
  901 => array (
    'msg' => _("Functions 'getFData' : La methode %{meth} de l'objet %{obj} n'existe pas."),
    'level' => 'c'
  ),
);
?>
