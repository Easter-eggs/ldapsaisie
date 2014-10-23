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
  "C000" => "Bureau du président",
  "C010" => "Groupement d'Intérêt Scientifique (GIS)",
  "C020" => "Aide à la conception de produits pédagogiques",
  "C021" => "Conception de dispositifs de formation ouverts et à distance",
  "C022" => "Coordination d'enseignement transversaux à plusieurs diplômes",
  "C023" => "Création et mise en place d'une nouvelle filière",
  "C030" => "Haut comité éducation économie",
  "C031" => "Organisation",
  "C032" => "Participation au développement et à l'animation de formations délocalisées",
  "C033" => "Participation aux activités de formation continue",
  "C034" => "Programmes pluriannuels de formation (PPF)",
  "C035" => "Organisation",
  "C050" => "Plateforme technologique",
  "E101" => "Conseil d'Administration (CA)",
  "E102" => "Section disciplinaire du CA",
  "E103" => "Conseil Scientifique (CS)",
  "E104" => "Conseil d'UFR",
  "E105" => "Conseil d'école",
  "E106" => "Conseil d'institut",
  "E107" => "Conseil des Etudes et Vie Universitaire (CEVU)",
  "E108" => "Conseil de service général universitaire",
  "E109" => "Conseil de service commun universitaire",
  "E201" => "Commission Paritaire Etablissement",
  "E202" => "Commision consultative paritaire",
  "E203" => "Commission de conseil",
  "E204" => "Commission des Moyens",
  "E205" => "Commission des Statuts ",
  "E206" => "Commission Administrative Paritaire",
  "E301" => "Comité Hygiène et Sécurité",
  "E302" => "Comité de Pilotage SGI",
  "E303" => "Comité Technique Paritaire ",
  "E304" => "Comité consultatif",
  "E305" => "Comité de sélection",
  "E306" => "Comité de Pilotage SGI",
  "E401" => "Jury",
  "E801" => "Conseil CROUS",
  "E901" => "CNESER (Conseil National de l'Ens. Sup. Rech.)",
  "E902" => "CNU Conseil National des Universités",
  "E903" => "CTPM Comité Tech. Par. Min. Ens. Sup. Rech.",
  "S101" => "Grand établissement",
  "S102" => "Université",
  "S103" => "Institut ",
  "S104" => "École ",
  "S105" => "ENS (Ecole Normale Supérieure)",
  "S106" => "PRES   (si celui-ci est de type EPSCP) sinon il faudra une catégorie N1-Autre",
  "S107" => "EFE (Ecole Française de l'Etranger)",
  "S108" => "Établissement public administratif rattaché",
  "S109" => "Observatoire   est-ce un EPSCP? Si oui il faut mettre N1-établissement",
  "S120" => "Pôle ",
  "S200" => "Composante",
  "S201" => "UFR",
  "S202" => "Département",
  "S203" => "Unité de recherche",
  "S204" => "Institut ",
  "S205" => "École",
  "S206" => "Centre polytechnique universitaire",
  "S207" => "OSU (Observatoire des Sciences de l'Univers)",
  "S208" => "IUFM",
  "S220" => "Structure fédérative de recherche",
  "S221" => "Collège des écoles doctorales",
  "S222" => "École doctorale",
  "S230" => "Service central",
  "S231" => "Service général ",
  "S233" => "Scolarité",
  "S234" => "Centre de ressources ",
  "S235" => "Cellule universitaire d'accueil",
  "S236" => "Cellule juridique",
  "S237" => "Cellule controle gestion",
  "S238" => "Centre",
  "S239" => "Service culturel et action sociale",
  "S240" => "Service des Relations Internationales",
  "S241" => "Service inter-universitaire ",
  "S250" => "SCD",
  "S251" => "Section-SCD",
  "S252" => "Formation permanente ",
  "S253" => "SCUIO",
  "S254" => "SAIC",
  "S255" => "Autres comme CEP, UEFAPS, …",
  "S256" => "Service technique",
  "S257" => "Catégorie prévue dans la loi / peut être appelé \"service-commun-rattaché\"",
  "S300" => "Cabinet",
  "S301" => "Coordination",
  "S302" => "Division",
  "S304" => "Mission",
  "S310" => "Laboratoire",
  "S311" => "Département de formation",
  "S312" => "Centre de recherche",
  "S330" => "Campus",
  "S340" => "Plate-forme de recherche ou plateau technique",
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

