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
    'msg' => _("Erreur inconnue!"),
    'level' => 'c'
  ),
  0 => array(
    'msg' => "%{msg}",
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
    'msg' => _("LSldap : Erreur durant la rÃ©cupÃ©ration de l'entrÃ©e Ldap."),
    'level' => 'c'
  ),
  5 => array (
    'msg' => _("LSldap : Erreur durant la mise Ã  jour de l'entrÃ©e Ldap (DN : %{dn})."),
    'level' => 'c'
  ),
  6 => array (
    'msg' => _("LSldap : Erreur durant la suppression des attributs vides."),
    'level' => 'w'
  ),
  7 => array (
    'msg' => _("LSldap : Erreur durant le changement du DN de l'objet."),
    'level' => 'w'
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
    'msg' => _("LSldapObject : Des donnÃ©es de configuration sont manquant pour la validation de l'attribut %{attr} de l'objet %{obj}."),
    'level' => 'c'
  ),
  26 => array (
    'msg' => _("LSldapObject : Erreur de configuration : L'objet %{obj} ne possÃ¨de pas d'attribut %{attr}."),
    'level' => 'c'
  ),
  27 => array (
    'msg' => _("LSldapObject : La fonction %{func} devant Ãªtre executÃ©e avant la modification n'existe pas."),
    'level' => 'c'
  ),
  28 => array (
    'msg' => _("LSldapObject : L'execution de la fonction %{func} devant Ãªtre executÃ©e avant la modification a Ã©chouÃ©e."),
    'level' => 'c'
  ),
  29 => array (
    'msg' => _("LSldapObject : La fonction %{func} devant Ãªtre executÃ©e aprÃ¨s la modification n'existe pas."),
    'level' => 'c'
  ),
  30 => array (
    'msg' => _("LSldapObject : L'execution de la fonction %{func} devant Ãªtre executÃ©e aprÃ¨s la modification a Ã©chouÃ©e."),
    'level' => 'c'
  ),
  31 => array (
    'msg' => _("LSldapObject : Il manque des informations de configuration du type d'objet %{obj} pour la crÃ©ation du nouveau DN."),
    'level' => 'c'
  ),
  32 => array (
    'msg' => _("LSldapObject : L'attribut %{attr} de l'objet n'est pas encore dÃ©finis. Il est impossible de generer un nouveau DN."),
    'level' => 'c'
  ),
  33 => array (
    'msg' => _("LSldapObject : Sans DN, l'objet n'a put Ãªtre modifiÃ©."),
    'level' => 'c'
  ),
  34 => array (
    'msg' => _("LSldapObject : L'attribut %{attr_depend} dÃ©pendant de l'attribut %{attr} n'existe pas."),
    'level' => 'w'
  ),
  35 => array (
    'msg' => _("LSldapObject : Erreur durant la suppression de %{objectname}."),
    'level' => 'c'
  ),
  36 => array (
    'msg' => _("LSldapObject : Erreur durant les actions avant renomage."),
    'level' => 'c'
  ),
  37 => array (
    'msg' => _("LSldapObject : Erreur durant les actions après renomage."),
    'level' => 'c'
  ),
  38 => array (
    'msg' => _("LSldapObject : Erreur durant les actions avant suppression."),
    'level' => 'c'
  ),
  39 => array (
    'msg' => _("LSldapObject : Erreur durant les actions après suppresion."),
    'level' => 'c'
  ),
  301 => array (
    'msg' => _("LSldapObject : Erreur durant les actions après la création. L'objet est pour autant créé."),
    'level' => 'c'
  ),
  302 => array (
    'msg' => _("LSldapObject : La fonction %{fonction} devant être éxecutée après la création de l'objet n'existe pas."),
    'level' => 'c'
  ),
  303 => array (
    'msg' => _("LSldapObject : Erreur durant l'exection de la fonction %{fonction} devant être éxecutée après la création de l'objet."),
    'level' => 'c'
  ),
  304 => array (
    'msg' => _("LSldapObject : La fonction %{fonction} devant être éxecutée après la suppression de l'objet n'existe pas."),
    'level' => 'c'
  ),
  305 => array (
    'msg' => _("LSldapObject : Erreur durant l'exection de la fonction %{fonction} devant être éxecutée après la suppression de l'objet."),
    'level' => 'c'
  ),
  
  // LSattribute
  41 => array (
    'msg' => _("LSattribute : Attribut %{attr} : Type d'attribut (ldap // html) inconnu (ldap = %{ldap} | html = %{html})."),
    'level' => 'c'
  ),
  42 => array (
    'msg' => _("LSattribute : La fonction %{func} pour afficher l'attribut %{attr} est inconnue."),
    'level' => 'c'
  ),
  43 => array (
    'msg' => _("LSattribute : La rÃ¨gle %{rule} pour valider l'attribut %{attr} est inconnue."),
    'level' => 'c'
  ),
  44 => array (
    'msg' => _("LSattribute : Les donnÃ©es de configuration pour vÃ©rifiÃ© l'attribut %{attr} sont incorrects."),
    'level' => 'c'
  ),
  45 => array (
    'msg' => _("LSattribute : La fonction %{func} pour sauver l'attribut %{attr} est inconnue."),
    'level' => 'c'
  ),
  46 => array (
    'msg' => _("LSattribute : La valeur de l'attribut %{attr} ne peut pas Ãªtre gÃ©nÃ©rÃ©e."),
    'level' => 'c'
  ),
  47 => array (
    'msg' => _("LSattribute : La valeur de l'attribut %{attr} n'a pas put Ãªtre gÃ©nÃ©rÃ©e."),
    'level' => 'c'
  ),
  48 => array (
    'msg' => _("LSattribute : La gÃ©nÃ©ration de l'attribut %{attr} n'a pas retournÃ© une valeur correcte."),
    'level' => 'c'
  ),

  // LSattr_html
  101 => array (
    'msg' => _("LSattr_html : La fonction addToForm() du type html de l'attribut %{attr} n'est pas dÃ©finie."),
    'level' => 'c'
  ),
  102 => array (
    'msg' => _("LSattr_html_select_list : Des donnÃ©es de configuration sont manquante pour la gÃ©nÃ©ration de la liste deroulante de l'attribut %{attr}."),
    'level' => 'c'
  ),
  103 => array (
    'msg' => _("LSattr_html_%{type} : Les donnÃ©es multiples ne sont pas gÃ©rÃ©s pour ce type d'attribut."),
    'level' => 'c'
  ),

  // LSform
  201 => array(
    'msg' => _("LSform : Erreur durant la recupÃ©ration des valeurs du formulaire."),
    'level' => 'c'
  ),
  202 => array(
    'msg' => _("LSform : Erreur durant la rÃ©cupÃ©ration de la valeur du formulaire du champ '%{element}'."),
    'level' => 'c'
  ),
  203 => array(
    'msg' => _("LSform : Les donnÃ©es du champ %{element} ne sont pas valides."),
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
    'msg' => _("LSform : Erreur durant la crÃ©ation de l'Ã©lement '%{element}'."),
    'level' => 'c'
  ),
  207 => array(
    'msg' => _("LSform : Aucune valeur de rentrÃ©e pour le champs '%{element}'."),
    'level' => 'c'
  ),

  301 => array(
    'msg' => _("LSformRule : Aucune regex n'a Ã©tÃ© fournis pour la validation des donnÃ©es."),
    'level' => 'w'
  ),
  
  // functions
  901 => array (
    'msg' => _("Functions 'getFData' : La methode %{meth} de l'objet %{obj} n'existe pas."),
    'level' => 'c'
  ),

  // LSsession
  1001 => array (
    'msg' => _("LSsession : La constante %{const} n'est pas dÃ©finie."),
    'level' => 'c'
  ),
  1002 => array (
    'msg' => _("LSsession : Le support %{addon} n'est pas assurÃ©. VÃ©rifier la compatibilitÃ© du systÃ¨me et la configuration de l'addon"),
    'level' => 'c'
  ),
  1003 => array (
    'msg' => _("LSsession : DonnÃ©es de configuration du serveur LDAP invalide. Impossible d'Ã©tablir une connexion."),
    'level' => 'c'
  ),
  1004 => array (
    'msg' => _("LSsession : Impossible de charger l'objets de type %{type} : type inconnu."),
    'level' => 'c'
  ),
  1005 => array (
    'msg' => _("LSsession : Impossible d'effecture l'authentification : Type d'objet d'authentification inconnu (%{type})."),
    'level' => 'c'
  ),
  1006 => array (
    'msg' => _("LSsession : Identifiant ou mot de passe incorrect."),
    'level' => 'c'
  ),
  1007 => array (
    'msg' => _("LSsession : Impossible de vous identifier : Duplication d'authentitÃ©."),
    'level' => 'c'
  ),
  1008 => array (
    'msg' => _("LSsession : Impossible d'inclure le moteur de rendu Smarty."),
    'level' => 'c'
  ),
  1009 => array (
    'msg' => _("LSsession : Impossible de se connecter au Serveur LDAP."),
    'level' => 'c'
  ),
  1010 => array (
    'msg' => _("LSsession : Impossible de charger la classe des objets d'authentification."),
    'level' => 'c'
  ),
  1011 => array (
    'msg' => _("LSsession : Vous n'êtes pas authorisé à  effectuer cette action."),
    'level' => 'c'
  ),
  1012 => array (
    'msg' => _("LSsession : Des informations sont manquantes pour l'affichage de cette page."),
    'level' => 'c'
  ),
  1013 => array (
    'msg' => _("LSrelations : La fonction de listage pour la relation %{relation} est inconnu."),
    'level' => 'c'
  ),
  1014 => array (
    'msg' => _("LSrelations : La fonction de mise Ã  jour pour la relation %{relation} est inconnu."),
    'level' => 'c'
  ),
  1015 => array (
    'msg' => _("LSrelations : Une erreur s'est produite durant la mise a jour de la relation %{relation}."),
    'level' => 'c'
  ),
  1016 => array (
    'msg' => _("LSrelations : L'objet %{LSobject} de la relation %{relation} est inconnu."),
    'level' => 'w'
  ),
  1017 => array (
    'msg' => _("LSsession : Impossible de cr&eacute;er correctement la liste des niveaux. V&eacute;rifier la configuration."),
    'level' => 'c'
  ),
  1018 => array (
    'msg' => _("LSsession : La récupération de mot de passe est désactivée pour ce serveur LDAP."),
    'level' => 'c'
  ),
  1019 => array (
    'msg' => _("LSsession : Des informations sont manquantes pour la récupération de votre mot de passe. Contactez l'administrateur du système."),
    'level' => 'c'
  ),
  1020 => array (
    'msg' => _("LSsession : Erreur durant la récupération de votre mot de passe. Contactez l'administrateur du système."),
    'level' => 'c'
  )
);
?>
