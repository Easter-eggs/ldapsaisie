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
 ***************************************************
 * Données de configuration pour le support SUPANN *
 ***************************************************
 */

// Nom de l'attribut LDAP nom
define('LS_SUPANN_LASTNAME_ATTR','sn');

// Nom de l'attribut LDAP prenom
define('LS_SUPANN_FIRSTNAME_ATTR','givenName');

// Type de LSobject correspondant aux entites SUPANN
define('LS_SUPANN_LSOBJECT_ENTITE_TYPE','LSsupannEntite');

// Format d'affichage du nom courts d'une entites SUPANN
define('LS_SUPANN_LSOBJECT_ENTITE_FORMAT_SHORTNAME','%{ou}');

// DN de l'entite SUPANN correspondant à l'etablissement
define('LS_SUPANN_ETABLISSEMENT_DN','supannCodeEntite=XXX,ou=structures,dc=univ,dc=fr');

// Type de LSobject correspondant aux entites SUPANN
// Exemple : 0753742K
define('LS_SUPANN_ETABLISSEMENT_UAI','0753742K');

// Table de données des roles generiques
$GLOBALS['supannRoleGenerique'] = array (
  "D00" => "MINISTRE",
  "D01" => "DIRECTEUR DU CABINET",
  "D02" => "DIRECTEUR ADJOINT DU CABINET",
  "D10" => "DIRECTEUR  AC",
  "D11" => "DELEGUE AC",
  "D12" => "DELEGUE REGIONAL A LA RECH. ET TECHN.",
  "D21" => "DIRECTEUR DE RECHERCHE",
  "D22" => "DIRECTEUR SCIENTIFIQUE",
  "D23" => "DIRECTEUR DE PROJET",
  "D30" => "Directeur",
  "D32" => "Directeur des études",
  "D34" => "Directeur de la Recherche",
  "D35" => "Doyen",
  "D40" => "SOUS-DIRECTEUR",
  "D60" => "DIRECTEUR DE DEPARTEMENT",
  "D70" => "DOYEN DE L'IGEN",
  "D71" => "DOYEN DE L'IGB",
  "D80" => "DIRECTEUR GENERAL AC",
  "D81" => "DIR GEN AC, SECRETAIRE GENERAL ADJOINT",
  "D90" => "HAUT FONCTIONNAIRE DE DEFENSE",
  "F01" => "CHEF DU CABINET",
  "F02" => "CHEF ADJOINT DU CABINET",
  "F10" => "CHEF DE SERVICE",
  "F11" => "CHEF DE SERVICE ADJOINT AU DIRECTEUR",
  "F12" => "CHEF DU SERVICE DE l'IGAENR",
  "F20" => "CHEF DE MISSION",
  "F21" => "CHEF DE LA MISSION",
  "F22" => "CHEF DE SERVICE ADJOINT AU DIR GEN",
  "F30" => "CHEF DE DIVISION",
  "F40" => "CHEF DE DEPARTEMENT",
  "F42" => "chef des services administratifs",
  "F50" => "CHEF DE CENTRE",
  "F60" => "CHEF DE BUREAU",
  "F61" => "CHEF DE CELLULE",
  "F62" => "CHEF DU CABINET DU SECRETAIRE GENERAL",
  "F70" => "CHEF D'EXPLOITATION",
  "F71" => "CHEF D'EQUIPE",
  "F73" => "CHEF DU SERVICE INTERIEUR",
  "F74" => "CHEF DE SECTION",
  "F75" => "CHEF DE SITE",
  "F76" => "CHEF DE CUISINE",
  "H10" => "CHARGE DU SERVICE",
  "H11" => "CHARGE DE SOUS-DIRECTION",
  "H20" => "CHARGE DE MISSION AUPRES DU MINISTRE",
  "H30" => "CHARGE DE MISSION AUPRES DU DIR CAB",
  "H40" => "CHARGE DE MISSION",
  "H70" => "CHARGE DU SECRETARIAT DU DIRECTEUR",
  "H75" => "CHARGE DU SECRETERIAT DU DRRT",
  "H80" => "CHARGE DE GESTION ADMINIS. ET COMPTABLE",
  "J01" => "ADJOINT AU CHEF DE CABINET",
  "J04" => "ADJOINT AU DIRECTEUR GENERAL AC",
  "J05" => "ADJOINT AU DIRECTEUR",
  "J06" => "ADJOINT AU DELEGUE AC",
  "J07" => "ADJOINT AU DRRT",
  "J10" => "ADJOINT AU CHEF DE SERVICE",
  "J11" => "ADJOINT AU CHARGE DU SERVICE",
  "J12" => "ADJOINT AU SOUS-DIRECTEUR",
  "J13" => "ADJOINT AU CHARGE DE SOUS-DIRECTION",
  "J20" => "ADJOINT AU CHEF DE MISSION",
  "J21" => "ADJOINT AU CHEF DE LA MISSION",
  "J30" => "ADJOINT AU CHEF DE DIVISION",
  "J31" => "ADJOINT AU CHEF DE DEPARTEMENT",
  "J32" => "ADJOINT AU DIRECTEUR DE DEPARTEMENT",
  "J33" => "ADJOINT AU CHEF DE CENTRE",
  "J34" => "ADJOINT AU CHEF DE BUREAU",
  "J35" => "A CHEF AU CHEF DE SECTION",
  "J36" => "ADJOINT AU CHEF DE CELLULE",
  "J40" => "ADJOINT AU RESPONSABLE DE CELLULE",
  "J41" => "ADJOINT AU RESPONSABLE DE L'UNITE",
  "J42" => "ADJOINT AU RESPONSABLE DE POLE",
  "J50" => "ADJOINT AU RESPONSABLE D'ATELIER",
  "J60" => "Directeur adjoint",
  "J61" => "Chef de service adjoint",
  "J62" => "Directeur des études adjoint",
  "J63" => "Directeur de la Recherche adjoint",
  "J63" => "Directeur scientifique adjoint",
  "M01" => "COLLABORATEUR EXTERIEUR",
  "N00" => "CONSEILLER AUPRES DU MINISTRE",
  "N01" => "CONSEILLER",
  "N02" => "CONSEILLER DU CABINET",
  "N03" => "CONSEILLER TECHNIQUE DU CABINET",
  "N10" => "CONSEILLER D'ETABLISSEMENTS",
  "N11" => "CONSEILLER PEDAGOGIQUE",
  "N20" => "CONSEILLER TECHNIQUE",
  "N50" => "COORDONNATEUR",
  "P00" => "PRESIDENT",
  "P01" => "PRESIDENT D'ASSOCIATION",
  "P10" => "VICE-PRESIDENT",
  "P50" => "SECRETAIRE GENERAL",
  "P51" => "Secrétaire Général adjoint",
  "P60" => "SECRETAIRE GENERAL AC",
  "P70" => "Administrateur",
  "P71" => "Administrateur provisoire",
  "R00" => "RESPONSABLE",
  "R01" => "RESPONSABLE DE CELLULE",
  "R02" => "RESPONSABLE DE DEPARTEMENT",
  "R10" => "RESPONSABLE DE MISSION",
  "R20" => "RESPONSABLE DE POLE",
  "R21" => "RESPONSABLE DE SECTEUR",
  "R22" => "RESPONSABLE D'UNITE",
  "R30" => "RESPONSABLE DE SERVICES TECHNIQUES",
  "R31" => "RESPONSABLE EPI",
  "R33" => "RESPONSABLE D'ATELIER",
  "R40" => "Responsable admnistratif",
  "R41" => "Responsable de diplôme",
  "R42" => "Responsable pédagogique",
  "R43" => "Responsable de programme",
  "R80" => "RESPONSABLE UGARH",
  "R81" => "ADJOINT(E) RESP UGARH",
  "S01" => "Encadrant Tuteur",
  "S10" => "Membre titulaire, Membre",
  "S11" => "Membre suppléant",
  "S12" => "Membre consultatif",
  "S13" => "Participant",
  "S14" => "Représentant      / Représentant étudiant",
  "S15" => "Délégué",
  "S16" => "Correspondant",
  "S17" => "Coordonnateur scientifique",
  "S20" => "Partenaire",
  "S21" => "Personnalité extérieure",
  "T01" => "ASSISTANT DE SERVICE SOCIAL",
  "T02" => "INFIRMIER",
  "T12" => "AMINISTRATEUR  DE DONNEES",
  "T13" => "HUISSIER",
  "T14" => "AGENT D'ACCUEIL",
  "T15" => "HOTESSE D'ACCUEIL",
  "T16" => "AGENT TECHNIQUE",
  "T17" => "CHARGE DE COM, REL PUBLIQUE OU PRESSE",
  "T18" => "ALLOCATAIRE D'EMPLOIS",
  "T19" => "ANALYSTE",
  "T22" => "ASSISTANT DE DIRECTION",
  "T24" => "ASSISTANT TECHNIQUE",
  "T25" => "CHARGE D'ETUDES",
  "T26" => "CHEF DE PROJET",
  "T27" => "CHEF DE PROJET INFORMATIQUE",
  "T29" => "CONCEPTEUR REDACTEUR SITE WEB",
  "T30" => "CONDUCTEUR AUTOMOBILE",
  "T31" => "CONSEILLER SCIENTIFIQUE ET  TECHNIQUE",
  "T32" => "CONTROLEUR  DE GESTION",
  "T34" => "CORRESPONDANT INFORMATIQUE",
  "T35" => "DEVELOPPEUR D'APPLICATIONS",
  "T36" => "DOCUMENTALISTE - ARCHIVISTE",
  "T37" => "GESTIONNAIRE",
  "T39" => "GESTIONNAIRE DE PARC INF & TELECOM",
  "T40" => "GESTIONNAIRE GRH",
  "T41" => "GESTIONNAIRE DE RESTAURANT ADMINISTRATIF",
  "T42" => "GESTIONNAIRE FINANCIER",
  "T43" => "INFOGRAPHISTE - MAQUETTISTE (PAO)",
  "T44" => "INFORMATICIEN BUREAUTIQUE",
  "T45" => "INFORMATICIEN D'EXPLOITATION",
  "T46" => "INFORMATICIEN SYSTEMES ET RESEAUX",
  "T47" => "INGENIEUR PEDAGOGIQUE OU EN FORM PROF",
  "T48" => "MECANICIEN",
  "T49" => "OPERATEUR",
  "T50" => "OUVRIER",
  "T51" => "AGENT DE PREMIERE INTERVENTION",
  "T52" => "PERSONNEL DE RESTAURATION",
  "T54" => "REDACTEUR",
  "T55" => "REPROGRAPHISTE",
  "T56" => "RESPONSABLE PRODUCTION ET SYSTEMES",
  "T57" => "SECRETAIRE",
  "T58" => "SECRETAIRE PARTICULIER",
  "T59" => "SECRETAIRE MEDICAL",
  "T60" => "CHARGE DE MARCHES PUBLICS",
  "T61" => "CHARGE DE  GESTION  FINANCIERE",
  "T62" => "CHARGE DE PROGRAMMATION BUDGETAIRE",
  "T63" => "TECHNICIEN CONSEIL HYGIENE ET SECURITE",
  "T64" => "TECHNICIEN EXPLOITATION- MAINTENANCE",
  "T65" => "AGENT COMPTABLE",
  "T66" => "GESTIONNAIRE D'ETABLISSEMENT",
  "T80" => "Maîtrise d'ouvrage des SI",
  "T81" => "Maîtrise' d'œuvre des SI",
  "T82" => "AQSSI",
  "T83" => "RSSI",
  "T84" => "CSSI (Correspondant/Chargé de SSI)",
  "X00" => "MEDIATEUR",
  "X01" => "MEDECIN",
  "X10" => "EXPERT PEDAGOGIQUE",
  "X11" => "EXPERT CREDIT IMPOT RECHERCHE",
  "X30" => "CHAUFFEUR UTILITAIRE",
  "X31" => "CHAUFFEUR DE MINISTRE",
  "X32" => "CHAUFFEUR DU CABINET",
  "X33" => "CHAUFFEUR DE DIRECTION",
  "X40" => "CONTROLEUR FINANCIER",
  "X50" => "JURISTE",
  "X51" => "Correspondant I&L",
  "X60" => "SECRETAIRE DE  CABINET",
  "X70" => "ACMO H&S",
  "X71" => "Chargé de service de prévention H&S",
  "X80" => "Assesseur"
);

$GLOBALS['supannTypeEntite'] = array (
  "S101" => "Conseil d'Administration",
  "S312" => "Centre de recherche"
);

// Table de tranduction des roles dans les entites
$GLOBALS['supannTranslateRoleEntiteValueDirectory'] = array(
  "SUPANN" => array (
    "role" => $GLOBALS['supannRoleGenerique'],
    "type" => $GLOBALS['supannTypeEntite']
  )
);

// Table des fonctions de tranduction des roles dans les entites
$GLOBALS['supannTranslateFunctionDirectory'] = array(
  "no" => array(
    "code" => "supanGetEntiteNameById"
  )
);
?>