// Table des code UAI
$GLOBALS['tableCodeUAI'] = array(
  "0133774G" => "Ecole Centrale de Marseille",
  "0840985P" => "Ecole de Gestion et de Commerce d'Avignon",
  "0130230E" => "ECOLE DE L'AIR",
  "0130239P" => "Ecole Supérieure de Commerce de Marseille du groupe EUROMED",
  "0132396J" => "ECOLE SUPERIEURE D'INGENIEURS DE MARSEILLE-CCIMP GROUPE ESIM IMT",
  "0133347T" => "Institut Supérieur de Micro-Electronique Appliquée Ecole Nationale Supérieure des Mines de St Etienne",
  "0130238N" => "Institut supérieur du Bâtiment et des Travaux Publics - CCIMP",
  "0133393T" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE D'AIX MARSEILLE",
  "0131842G" => "UNIVERSITE AIX MARSEILLE 1",
  "0131843H" => "UNIVERSITE AIX MARSEILLE 2",
  "0132364Z" => "UNIVERSITE AIX MARSEILLE 3 PAUL CEZANNE",
  "0840685N" => "UNIVERSITE D AVIGNON ET DES PAYS DE VAUCLUSE",
  "0801911T" => "ECOLE SUPERIEURE D'INGENIEURS EN ELECTROTECHNIQUE ET ELECTRONIQUE D'AMIENS",
  "0800080C" => "GROUPE SUP DE CO ECOLE SUPERIEURE DE COMMERCE",
  "0600071B" => "INSTITUT SUPERIEUR AGRICOLE DE BEAUVAIS",
  "0801885P" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE D'AMIENS",
  "0801344B" => "UNIVERSITE D'AMIENS",
  "0601223D" => "UNIVERSITE DE TECHNOLOGIE DE COMPIEGNE",
  "0701045F" => "ECOLE DE GESTION, DE COMMERCE DE FRANCHE COMTE",
  "0250082D" => "ECOLE NATIONALE SUPERIEURE DE MECANIQUE ET DES MICROTECHNIQUES DE BESANCON",
  "0900362E" => "ECOLE SUPERIEURE DES TECHN. ET DES AFFAIRES",
  "0251762E" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE BESANCON",
  "0251215K" => "UNIVERSITE DE BESANCON",
  "0900424X" => "UNIVERSITE DE TECHNOLOGIE DE BELFORT MONTBELIARD",
  "0332984P" => "CENT ETUD SUP INDUST BLANQUEFO",
  "0332818J" => "ECOLE COMMERCE EUROPEENNE GR INSEEC",
  "0641848L" => "ECOLE DE GESTION ET DE COMMERCE DE BAYONNE",
  "0330203S" => "ECOLE NATIONALE D'INGENIEURS DES TRAVAUX AGRICOLES DE BORDEAUX",
  "0330211A" => "ECOLE SUP DE COMMERCE DE BORDX GRPE BORDX ECOLE MANAGEMENT",
  "0640096G" => "ECOLE SUPERIEURE DE COMMERCE",
  "0641923T" => "ES TECH INDUS AVANCEES BAYONNE",
  "0332524P" => "I.N.S.E.E.C.",
  "0332826T" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE BORDEAUX",
  "0331764N" => "UNIVERSITE BORDEAUX 1 SCIENCES ET TECHNOLOGIES",
  "0331765P" => "UNIVERSITE BORDEAUX 2",
  "0331766R" => "UNIVERSITE BORDEAUX 3",
  "0332929E" => "UNIVERSITE BORDEAUX 4",
  "0640251A" => "UNIVERSITE DE PAU",
  "0501840D" => "ECOLE FORMATION GESTION COMM. EGC BASSE NORMANDIE",
  "0611136D" => "ECOLE INGENIEURS INSTITUT SUP DE PLASTURGIE",
  "0142124H" => "ECOLE MANAGEMENT DE NORMANDIE GROUPE LE HAVRE CAEN",
  "0142182W" => "ECOLE SUPERIEURE D INGENIEURS DES TRAVAUX DE LA CONSTRUCTION DE CAEN",
  "0142158V" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE CAEN",
  "0141408E" => "UNIVERSITE DE CAEN, BASSE NORMANDIE",
  "0631786Z" => "ECOLE NATIONALE D'INGENIEURS DES TRAVAUX AGRICOLES DE CLERMONT FERRAND",
  "0630109B" => "ECOLE SUPERIEURE DE COMMERCE",
  "0631833A" => "INSTITUT FRANÇAIS DE MECANIQUE AVANCEE DE CLERMONT-FERRAND",
  "0631821M" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE CLERMONT FERRAND",
  "0631262E" => "UNIVERSITE DE CLERMONT FERRAND 1",
  "0631525R" => "UNIVERSITE DE CLERMONT FERRAND 2",
  "7200709H" => "ECOLE GESTION ET COMMERCE BORGO",
  "7200164R" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE CORSE",
  "7200664J" => "UNIVERSITE DE CORTE",
  "0941934S" => "Ecole d'ingénieurs des technologies de l'information et du management",
  "0772517T" => "ECOLE NATIONALE DES PONTS ET CHAUSSEES",
  "0772496V" => "ECOLE NATIONALE DES SCIENCES GEOGRAPHIQUES",
  "0940607Z" => "Ecole Normale Superieure de Cachan",
  "0942095S" => "ECOLE POUR INFORMAT.TECHN.AVA. EPITA",
  "0941875C" => "ECOLE SUPERIEURE DES INDUSTRIES DU CAOUTCHOUC",
  "0941954N" => "ECOLE SUPERIEURE DES TRAVAUX DE LA CONSTRUCTION DE CACHAN (EX ECOLE SUPERIEURE DES TRAVAUX PUBLICS DE CACHAN)",
  "0932019P" => "ECOLE SUPERIEURE D'INGENIEURS EN ELECTROTECHNIQUE ET ELECTRONIQUE DE LA CCI DE PARIS",
  "0772219U" => "ESI INFORM GENIE TELECOM AVON ESIGETEL",
  "0932341P" => "INST SUPERIEUR TECHNO MANAGT ISTM",
  "0930603A" => "institut supérieur de mécanique de paris",
  "0941936U" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE CRETEIL",
  "0772502B" => "UNIVERSITE MARNE LA VALLEE",
  "0931238R" => "UNIVERSITE PARIS NORD VILLETANEUSE",
  "0941111X" => "UNIVERSITE PARIS 12 VAL DE MARNE",
  "0931827F" => "UNIVERSITE PARIS 8",
  "0212024L" => "ETABLISSEMENT NATIONAL D'ENSEIGNEMENT SUPERIEUR AGRONOMIQUE DE DIJON",
  "0210099U" => "GROUPE ESC DIJON BOURGOGNE",
  "0211960S" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE DIJON",
  "0211237F" => "UNIVERSITE DE DIJON",
  "0261251U" => "Ecole de commerce gestion administrative et vente",
  "0730899F" => "ECOLE SUPERIEURE DE COMMERCE DE CHAMBERY",
  "0382778N" => "ECOLE SUPERIEURE DE COMMERCE DE GRENOBLE",
  "0381912X" => "INSTITUT POLYTECHNIQUE DE GRENOBLE",
  "0382955F" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE GRENOBLE",
  "0730858L" => "UNIVERSITE DE CHAMBERY",
  "0381838S" => "UNIVERSITE GRENOBLE 1",
  "0381839T" => "UNIVERSITE GRENOBLE 2",
  "0381840U" => "UNIVERSITE GRENOBLE 3",
  "9710939U" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DES ANTILLES GUYANE",
  "9710585J" => "UNIVERSITE DES ANTILLES GUYANE",
  "9730224F" => "ANTENNE D IUFM IUFM ANTILLES GUYANE",
  "0622384E" => "CENTRE D'ETUDES SUPERIEURES INDUSTRIELLES CENTRE REGIONAL DU NORD",
  "0595714R" => "EC SUP METROLOGIE DOUAI ECOLE SUPERIEUR DE METROLOGIE",
  "0590349J" => "ECOLE CENTRALE DE LILLE",
  "0590350K" => "ECOLE DES HAUTES ETUDES COMMERCIALES DU NORD",
  "0623921A" => "ECOLE D'INGENIEURS DU PAS DE CALAIS",
  "0596163D" => "ECOLE GESTION COMMERCE FLANDRE C.C.I. LILLE-METROPOLE",
  "0590311T" => "ECOLE NATIONALE SUPERIEURE DE CHIMIE DE LILLE, RATTACHEE A L'UNIVERSITE DE LILLE 1",
  "0590338X" => "ECOLE NATIONALE SUPERIEURE DES ARTS ET INDUSTRIES TEXTILES DE ROUBAIX",
  "0590342B" => "ECOLE NATIONALE SUPERIEURE DES TECHNIQUES INDUSTRIELLES ET DES MINES DE DOUAI",
  "0590346F" => "ECOLE SUPERIEURE DE COMMERCE DE LILLE",
  "0590353N" => "ECOLE SUPERIEURE DES TECHNIQUES INDUSTRIELLES ET DES TEXTILES",
  "0590348H" => "HAUTES ETUDES INDUSTRIELLES",
  "0590345E" => "INSTITUT CATHOLIQUE D'ARTS ET METIERS",
  "0590344D" => "INSTITUT CATHOLIQUE DE LILLE MEMBRE UNIV CATHOLIQUE DE LILL",
  "0593202K" => "INSTITUT D'ECONOMIE SCIENTIFIQUE ET DE GESTION",
  "0590343C" => "INSTITUT SUPERIEUR D'AGRICULTURE",
  "0590347G" => "INSTITUT SUPERIEUR DE L ELECTRONIQUE ET DU NUMERIQUE LILLE",
  "0595851P" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE LILLE",
  "0623957P" => "UNIVERSITE D'ARTOIS",
  "0593559Y" => "UNIVERSITE DE LILLE 1",
  "0593560Z" => "UNIVERSITE DE LILLE 2",
  "0593561A" => "UNIVERSITE DE LILLE 3",
  "0593279U" => "UNIVERSITE DE VALENCIENNES",
  "0595964M" => "UNIVERSITE DU LITTORAL",
  "0190805X" => "EGC BRIVE ECOLE GESTION ET COMMERCE",
  "0870997L" => "INSTITUT D'INGENIERIE INFORMATIQUE DE LIMOGES",
  "0871012C" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DU LIMOUSIN",
  "0870669E" => "UNIVERSITE DE LIMOGES",
  "0011293A" => "ANTENNE DE PLASTURGIE DE L'INSA DE LYON",
  "0693180G" => "ASSOCIATION LYONNAISE POUR LA FORMATION - INSTITUT POUR LA DIFFUSION DE LA RECHERCHE ACTIVE COMMERCIALE DE LYON",
  "0691696U" => "CENTRE D'ETUDES SUPERIEURES INDUSTRIELLES CENTRE REGIONAL RHONE ALPES AUVERGNE",
  "0690194L" => "ECOLE CATHOLIQUE D'ARTS ET METIERS",
  "0690187D" => "ECOLE CENTRALE DE LYON",
  "0693448Y" => "ECOLE DE COMMERCE EUROPEENNE DE LYON DU GROUPE INSEEC",
  "0690197P" => "ECOLE DE MANAGEMENT DE LYON",
  "0692587M" => "ECOLE NATIONALE DES TRAVAUX MARITIMES",
  "0692566P" => "ECOLE NATIONALE DES TRAVAUX PUBLICS DE L'ETAT",
  "0420093Y" => "ECOLE NATIONALE D'INGENIEURS DE SAINT-ETIENNE",
  "0420094Z" => "ECOLE NATIONALE SUPERIEURE DES MINES DE SAINT ETIENNE",
  "0692459Y" => "ECOLE NATIONALE SUPERIEURE DES SCIENCES DE L'INFORMATION ET DES BIBLIOTHEQUES (E.N.S.S.I.B.)",
  "0693259T" => "ECOLE NORMALE SUPERIEURE DE LYON",
  "0693817Z" => "ECOLE NORMALE SUPERIEURE LETTRES ET SCIENCES HUMAINES",
  "0693623N" => "ECOLE SUPERIEURE DE CHIMIE PHYSIQUE ELECTRONIQUE DE LYON",
  "0421601M" => "GROUPE ECOLE SUPERIEURE DE COMMERCE DE SAINT ETIENNE",
  "0690195M" => "INSTITUT CATHOLIQUE DE LYON",
  "0690192J" => "INSTITUT NATIONAL DES SCIENCES APPLIQUEES DE LYON",
  "0692353H" => "INSTITUT SUPERIEUR D'AGRICULTURE RHONE ALPES",
  "0693364G" => "INSTITUT TEXTILE ET CHIMIQUE DE LYON (ITECH)",
  "0693480H" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE LYON",
  "0421095M" => "UNIVERSITE DE SAINT-ETIENNE",
  "0691774D" => "UNIVERSITE LYON 1",
  "0691775E" => "UNIVERSITE LYON 2",
  "0692437Z" => "UNIVERSITE LYON 3",
  "9720719Z" => "EC.INTER.AFFAI.MANAGEMENT EIAM-EGC",
  "9720706K" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE LA MARTINIQUE",
  "0342222F" => "CIESSA MONTPELLIER SUP AGRONOMIE",
  "0340131H" => "ECOLE NATIONALE SUPERIEURE AGRONOMIQUE DE MONTPELLIER",
  "0300063F" => "ECOLE NATIONALE SUPERIEURE DES TECHNIQUES INDUSTRIELLES ET DES MINES D'ALES",
  "0340137P" => "ECOLE SUPERIEURE DE COMMERCE DE MONTPELLIER DU GROUPE SUP DE CODE MONTPELLIER",
  "0341818S" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE MONTPELLIER",
  "0341087X" => "UNIVERSITE DE MONTPELLIER 1",
  "0341089Z" => "UNIVERSITE DE MONTPELLIER 3",
  "0301687W" => "Université de Nîmes",
  "0660437S" => "UNIVERSITE DE PERPIGNAN VIA DOMITIA",
  "0341088Y" => "UNIVERSITE MONTPELLIER 2",
  "0542260N" => "CENTRE D'ETUDES SUPERIEURES INDUSTRIELLES CENTRE REGIONAL LORRAINE CHAMPAGNE ARDENNES",
  "0570140T" => "ECOLE NATIONALE D'INGENIEURS DE METZ",
  "0880077F" => "ECOLE SUPERIEURE DES INDUSTRIES TEXTILES D'EPINAL",
  "0573389Z" => "ECOLE SUPERIEURE D'INGENIEURS DES TRAVAUX DE LA CONSTRUCTION DE METZ",
  "0573593W" => "ECOLE SUPERIEURE DU SOUDAGE ET DE SES APPLICATIONS",
  "0542455A" => "Institut Commercial de Nancy - Ecole de Management",
  "0541564G" => "INSTITUT NATIONAL POLYTECHNIQUE DE LORRAINE",
  "0542255H" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE NANCY METZ",
  "0572081C" => "UNIVERSITE DE METZ",
  "0541507V" => "UNIVERSITE NANCY 1",
  "0541508W" => "UNIVERSITE NANCY 2",
  "0440112H" => "Audencia école management Nantes",
  "0442292C" => "CENTRE D'ETUDES SUPERIEURES INDUSTRIELLES CENTRE REGIONAL DES PAYS DE LOIRE",
  "0441965X" => "ECOLE ATLANTIQUE DE COMMERCE INTERNATIONAL CHAMBRE DE COMMERCE ET D INDUSTRIE DE NANTES ET DE ST NAZAIRE.",
  "0440100V" => "ECOLE CENTRALE DE NANTES",
  "0721513D" => "Ecole de gestion et de commerce du Mans",
  "0851465F" => "Ecole de gestion et de commerce Vendée",
  "0441679L" => "ECOLE NATIONALE D'INGENIEURS DES TECHNIQUES DES INDUSTRIES AGRICOLES ET ALIMENTAIRES",
  "0442205H" => "ECOLE NATIONALE SUPERIEURE DES TECHNIQUES INDUSTRIELLES ET DES MINES DE NANTES",
  "0492246A" => "ECOLE SUPERIEURE ANGEVINE D'INFORMATIQUE ET DE PRODUCTIQUE",
  "0490072M" => "ECOLE SUPERIEURE D'AGRICULTURE D'ANGERS",
  "0490075R" => "ECOLE SUPERIEURE D'ELECTRONIQUE DE L'OUEST",
  "0721575W" => "ECOLE SUPERIEURE DES GEOMETRES TOPOGRAPHES",
  "0490076S" => "ECOLE SUPERIEURE DES SCIENCES COMMERCIALES D ANGERS DU GROUPE ESSCA",
  "0442278M" => "ECOLE SUPERIEURE DU BOIS",
  "0492202C" => "ECOLE SUPERIEURE ET D APPLICATION DU GENIE",
  "0492189N" => "INH ANGERS",
  "0442185L" => "INSTITUT CATHOLIQUE D'ARTS ET METIERS DE NANTES",
  "0851415B" => "INSTITUT DE FORMATION DE L'UCO AUX METIERS DE L'ENSEIGNEMENT",
  "0492248C" => "INSTITUT NATIONAL D'HORTICULTURE - ECOLE NATIONALE D'INGENIEURS DE L'HORTICULTURE ET DU PAYSAGE",
  "0492247B" => "INSTITUT NATIONAL D'HORTICULTURE - ECOLE NATIONALE SUPERIEURE D'HORTICULTURE ET D'AMENAGEMENT DU PAYSAGE",
  "0721484X" => "INSTITUT SUPERIEUR DES MATERIAUX DU MANS",
  "0442199B" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE NANTES",
  "0490811R" => "UNIVERSITE CATHOLIQUE DE L'OUEST",
  "0490970N" => "UNIVERSITE D'ANGERS",
  "0440984F" => "UNIVERSITE DE NANTES",
  "0720916E" => "UNIVERSITE LE MANS",
  "0060656F" => "ECOLE HTES ETUDES COMMERCIALES C.E.R.A.M.",
  "0831521C" => "INSTITUT SUPERIEUR DE MECANIQUE DE PARIS",
  "0831458J" => "Institut Supérieur d'Electronique et du Numerique - TOULON",
  "0061758D" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE NICE",
  "0060931E" => "UNIVERSITE DE NICE",
  "0830766G" => "UNIVERSITE DE TOULON",
  "9830491S" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DU PACIFIQUE",
  "9830445S" => "UNIVERSITE DE LA NOUVELLE-CALEDONIE",
  "0451493D" => "EC DE COMMERCE ET DE GESTION ECG (CCI) ORLEANS",
  "0371376V" => "EC SUP COMMERCE TOURS GROUPE ESCM TOURS POITIERS",
  "0180910S" => "ECOLE NAT SUP ING DE BOURGES",
  "0410981U" => "ECOLE NATIONALE SUPERIEURE DE LA NATURE ET DU PAYSAGE DE BLOIS",
  "0451482S" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE D'ORLEANS TOURS",
  "0370800U" => "UNIVERSITE DE TOURS",
  "0450855K" => "UNIVERSITE D'ORLEANS",
  "0752092S" => "ACADEMIE COMMERCIALE INTERNATIONALE - NEGOCIA",
  "0754988P" => "ADVANCIA",
  "0753471R" => "CONSERVATOIRE NATIONAL DES ARTS ET METIERS",
  "0753636V" => "EC EUROPEENNE DE GESTION EUROPEAN BUSINESS SCHOOL",
  "0754967S" => "EC SUP ACTION ET RECH COMMERC",
  "0754431J" => "ECOLE CENTRALE D ́ELECTRONIQUE PARIS",
  "0753742K" => "ECOLE DES HAUTES ETUDES EN SCIENCES SOCIALES",
  "0750043P" => "ECOLE D'INGENIEURS DE LA VILLE DE PARIS",
  "0753478Y" => "ECOLE NATIONALE DES CHARTES",
  "0753503A" => "ECOLE NATIONALE DU GENIE RURAL DES EAUX ET DES FORETS",
  "0753237L" => "ECOLE NATIONALE SUPERIEURE D'ARTS ET METIERS",
  "0753375L" => "ECOLE NATIONALE SUPERIEURE DE CHIMIE DE PARIS, RATTACHEE A L'UNIVERSITE PARIS 6",
  "0753493P" => "ECOLE NATIONALE SUPERIEURE DES MINES DE PARIS",
  "0751878J" => "ECOLE NATIONALE SUPERIEURE DES TECHNIQUES AVANCEES",
  "0753510H" => "ECOLE NATIONALE SUPERIEURE DES TELECOMMUNICATIONS",
  "0753455Y" => "ECOLE NORMALE SUPERIEURE",
  "0753486G" => "ECOLE PRATIQUE DES HAUTES ETUDES",
  "0753574C" => "ECOLE SPECIALE DE MECANIQUE ET D'ELECTRICITE",
  "0753607N" => "ECOLE SPECIALE DES TRAVAUX PUBLICS DU BATIMENT ET DE L'INDUSTRIE",
  "0753560M" => "ECOLE SUPERIEURE D INFORMATIQUE ELECTRONIQUE AUTOMATIQUE",
  "0753547Y" => "ECOLE SUPERIEURE DE COMMERCE DE PARIS-ECOLE EUROPEENNE DES AFFAIRES, CHAMBRE DE COMMERCE ET D INDUSTRIE DE PARIS",
  "0753111Z" => "ECOLE SUPERIEURE DE GESTION",
  "0753429V" => "ECOLE SUPERIEURE DE PHYSIQUE ET DE CHIMIE INDUSTRIELLES DE LA VILLE DE PARIS",
  "0754500J" => "INST ETUD ECO ET COMMERCIALES",
  "0753147N" => "INST INTERNAT COMMERCE DISTRIB",
  "0752792C" => "INST PREPA ADM ET GESTION",
  "0752304X" => "INST SUP SC TECHN ET ECO COMM",
  "0753620C" => "INST SUPERIEUR DE GESTION",
  "0753541S" => "INSTITUT CATHOLIQUE PARIS",
  "0753428U" => "INSTITUT DE PHYSIQUE DU GLOBE DE PARIS",
  "0753431X" => "INSTITUT D'ETUDES POLITIQUES DE PARIS",
  "0753465J" => "INSTITUT NATIONAL AGRONOMIQUE PARIS GRIGNON",
  "0755026F" => "INSTITUT NATIONAL D HISTOIRE DE L ART",
  "0753488J" => "INSTITUT NATIONAL DES LANGUES ET CIVILISATIONS ORIENTALES",
  "0750252S" => "INSTITUT SUP DU COMMERCE",
  "0753559L" => "INSTITUT SUPERIEUR D ELECTRONIQUE DE PARIS",
  "0754445Z" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE PARIS",
  "0753494R" => "MUSEUM NATIONAL D'HISTOIRE NATURELLE",
  "0753496T" => "OBSERVATOIRE DE PARIS",
  "0750736T" => "UNIVERSITE DE TECHNOLOGIE EN SCIENCES DES ORGANISATIONS ET DE LA DECISION DE PARIS-DAUPHINE",
  "0751717J" => "UNIVERSITE PARIS 1",
  "0751718K" => "UNIVERSITE PARIS 2",
  "0751719L" => "UNIVERSITE PARIS 3",
  "0751720M" => "UNIVERSITE PARIS 4",
  "0751721N" => "UNIVERSITE PARIS 5",
  "0751722P" => "UNIVERSITE PARIS 6",
  "0751723R" => "UNIVERSITE PARIS 7",
  "0161122H" => "Ecole de gestion et de commerce d'Angoulème",
  "0171435T" => "ECOLE D'INGENIEURS EN GENIE DES SYSTEMES INDUSTRIELS",
  "0860073M" => "ECOLE NATIONALE SUPERIEURE DE MECANIQUE ET D'AEROTECHNIQUE DE POITIERS, RATTACHEE A L'UNIVERSITE DE POITIERS",
  "0171427J" => "ECOLE SUPERIEURE DE COMMERCE",
  "0861249R" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE POITOU-CHARENTES",
  "0171463Y" => "UNIVERSITE DE LA ROCHELLE",
  "0860856N" => "UNIVERSITE DE POITIERS",
  "9840349G" => "UNIVERSITE POLYNESIE FRANCAISE",
  "0101059X" => "Ecole Supérieure de Commerce",
  "0511935B" => "Institut Universitaire de Formation des Maîtres de l'académie de Reims",
  "0510088U" => "Reims management school",
  "0511296G" => "Université de Reims",
  "0101060Y" => "Université de Technologie de Troyes",
  "0352330T" => "ECOLE DE GESTION ET DE COMMERCE DE BRETAGNE DE LA CHAMBRE DE COMMERCE ET D'INDUSTRIE DU PAYS DE ST MALO",
  "0352337A" => "ECOLE LOUIS DE BROGLIE",
  "0350095N" => "ECOLE NATIONALE DE LA SANTE PUBLIQUE",
  "0352480F" => "ECOLE NATIONALE DE LA STATISTIQUE ET DE L'ANALYSE DE L'INFORMATION",
  "0290119X" => "ECOLE NATIONALE D'INGENIEURS DE BREST",
  "0350087E" => "ECOLE NATIONALE SUPERIEURE AGRONOMIQUE DE RENNES (AGROCAMPUS RENNES)",
  "0350077U" => "ECOLE NATIONALE SUPERIEURE DE CHIMIE DE RENNES, RATTACHEE A L'UNIVERSITE DE RENNES 1",
  "0290125D" => "ECOLE NATIONALE SUPERIEURE DES INGENIEURS DES ETUDES ET TECHNIQUES D'ARMEMENT",
  "0291811L" => "ECOLE NATIONALE SUPERIEURE DES TELECOMMUNICATIONS DE BRETAGNE",
  "0290124C" => "ECOLE NAVALE",
  "0560068V" => "ECOLE SPECIALE MILITAIRE DE SAINT CYR",
  "0290127F" => "Ecole Supérieure de Commerce de Bretagne Brest de la Chambre de Commerce et d'Industrie de Brest",
  "0351842M" => "ECOLE SUPERIEURE ET D'APPLICATION DES TRANSMISSIONS",
  "0352373P" => "ECOLE SUPERIEURE PRIVEE EME",
  "0352305R" => "GROUPE ECOLE SUPERIEURE DE COMMERCE DE RENNES",
  "0352422T" => "INSTITUT D'ETUDES SUPERIEURES D'INDUSTRIE ET D'ECONOMIE LAITIERES (AGROCAMPUS RENNES)",
  "0350097R" => "INSTITUT NATIONAL DES SCIENCES APPLIQUEES DE RENNES",
  "0352347L" => "INSTITUT NATIONAL SUPERIEUR DE FORMATION AGROALIMENTAIRE (AGROCAMPUS RENNES)",
  "0292125C" => "INSTITUT SUPERIEUR DE L'ELECTRONIQUE ET DU NUMERIQUE",
  "0352291A" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE RENNES",
  "0290346U" => "UNIVERSITE DE BREST",
  "0561718N" => "UNIVERSITE DE BRETAGNE SUD",
  "0350936C" => "UNIVERSITE RENNES 1",
  "0350937D" => "UNIVERSITE RENNES 2",
  "9741101D" => "ECOLE DE GESTION ET COMMERCE CTRE CONSUL FORM.STE CLOTILDE",
  "9741061K" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE LA REUNION",
  "9740478B" => "UNIVERSITE DE LA REUNION",
  "0762969P" => "CENTRE D'ETUDES SUPERIEURES INDUSTRIELLES CENTRE REGIONAL DE NORMANDIE",
  "0760167U" => "ECOLE DE SUPERIEURE DE COMMERCE DE ROUEN",
  "0760168V" => "ECOLE SUPERIEURE DE COMMERCE SUP. DE CO. LE HAVRE CAEN",
  "0762378X" => "ECOLE SUPERIEURE D'INGENIEURS EN GENIE ELECTRIQUE",
  "0271338H" => "Ecole supérieure d'ingénieurs et techniciens pour l'agriculture",
  "0760165S" => "INSTITUT NATIONAL DES SCIENCES APPLIQUEES DE ROUEN",
  "0762970R" => "INSTITUT PORTUAIRE D ENSEIGNEMENT ET RECHERCHE LE HAVRE",
  "0762952W" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE ROUEN",
  "0761904G" => "UNIVERSITE DE ROUEN",
  "0762762P" => "UNIVERSITE DU HAVRE",
  "0670189S" => "ECOLE NATIONALE DU GENIE DE L'EAU ET DE L'ENVIRONNEMENT DE STRASBOURG",
  "0680097L" => "ECOLE NATIONALE SUPERIEURE DE CHIMIE",
  "0670190T" => "Institut nationale des sciences appliquées de Strasbourg",
  "0672635A" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L'ACADEMIE DE STRASBOURG",
  "0681166Y" => "UNIVERSITE DE MULHOUSE",
  "0671712X" => "UNIVERSITE STRASBOURG 1",
  "0671713Y" => "UNIVERSITE STRASBOURG 2",
  "0671778U" => "UNIVERSITE STRASBOURG 3",
  "0312020C" => "Centre d'Etudes Supérieures Industrielles Midi-Pyrénées",
  "0811293R" => "Centre Universitaire de Formation et de Recherche du Nord-Est Midi-Pyrénées Jean-Francois Champollion Albi",
  "0820822Y" => "Ecole de Gestion et de Commerce",
  "0310154Z" => "Ecole d'Ingénieur de purpan",
  "0312069F" => "Ecole Nationale de la Météorologie",
  "0311256X" => "Ecole Nationale de l'Aviation Civile de Toulouse",
  "0650048Z" => "Ecole Nationale d'Ingénieurs de Tarbes",
  "0811200P" => "Ecole Nationale Supérieure des Techniques Industrielles et des Mines d'Albi-Carmaux",
  "0121367W" => "Ecole supérieure technique privée de gestion commerce informatique",
  "0312013V" => "Ecole supérieure technique privée gestion commerce informatique",
  "0310156B" => "Groupe Ecole supérieure de Commerce",
  "0310155A" => "Institut catholique Toulouse",
  "0312421N" => "Institut Catholique d'arts et métiers de Toulouse",
  "0310152X" => "Institut National des Sciences Appliquées de Toulouse",
  "0311381H" => "Institut National Polytechnique de Toulouse",
  "0312760G" => "Institut Supérieur de l'Aéronautique et de l'Espace",
  "0312299F" => "Institut Universitaire de Formation des Maîtres Académie de Toulouse Midi-Pyrénées",
  "0311382J" => "Université Toulouse 1 Sciences Sociales",
  "0311383K" => "Université Toulouse 2 Le Mirail",
  "0311384L" => "Université Toulouse 3 Paul Sabatier",
  "0922455U" => "CTRE D'ETUDES SUPERIEURES INDUSTRIELLES CTRE REGIONAL D'ILE DE FRANCE",
  "0921682D" => "EC NAT STAT ADM ECO MALAKOFF",
  "0921225G" => "ECOLE CENTRALE DES ARTS ET MANUFACTURES",
  "0951820M" => "ECOLE DE BIOLOGIE INDUSTRIELLE EBI",
  "0922369A" => "ECOLE DE MANAGEMENT LEONARD DE VINCI",
  "0951819L" => "ECOLE D'ELECTRICITE DE PRODUCTION ET DES METHODES INDUSTRIELLES EPMI",
  "0783054W" => "ECOLE HTES ETUDES COMMERCIALES H E C",
  "0951623Y" => "ECOLE INTERNATIONALE DES SCIENCES DU TRAITEMENT DE L'INFORMATION",
  "0951376E" => "ECOLE NATIONALE SUPERIEURE DE L'ELECTRONIQUE ET DE SES APPLICATIONS DE CERGY",
  "0910684Z" => "ECOLE NATIONALE SUPERIEURE DES INDUSTRIES AGRICOLES ET ALIMENTAIRES",
  "0920815L" => "ECOLE NATIONALE SUPERIEURE DU PETROLE ET DES MOTEURS DE RUEIL",
  "0911568K" => "ECOLE POLYTECHNIQUE",
  "0920674H" => "ECOLE POLYTECHNIQUE FEMININE",
  "0922007G" => "ECOLE PRIVEE DES DIRIGEANTS ET CREATEURS D'ENTREPRISES",
  "0951214D" => "ECOLE SUP SC ECO COMMERCIALES INSTITUT CATHOLIQUE",
  "0951803U" => "ECOLE SUPERIEURE DE CHIMIE ORGANIQUE ET MINERALE ESCOM",
  "0920672F" => "ECOLE SUPERIEURE DE FONDERIE",
  "0911494E" => "ECOLE SUPERIEURE D'ELECTRICITE PRIVEE SUPELEC",
  "0921929X" => "ECOLE SUPERIEURE DES TECHNIQUES AERONAUTIQUES ET DE CONSTRUCTION AUTOMOBILE",
  "0922563L" => "Ecole Supérieure d'Ingénieurs Léonard De Vinci. Etablissement d'Enseignement Supérieur Technique Privé.",
  "0910725U" => "ECOLE SUPERIEURE D'OPTIQUE",
  "0922374F" => "ECOLE SUPERIEURE PRIVEE COMMERCE EXTERIEUR ESCE LDV",
  "0951804V" => "institut géologique albert de lapparent (igal)",
  "0910685A" => "INSTITUT NATIONAL DES SCIENCES ET TECHNIQUES NUCLEAIRES",
  "0911781S" => "INSTITUT NATIONAL DES TELECOMMUNICATIONS",
  "0951808Z" => "institut supérieur des techniques d'outre-mer (istom)",
  "0781938H" => "INSTITUT UNIVERSITAIRE DE FORMATION DES MAITRES DE L 'ACADEMIE DE VERSAILLES",
  "0951793H" => "UNIVERSITE CERGY PONTOISE",
  "0781944P" => "UNIVERSITE DE VERSAILLES SAINT QUENTIN EN YVELINES",
  "0911975C" => "UNIVERSITE EVRY VAL D ESSONNE",
  "0921204J" => "UNIVERSITE PARIS 10",
  "0911101C" => "UNIVERSITE PARIS 11",
);

$GLOBALS['supannTranslateEtablissementDirectory'] = array(
  'UAI' => $GLOBALS['tableCodeUAI']
);
?>
