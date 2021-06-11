<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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
 ***************************************************
 * Données de configuration pour le support SUPANN *
 ***************************************************
 */

// Nom de l'attribut LDAP nom
$GLOBALS['LS_SUPANN_LASTNAME_ATTR'] = 'sn';

// Nom de l'attribut LDAP prenom
$GLOBALS['LS_SUPANN_FIRSTNAME_ATTR'] = 'givenName';

// Type de LSobject correspondant aux entites SUPANN
$GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_TYPE'] = 'LSsupannEntite';

// Types de LSobject correspondant aux parrains SUPANN
$GLOBALS['LS_SUPANN_LSOBJECT_PARRAIN_TYPES'] = array('LSsupannPerson', 'LSsupannGroup', 'LSsupannOrg', 'LSsupannEntite');

// Format d'affichage du nom courts d'une entites SUPANN
$GLOBALS['LS_SUPANN_LSOBJECT_ENTITE_FORMAT_SHORTNAME'] = '%{ou}';

// DN de l'entite SUPANN correspondant à l'etablissement
$GLOBALS['LS_SUPANN_ETABLISSEMENT_DN'] = 'supannCodeEntite=XXX,ou=structures,dc=univ,dc=fr';

// Type de LSobject correspondant aux entites SUPANN
// Exemple : 0753742K
$GLOBALS['LS_SUPANN_ETABLISSEMENT_UAI'] = '0753742K';

// LSformat de l'attribut eduPersonPrincipalName
$GLOBALS['LS_SUPANN_EPPN_FORMAT'] = "%{uid}@univ.fr";

// LSformat de l'attribut eduPersonUniqueId (%{uniqueId} étant un ID unique généré aléatoirement)
$GLOBALS['LS_SUPANN_EPUI_FORMAT'] = "%{uniqueId}@univ.fr";

/*
 * Nomenclatures SUPANN
 *
 * Tableau stockant les nomenclautures utilisées.
 *
 * Doc SUPANN :
 *   https://services.renater.fr/documentation/supann/2009/documentcomplet#nomenclatures
 *
 * $GLOBALS['supannNomenclatures'] = array (
 *     '[ETIQUETTE]' => array (
 *         '[table1] => array (
 *             '[key1]' => '[label1],
 *             '[key2]' => '[label2],
 *             [...]
 *         )
 *      ),
 * );
 *
 * [ETIQUETTE] : l'étiquette de la valeur (correspondant le plus souvent ou mainteneur de la nomenclature)
 * [table] : le nom de la table :
 *   - civilite : la civilité des personnes (supannCivilite)
 *   - affiliation : l'affiliation des personnes (eduPersonAffiliation)
 *   - mailPriveLabel: le label des mails privés des personnes (supannMailPrive)
 *   - adressePostalePriveeLabel: le label des adresses privées des personnes (supannAdressePostalePrivee)
 *   - telephonePriveLabel: le label des télépgones privés des personnes (supannTelephonePrive)
 *   - roleGenerique : les rôles génériques (supannRoleGenerique)
 *   - typeEntite : les types d'entités (supannTypeEntite)
 *   - empCorps : les corps d'appartenances des personnels (supannEmpCorps)
 *   - codeEtablissement : les codes d'établissement (supannEtablissement)
 *   - etuRegimeInscription : les régimes d'inscription étudiant (supannEtuRegimeInscription)
 *   - etuSecteurDisciplinaire : les secteurs disciplinaires de dîplomes ou d'enseignements (supannEtuSecteurDisciplinaire)
 *   - etuTypeDiplome : les types de diplôme (supannEtuTypeDiplome)
 *   - etuDiplome : les diplômes (supannEtuDiplome)
 *   - etuEtape : les étapes des enseignements (supannEtuEtape)
 *   - etuElementPedagogique : les éléments pédagogiques (supannEtuElementPedagogique)
 *
 */
$GLOBALS['supannNomenclatures'] = array (
  'SUPANN' => array (
    'civilite' => array(
      'Mme' => ___('Mrs.'),
      'M.' => ___('Mr.'),
    ),
    'affiliation' => array (
      'researcher' => 'Chercheur (researcher)',
      'retired' => 'Retraité (retired)',
      'emeritus' => 'Professeur émérite (emeritus)',
      'teacher' => 'Professeur (teacher)',
      'registered-reader' => 'Lecteur enregistré dans une bibliothèque (registered-reader)',
    ),
    'mailPriveLabel' => array (
      'SECOURS' => ___('Backup'),
      'PERSO' => ___('Personal'),
      'PARENTS' => ___('Parents'),
      'PRO' => ___('Professional'),
    ),
    'adressePostalePriveeLabel' => array (
      'TEMP' => ___('Temporary'),
      'PERSO' => ___('Personal'),
      'PARENTS' => ___('Parents'),
      'PRO' => ___('Professional'),
    ),
    'telephonePriveLabel' => array (
      'MOBPERSO' => ___('Personal mobile'),
      'FIXEPERSO' => ___('Personal landline'),
      'FIXEPARENTS' => ___('Parents landline'),
      'MOBPARENTS' => ___('Parents mobile'),
      'MOBPRO' => ___('Professional mobile'),
      'FIXEPRO' => ___('Professional landline'),
      'SECOURS' => ___('Backup'),
    ),
    'ressource' => array (
      'COMPTE' => ___('User account'),
      'MAIL' => ___('Mailbox'),
    ),
    'ressourceEtat' => array (
      'A' => ___('Active'),
      'I' => ___('Inactive'),
      'S' => ___('Suspended'),
    ),
    'ressourceSousEtat' => array (
      'SupannPrecree' => ___('Account created in advance, but not yet operational'),
      'SupannCree' => ___('Account operational, but of which the user has not yet taken possession'),
      'SupannAnticipe' => ___('Account operational and accessed by the user, in anticipation of their start date of activity'),
      'SupannActif' => ___('Account operational and accessed by the user in regular activity'),
      'SupannSursis' => ___('Account operational and accessed by the user, suspended after the date of end of activity'),
      'SupannExpire' => ___('Account which is no longer operational, the date of end of activity and possible suspension having passed, but whose cancellation deadlines have not yet been reached'),
      'SupannInactif' => ___('Non-operational account (without specifying the reason) whose deletion deadlines have not yet been reached'),
      'SupannSupprDonnees' => ___('Expired account that has reached the data deletion deadline'),
      'SupannSupprCompte' => ___('Expired account pending permanent deletion'),
      'SupannVerrouille' => ___('Account locked (without specifying the reason)'),
      'SupannVerrouAdministratif' => ___('Account locked for administrative reasons (account suspension, charter abuse, etc.)'),
      'SupannVerrouTechnique' => ___("Account locked for a technical reason (detection of a namesake, suspicion of a hacked account, etc.)"),
    ),
  ),
  'eduPerson' => array(
    'affiliation' => array (
      'student' => "Étudiant (student)",
      'faculty' => "Membre du corps professoral (faculty)",
      'staff' => "Personne exerçant une activité administrative, technique ou de support, autre que l'enseignement et la recherche (staff)",
      'employee' => "Personne employée par l'établissement (employee)",
      'member' => "Membre de l'établissement (member)",
      'affiliate' => "Partenaire en relation avec l'établissement, sans en être membre (affiliate)",
      'alum' => "Ancien étudiant (alum)",
      'library-walk-in' => "Personne physiquement présente dans une bibliothèque (library-walk-in)",
    ),
  ),
  'oidc' => array(
    'oidc_genre' => array(
      'female' => ___('Female'),
      'male' => ___('Male'),
      'other' => ___('Other'),
    ),
  ),
);
