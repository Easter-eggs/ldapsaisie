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

$GLOBALS['LSerror_code'] = array (
  '-1' => array (
    'msg' => _("Erreur inconnue!")
  ),
  0 => array(
    'msg' => "%{msg}"
  ),
  // LSldap
  1 => array (
    'msg' => _("LSldap : Erreur durant la connexion au serveur LDAP (%{msg}).")
  ),
  2 => array (
    'msg' => _("LSldap : Erreur durant la recherche LDAP (%{msg}).")
  ),
  3 => array (
    'msg' => _("LSldap : Type d'objet inconnu.")
  ),
  4 => array (
    'msg' => _("LSldap : Erreur durant la récupération de l'entrée Ldap.")
  ),
  5 => array (
    'msg' => _("LSldap : Erreur durant la mise à jour de l'entrée Ldap (DN : %{dn}).")
  ),
  6 => array (
    'msg' => _("LSldap : Erreur durant la suppression des attributs vides.")
  ),
  7 => array (
    'msg' => _("LSldap : Erreur durant le changement du DN de l'objet.")
  ),
  
  // LSldapObject
  21 => array (
    'msg' => _("LSldapObject : Type d'objet inconnu.")
  ),
  22 => array (
    'msg' => _("LSldapObject : Formulaire de mise jour inconnu par l'objet %{msg}.")
  ),
  23 => array (
    'msg' => _("LSldapObject : Aucun formulaire n'existe dans l'objet %{msg}.")
  ),
  24 => array (
    'msg' => _("LSldapObject : La fonction %{func} pour valider l'attribut %{attr} de l'objet %{obj} est inconnue.")
  ),
  25 => array (
    'msg' => _("LSldapObject : Des données de configuration sont manquant pour la validation de l'attribut %{attr} de l'objet %{obj}.")
  ),
  26 => array (
    'msg' => _("LSldapObject : Erreur de configuration : L'objet %{obj} ne possède pas d'attribut %{attr}.")
  ),
  27 => array (
    'msg' => _("LSldapObject : La fonction %{func} devant être executée avant la modification n'existe pas.")
  ),
  28 => array (
    'msg' => _("LSldapObject : L'execution de la fonction %{func} devant être executée avant la modification a échouée.")
  ),
  29 => array (
    'msg' => _("LSldapObject : La fonction %{func} devant être executée après la modification n'existe pas.")
  ),
  30 => array (
    'msg' => _("LSldapObject : L'execution de la fonction %{func} devant être executée après la modification a échouée.")
  ),
  31 => array (
    'msg' => _("LSldapObject : Il manque des informations de configuration du type d'objet %{obj} pour la création du nouveau DN.")
  ),
  32 => array (
    'msg' => _("LSldapObject : L'attribut %{attr} de l'objet n'est pas encore définis. Il est impossible de generer un nouveau DN.")
  ),
  33 => array (
    'msg' => _("LSldapObject : Sans DN, l'objet n'a put être modifié.")
  ),
  34 => array (
    'msg' => _("LSldapObject : L'attribut %{attr_depend} dépendant de l'attribut %{attr} n'existe pas.")
  ),
  35 => array (
    'msg' => _("LSldapObject : Erreur durant la suppression de %{objectname}.")
  ),
  36 => array (
    'msg' => _("LSldapObject : Erreur durant les actions avant renomage.")
  ),
  37 => array (
    'msg' => _("LSldapObject : Erreur durant les actions aprês renomage.")
  ),
  38 => array (
    'msg' => _("LSldapObject : Erreur durant les actions avant suppression.")
  ),
  39 => array (
    'msg' => _("LSldapObject : Erreur durant les actions aprês suppresion.")
  ),
  301 => array (
    'msg' => _("LSldapObject : Erreur durant les actions aprês la création. L'objet est pour autant créé.")
  ),
  302 => array (
    'msg' => _("LSldapObject : La fonction %{fonction} devant être éxecutée aprês la création de l'objet n'existe pas.")
  ),
  303 => array (
    'msg' => _("LSldapObject : Erreur durant l'exection de la fonction %{fonction} devant être éxecutée aprês la création de l'objet.")
  ),
  304 => array (
    'msg' => _("LSldapObject : La fonction %{fonction} devant être éxecutée aprês la suppression de l'objet n'existe pas.")
  ),
  305 => array (
    'msg' => _("LSldapObject : Erreur durant l'exection de la fonction %{fonction} devant être éxecutée aprês la suppression de l'objet.")
  ),
  306 => array (
    'msg' => _("LSldapObject : La fonction %{func} devant être executée après la modification de l'attribut %{attr} n'existe pas.")
  ),
  307 => array (
    'msg' => _("LSldapObject : L'execution de la fonction %{func} devant être executée après la modification de l'attribut %{attr} a échouée.")
  ),
  308 => array (
    'msg' => _("LSldapObject : La fonction %{func} devant être executée avant la modification de l'attribut %{attr} n'existe pas.")
  ),
  309 => array (
    'msg' => _("LSldapObject : L'execution de la fonction %{func} devant être executée avant la modification de l'attribut %{attr} a échouée.")
  ),
  
  // LSattribute
  41 => array (
    'msg' => _("LSattribute : Attribut %{attr} : Type d'attribut (ldap // html) inconnu (ldap = %{ldap} | html = %{html}).")
  ),
  42 => array (
    'msg' => _("LSattribute : La fonction %{func} pour afficher l'attribut %{attr} est inconnue.")
  ),
  43 => array (
    'msg' => _("LSattribute : La règle %{rule} pour valider l'attribut %{attr} est inconnue.")
  ),
  44 => array (
    'msg' => _("LSattribute : Les données de configuration pour vérifié l'attribut %{attr} sont incorrects.")
  ),
  45 => array (
    'msg' => _("LSattribute : La fonction %{func} pour sauver l'attribut %{attr} est inconnue.")
  ),
  46 => array (
    'msg' => _("LSattribute : La valeur de l'attribut %{attr} ne peut pas être générée.")
  ),
  47 => array (
    'msg' => _("LSattribute : La valeur de l'attribut %{attr} n'a pas put être générée.")
  ),
  48 => array (
    'msg' => _("LSattribute : La génération de l'attribut %{attr} n'a pas retourné une valeur correcte.")
  ),

  // LSattr_html
  101 => array (
    'msg' => _("LSattr_html : La fonction addToForm() du type html de l'attribut %{attr} n'est pas définie.")
  ),
  102 => array (
    'msg' => _("LSattr_html_select_list : Des données de configuration sont manquante pour la génération de la liste deroulante de l'attribut %{attr}.")
  ),
  103 => array (
    'msg' => _("LSattr_html_%{type} : Les données multiples ne sont pas gérés pour ce type d'attribut.")
  ),

  // LSform
  201 => array(
    'msg' => _("LSform : Erreur durant la recupération des valeurs du formulaire.")
  ),
  202 => array(
    'msg' => _("LSform : Erreur durant la récupération de la valeur du formulaire du champ '%{element}'.")
  ),
  203 => array(
    'msg' => _("LSform : Les données du champ %{element} ne sont pas valides.")
  ),
  204 => array(
    'msg' => _("LSform : Le champ %{element} n'existe pas.")
  ),
  205 => array(
    'msg' => _("LSfom : Type de champ inconnu (%{type}).")
  ),
  206 => array(
    'msg' => _("LSform : Erreur durant la création de l'élement '%{element}'.")
  ),
  207 => array(
    'msg' => _("LSform : Aucune valeur de rentrée pour le champs '%{element}'.")
  ),

  801 => array(
    'msg' => _("LSformRule : Aucune regex n'a été fournis pour la validation des données.")
  ),
  
  // functions
  901 => array (
    'msg' => _("Functions 'getFData' : La methode %{meth} de l'objet %{obj} n'existe pas.")
  ),

  // LSsession
  1001 => array (
    'msg' => _("LSsession : La constante %{const} n'est pas définie.")
  ),
  1002 => array (
    'msg' => _("LSsession : Le support %{addon} n'est pas assuré. Vérifier la compatibilité du système et la configuration de l'addon")
  ),
  1003 => array (
    'msg' => _("LSsession : Données de configuration du serveur LDAP invalide. Impossible d'établir une connexion.")
  ),
  1004 => array (
    'msg' => _("LSsession : Impossible de charger l'objets de type %{type} : type inconnu.")
  ),
  1005 => array (
    'msg' => _("LSsession : Impossible d'effecture l'authentification : Type d'objet d'authentification inconnu (%{type}).")
  ),
  1006 => array (
    'msg' => _("LSsession : Identifiant ou mot de passe incorrect.")
  ),
  1007 => array (
    'msg' => _("LSsession : Impossible de vous identifier : Duplication d'authentité.")
  ),
  1008 => array (
    'msg' => _("LSsession : Impossible d'inclure le moteur de rendu Smarty.")
  ),
  1009 => array (
    'msg' => _("LSsession : Impossible de se connecter au Serveur LDAP.")
  ),
  1010 => array (
    'msg' => _("LSsession : Impossible de charger la classe des objets d'authentification.")
  ),
  1011 => array (
    'msg' => _("LSsession : Vous n'êtes pas authorisé à effectuer cette action.")
  ),
  1012 => array (
    'msg' => _("LSsession : Des informations sont manquantes pour l'affichage de cette page.")
  ),
  1013 => array (
    'msg' => _("LSrelations : La fonction de listage pour la relation %{relation} est inconnu.")
  ),
  1014 => array (
    'msg' => _("LSrelations : La fonction de mise à jour pour la relation %{relation} est inconnu.")
  ),
  1015 => array (
    'msg' => _("LSrelations : Une erreur s'est produite durant la mise a jour de la relation %{relation}.")
  ),
  1016 => array (
    'msg' => _("LSrelations : L'objet %{LSobject} de la relation %{relation} est inconnu.")
  ),
  1017 => array (
    'msg' => _("LSsession : Impossible de créer correctement la liste des niveaux. Vérifier la configuration.")
  ),
  1018 => array (
    'msg' => _("LSsession : La récupération de mot de passe est désactivée pour ce serveur LDAP.")
  ),
  1019 => array (
    'msg' => _("LSsession : Des informations sont manquantes pour la récupération de votre mot de passe. Contactez l'administrateur du systême.")
  ),
  1020 => array (
    'msg' => _("LSsession : Erreur durant la récupération de votre mot de passe. Contactez l'administrateur du systême.")
  ),
  1021 => array (
    'msg' => _("LSrelation : Des paramètres sont manquants dans l'invocation des méthodes de manipulations de relations standarts (Méthode : %{meth}).")
  ),
  1022 => array(
    'msg' => _("LSsession : problème durant l'initialisation.")
  )
);
?>
