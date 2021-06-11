<?php

/*
 *******************************************************************************************************************
 * Ce fichier a été généré le 2021-06-11 à 11:13:31 en utilisant le script convert_supann_population_from_csv.php.
 * Son contenu est basé sur un export CSV des catégories de populations fournies par SUPANN au format XLSX
 * et datant du 2021-06-10 à 15:27:04.
 *
 * Note : Le script convert_supann_population_from_csv.php est fourni avec les sources du projet LdapSaisie dans
 * le dossier resources/supann.
 *******************************************************************************************************************
 */


$GLOBALS['supannPopulations'] = array (
  'R' => 
  array (
    'subpopulations' => 
    array (
      'G' => 
      array (
        'subpopulations' => 
        array (
          'P' => 
          array (
            'subpopulations' => 
            array (
              'F' => 
              array (
                'subpopulations' => 
                array (
                  'T' => 
                  array (
                    'label' => 'Enseignant-chercheur titulaire',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'researcher',
                      3 => 'member',
                      4 => 'teacher',
                    ),
                    'definition' => 'Personnel géré permanent titulaire exerçant une activité d\'enseignement et de recherche',
                    'poids' => 800,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'C' => 
                  array (
                    'label' => 'Enseignant-chercheur contractuel',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'researcher',
                      3 => 'member',
                      4 => 'teacher',
                    ),
                    'definition' => 'Personnel géré permanent contractuel exerçant une activité d\'enseignement et de recherche',
                    'poids' => 800,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Enseignant-chercheur',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'employee',
                  2 => 'researcher',
                  3 => 'member',
                  4 => 'teacher',
                ),
                'definition' => 'Personnel géré permanent exerçant une activité d\'enseignement et de recherche dans l\'établissement',
                'poids' => 800,
              ),
              'C' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Chercheur',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'employee',
                  2 => 'researcher',
                  3 => 'member',
                ),
                'definition' => 'Personnel géré permanent exerçant uniquement une activité de recherche dans l\'établissement',
                'poids' => 800,
              ),
              'E' => 
              array (
                'subpopulations' => 
                array (
                  'T' => 
                  array (
                    'label' => 'Enseignant titulaire',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'member',
                      3 => 'teacher',
                    ),
                    'definition' => 'Personnel géré permanent titulaire exerçant uniquement une activité d\'enseignement',
                    'poids' => 800,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'C' => 
                  array (
                    'label' => 'Enseignant contractuel',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'member',
                      3 => 'teacher',
                    ),
                    'definition' => 'Personnel géré permanent contractuel exerçant uniquement une activité d\'enseignement',
                    'poids' => 800,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Enseignant',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'employee',
                  2 => 'member',
                  3 => 'teacher',
                ),
                'definition' => 'Personnel géré permanent exerçant uniquement une activité d\'enseignement dans l\'établissement',
                'poids' => 800,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                  'T' => 
                  array (
                    'label' => 'Personnel administratif ou technique titulaire',
                    'affiliations' => 
                    array (
                      0 => 'employee',
                      1 => 'member',
                      2 => 'staff',
                    ),
                    'definition' => 'Personnel géré permanent administratif ou technique titulaire',
                    'poids' => 800,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'C' => 
                  array (
                    'label' => 'Personnel administratif ou technique contractuel',
                    'affiliations' => 
                    array (
                      0 => 'employee',
                      1 => 'member',
                      2 => 'staff',
                    ),
                    'definition' => 'Personnel géré permanent administratif ou technique contractuel',
                    'poids' => 800,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Personnel administratif ou technique',
                'affiliations' => 
                array (
                  0 => 'employee',
                  1 => 'member',
                  2 => 'staff',
                ),
                'definition' => 'Personnel géré permanent exerçant une activité administrative ou technique',
                'poids' => 800,
              ),
            ),
            'label' => 'Personnel géré permanent',
            'affiliations' => 
            array (
              0 => 'employee',
              1 => 'member',
            ),
            'definition' => 'Personnel géré exerçant son activité principale dans l\'établissement',
            'poids' => 800,
          ),
          'N' => 
          array (
            'subpopulations' => 
            array (
              'F' => 
              array (
                'subpopulations' => 
                array (
                  'C' => 
                  array (
                    'label' => 'Contractuel recherche non-doctorant avec enseignement',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'researcher',
                      3 => 'member',
                      4 => 'teacher',
                    ),
                    'definition' => 'Personnel exerçant une activité d\'enseignement et de recherche, dont le statut juridique impose une durée maximale d\'exercice, hors stage doctoral',
                    'poids' => 750,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'D' => 
                  array (
                    'label' => 'Doctorant Contractuel avec enseignement',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'researcher',
                      3 => 'member',
                      4 => 'teacher',
                    ),
                    'definition' => 'Personnel exerçant une activité d\'enseignement et de recherche, dont le statut juridique impose une durée maximale d\'exercice,dans le cadre d\'un stage doctoral',
                    'poids' => 750,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'A' => 
                  array (
                    'label' => 'Professeur associé',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'researcher',
                      3 => 'member',
                      4 => 'teacher',
                    ),
                    'definition' => 'Personne recrutée par l\'établissement en qualité de professeur des universités ou de maître de conférence associé, selon un statut juridique imposant une durée maximale d\'exercice',
                    'poids' => 750,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => ' Enseignant-chercheur non-permanent',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'employee',
                  2 => 'researcher',
                  3 => 'member',
                  4 => 'teacher',
                ),
                'definition' => 'Personnel exerçant une activité d\'enseignement et de recherche, dont le statut juridique impose une durée maximale d\'exercice',
                'poids' => 750,
              ),
              'C' => 
              array (
                'subpopulations' => 
                array (
                  'C' => 
                  array (
                    'label' => 'Contractuel recherche non-doctorant sans enseignement',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'researcher',
                      3 => 'member',
                    ),
                    'definition' => 'Personnel exerçant une activité de recherche, sans enseignement, dont le statut juridique impose une durée maximale d\'exercice, hors stage doctoral',
                    'poids' => 750,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'D' => 
                  array (
                    'label' => 'Doctorant Contractuel sans enseignement',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'employee',
                      2 => 'researcher',
                      3 => 'member',
                    ),
                    'definition' => 'Personnel exerçant une activité de recherche, sans enseignement, dont le statut juridique impose une durée maximale d\'exercice, dans le cadre d\'un stage doctoral',
                    'poids' => 750,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Chercheur non-permanent',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'employee',
                  2 => 'researcher',
                  3 => 'member',
                ),
                'definition' => 'Personnel géré exerçant une activité de recherche dans l\'établissement, sans enseignement,  dont le statut juridique impose une durée maximale d\'exercice',
                'poids' => 750,
              ),
              'E' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Enseignant non-permanent',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'employee',
                  2 => 'member',
                  3 => 'teacher',
                ),
                'definition' => 'Personnel permanent géré exerçant une activité d\'enseignement, sans activité de recherche, dont le statut juridique impose une durée maximale d\'exercice',
                'poids' => 750,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                  'P' => 
                  array (
                    'label' => 'Apprenti',
                    'affiliations' => 
                    array (
                      0 => 'employee',
                      1 => 'member',
                      2 => 'staff',
                    ),
                    'definition' => 'Employé administratif ou technique titulaire d\'un contrat d\'apprentissage ou de professionnalisation',
                    'poids' => 750,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Personnel administratif ou technique non-permanent',
                'affiliations' => 
                array (
                  0 => 'employee',
                  1 => 'member',
                  2 => 'staff',
                ),
                'definition' => 'Personnel géré effectuant des tâches admlnistratives ou techniques, dont le statut juridique impose une durée maximale d\'exercice',
                'poids' => 750,
              ),
            ),
            'label' => 'Personnel géré non-permanent',
            'affiliations' => 
            array (
              0 => 'employee',
              1 => 'member',
            ),
            'definition' => 'Personnel géré exerçant dans l\'établissement au titre d’une activité connexe à temps partiel ou d’une activité principale limitée dans le temps de par son statut',
            'poids' => 750,
          ),
          'I' => 
          array (
            'subpopulations' => 
            array (
              'E' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Enseignant vacataire',
                'affiliations' => 
                array (
                  0 => 'employee',
                  1 => 'member',
                  2 => 'teacher',
                ),
                'definition' => 'Personne effectuant des enseignements à titre d\'activité annexe de son activité principale, sur la base d\'un contrat de vacation conclu pour une durée et un volume d\'heures définis',
                'poids' => 700,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Contractuel administratif saisonnier',
                'affiliations' => 
                array (
                  0 => 'employee',
                  1 => 'member',
                  2 => 'staff',
                ),
                'definition' => 'Contractuel assurant des activités administratives ou techniques saisonnières ou à volume horaire réduit, pour une durée courte',
                'poids' => 700,
              ),
            ),
            'label' => 'Personnel géré intérimaire',
            'affiliations' => 
            array (
              0 => 'employee',
              1 => 'member',
            ),
            'definition' => 'Personnel géré effectuant des tâches à durée limitée et volume horaire réduit, annexes à une autre activité, avec implication modérée dans la vie de l\'établissement',
            'poids' => 700,
          ),
          'R' => 
          array (
            'subpopulations' => 
            array (
              'E' => 
              array (
                'subpopulations' => 
                array (
                  'R' => 
                  array (
                    'label' => 'Tuteur',
                    'affiliations' => 
                    array (
                      0 => 'employee',
                      1 => 'teacher',
                    ),
                    'definition' => 'Personnel affecté à des tâches ponctuelles de tutorat pédagogique ou technique, d\'aide au public',
                    'poids' => 400,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Employé pédagogique ponctuel',
                'affiliations' => 
                array (
                  0 => 'employee',
                  1 => 'teacher',
                ),
                'definition' => 'Personnel géré ponctuel effectuant des tâches à caractère pédagogique',
                'poids' => 400,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                  'N' => 
                  array (
                    'label' => 'Assistant à la personne',
                    'affiliations' => 
                    array (
                      0 => 'employee',
                      1 => 'staff',
                    ),
                    'definition' => 'Personnel géré ponctuel effectuant des tâches d\'assistance à la Personne',
                    'poids' => 400,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'Q' => 
                  array (
                    'label' => 'Assistant administratif ou technique',
                    'affiliations' => 
                    array (
                      0 => 'employee',
                      1 => 'staff',
                    ),
                    'definition' => 'Personnel géré ponctuel effectuant des tâches d\'assistance administratives ou techniques',
                    'poids' => 400,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'L' => 
                  array (
                    'label' => 'Surveillant',
                    'affiliations' => 
                    array (
                      0 => 'employee',
                      1 => 'staff',
                    ),
                    'definition' => 'Personnel géré ponctuel effectuant des tâches de surveillance, de gardiennage ou de vigilance',
                    'poids' => 400,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Employé administratif ou technique ponctuel',
                'affiliations' => 
                array (
                  0 => 'employee',
                  1 => 'staff',
                ),
                'definition' => 'Personnel géré ponctuel effectuant des tâches administratives ou techniques',
                'poids' => 400,
              ),
            ),
            'label' => 'Personnel géré ponctuel',
            'affiliations' => 
            array (
              0 => 'employee',
            ),
            'definition' => 'Personnel géré effectuant des tâches administratives, techniques ou d\'accompagnement à titre d\'activité annexe, rémunéré à la tâche ou à l\'heure, sans implication dans la vie de l\'établissement',
            'poids' => 400,
          ),
          'O' => 
          array (
            'subpopulations' => 
            array (
              'D' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Employé temporairement inactif',
                'affiliations' => 
                array (
                  0 => 'employee',
                  1 => 'member',
                ),
                'definition' => 'Personnel géré ayant temporairement interrompu son activité dans l\'établissement, mais conservant le statut de membre',
                'poids' => 150,
              ),
              'B' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Employé inactif',
                'affiliations' => 
                array (
                  0 => 'employee',
                ),
                'definition' => 'Personnel géré n\'étant pas reconnu comme membre de l\'établissement',
                'poids' => 150,
              ),
            ),
            'label' => 'Personnel géré inactif',
            'affiliations' => 
            array (
              0 => 'employee',
            ),
            'definition' => 'Personnel géré n\'exerçant pas pour l\'établissement',
            'poids' => 150,
          ),
        ),
        'label' => 'Personnel géré',
        'affiliations' => 
        array (
          0 => 'employee',
        ),
        'definition' => 'Personnel employé par l\'établissement',
        'poids' => NULL,
      ),
      'H' => 
      array (
        'subpopulations' => 
        array (
          'T' => 
          array (
            'subpopulations' => 
            array (
              'F' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Enseignant-Chercheur co-tutélaire',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'researcher',
                  2 => 'member',
                  3 => 'teacher',
                ),
                'definition' => 'Personnel exerçant une activité de recherche et d\'enseignement dans une structure à tutelles multiples de l\'établissement, géré par une des tutelles de la structure autre que l\'établissement lui-même',
                'poids' => 780,
              ),
              'C' => 
              array (
                'subpopulations' => 
                array (
                  'O' => 
                  array (
                    'label' => 'Chercheur d\'organisme de recherche',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'researcher',
                      2 => 'member',
                    ),
                    'definition' => 'Personnel exerçant une activité de recherche dans une structure à tutelles multiples de l\'établissement, géré par un  organisme de recherche (ESPT, EPA, EPIC, etc) co-tutélaire de cette structure',
                    'poids' => 780,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'E' => 
                  array (
                    'label' => 'Chercheur EPSCP hébergé',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'researcher',
                      2 => 'member',
                    ),
                    'definition' => 'Chercheur ou enseignant-chercheur géré par un EPSCP co-tutélaire sa stucture d\'affectation, exerçant dans celle-ci une activité de recherche (son activité d\'enseignement s\'effectuant dans une autre structure de son établissement de tutelle, sans objet ici)',
                    'poids' => 780,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'H' => 
                  array (
                    'label' => 'Praticien hospitalier',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'researcher',
                      2 => 'member',
                    ),
                    'definition' => 'Praticien hospitalier exerçant une activité de recherche dans une des structures à tutelles multiples de l\'établissement, géré par un établisement public de santé co-tutélaire de cette structure',
                    'poids' => 780,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Chercheur co-tutélaire',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'researcher',
                  2 => 'member',
                ),
                'definition' => 'Personnel exerçant une activité de recherche dans une structure à tutelles multiples de l\'établissement, géré par une des tutelles de la structure autre que l\'établissement lui-même',
                'poids' => 780,
              ),
              'E' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Enseignant co-tutélaire',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'member',
                  2 => 'teacher',
                ),
                'definition' => 'Personnel exerçant une activité d\'enseignement dans une structure à tutelles multiples de l\'établissement, géré par une des tutelles de la structure autre que l\'établissement lui-même',
                'poids' => 780,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                  'O' => 
                  array (
                    'label' => 'Personnel administratif ou technique d\'organisme de recherche',
                    'affiliations' => 
                    array (
                      0 => 'member',
                      1 => 'staff',
                    ),
                    'definition' => 'Personnel exerçant une activité administrative ou technique dans une structure à tutelles multiples de l\'établissement, géré par un  organisme de recherche (ESPT, EPA, EPIC, etc) co-tutélaire de cette structure',
                    'poids' => 780,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'E' => 
                  array (
                    'label' => 'Personnel administratif ou technique EPSCP hébergé',
                    'affiliations' => 
                    array (
                      0 => 'member',
                      1 => 'staff',
                    ),
                    'definition' => 'Personnel exerçant une activité administrative ou technique dans une structure à tutelles multiples de l\'établissement, géré par un EPSCP co-tutélaire de sa structure d\'affectation',
                    'poids' => 780,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Personnel administratif ou technique co-tutélaire',
                'affiliations' => 
                array (
                  0 => 'member',
                  1 => 'staff',
                ),
                'definition' => 'Personnel exerçant une activité administrative ou technique dans une structure à tutelles multiples de l\'établissement, géré par une des tutelles de la structure autre que l\'établissement lui-même',
                'poids' => 780,
              ),
            ),
            'label' => 'Personnel co-tutélaire',
            'affiliations' => 
            array (
              0 => 'member',
            ),
            'definition' => 'Personnel membre d\'une structure à tutelles multiples de l\'établissement, géré par une des tutelles de la structure autre que l\'établissement lui-même',
            'poids' => 780,
          ),
          'M' => 
          array (
            'subpopulations' => 
            array (
              'F' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Émérite',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'researcher',
                  2 => 'emeritus',
                  3 => 'member',
                ),
                'definition' => 'Enseignant-chercheur retraité de l\'établissement ayant obtenu l\'éméritat ou un statut équivalent, validé par les instances de l\'établissement',
                'poids' => 780,
              ),
            ),
          ),
          'L' => 
          array (
            'subpopulations' => 
            array (
              'F' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Enseignant-chercheur sous convention',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'researcher',
                  2 => 'member',
                  3 => 'teacher',
                ),
                'definition' => 'Personnel tiers exerçant dans l\'établissement une activité d\'enseignement et de recherche au titre d\'une convention',
                'poids' => 720,
              ),
              'C' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Chercheur sous convention',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'researcher',
                  2 => 'member',
                ),
                'definition' => 'Personnel tiers exerçant dans l\'établissement une activité de recherche au titre d\'une convention',
                'poids' => 720,
              ),
              'E' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Enseignant sous convention',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'member',
                  2 => 'teacher',
                ),
                'definition' => 'Personnel tiers exerçant dans l\'établissement une activité d\'enseignement au titre d\'une convention',
                'poids' => 720,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Personnel administratif ou technique sous convention',
                'affiliations' => 
                array (
                  0 => 'member',
                  1 => 'staff',
                ),
                'definition' => 'Personnel tiers exerçant dans l\'établissement une activité administrative ou technique au titre d\'une convention',
                'poids' => 720,
              ),
            ),
            'label' => 'Personnel sous convention',
            'affiliations' => 
            array (
              0 => 'member',
            ),
            'definition' => 'Personnel géré par un organisme tiers, affecté dans l\'établissement au titre d\'une convention pérenne (échange de services, mutualisation de moyens, mise à disposition, …)',
            'poids' => 720,
          ),
          'J' => 
          array (
            'subpopulations' => 
            array (
              'F' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Enseignant-chercheur associé',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'researcher',
                  2 => 'member',
                  3 => 'teacher',
                ),
                'definition' => 'Personnel externe exerçant pour l\'établissement une activité d\'enseignement et de recherche temporaire dans le cadre d\'une association, invitation, convention  ou d\'un programme d\'échange',
                'poids' => 580,
              ),
              'C' => 
              array (
                'subpopulations' => 
                array (
                  'A' => 
                  array (
                    'label' => 'Collaborateur de recherche',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'researcher',
                      2 => 'member',
                    ),
                    'definition' => 'Chercheur ou enseignant-chercheur externe participant à un projet de recherche au sein d\'une structure de l\'établissement (sans y exercer d\'enseignement)',
                    'poids' => 580,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'S' => 
                  array (
                    'label' => 'Partenaire de recherche hébergé',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'researcher',
                      2 => 'member',
                    ),
                    'definition' => 'Chercheur membre d\'un organisme privé participant à un projet de recherche au sein d\'une structure de l\'établissement (sans y exercer d\'enseignement), dans le cadre d\'un partenariat',
                    'poids' => 580,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'F' => 
                  array (
                    'label' => 'Apprenant chercheur hébergé',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'researcher',
                      2 => 'member',
                    ),
                    'definition' => 'Doctorant ou apprenant en HDR d\'un autre établissement exerçant une activité de recherche dans une structure de l\'établissement, dans le cadre de son stage doctoral',
                    'poids' => 580,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                  'D' => 
                  array (
                    'label' => 'Jeune docteur hébergé',
                    'affiliations' => 
                    array (
                      0 => 'faculty',
                      1 => 'researcher',
                      2 => 'member',
                    ),
                    'definition' => 'Ancien apprenant ayant récemment obtenu son doctorat dans l\'établissement, hébergé en attente d\'une situation professionnelle',
                    'poids' => 580,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Chercheur associé',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'researcher',
                  2 => 'member',
                ),
                'definition' => 'Personnel externe exerçant pour l\'établissement une activité de recherche temporaire au titre d\'une association, invitation, convention ou d\'un programme d\'échange',
                'poids' => 580,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                  'G' => 
                  array (
                    'label' => 'Stagiaire hébergé',
                    'affiliations' => 
                    array (
                      0 => 'member',
                      1 => 'staff',
                    ),
                    'definition' => 'Stagiaire disposant d\'une convention de stage avec l\'établissement, une se ses structures ou une co-tutelle, intégré à son équipe d\'accueil',
                    'poids' => 580,
                    'subpopulations' => 
                    array (
                    ),
                  ),
                ),
                'label' => 'Personnel administratif ou technique associé',
                'affiliations' => 
                array (
                  0 => 'member',
                  1 => 'staff',
                ),
                'definition' => 'Personnel externe exerçant pour l\'établissement une activité administrative ou technique temporaire au titre d\'une association, invitation, convention ou d\'un programme d\'échange',
                'poids' => 580,
              ),
            ),
            'label' => 'Personnel associé',
            'affiliations' => 
            array (
              0 => 'member',
            ),
            'definition' => 'Personnel externe exerçant pour l\'établissement une activité temporaire dans le cadre d\'une association, d\'une invitation, d\'un partenariat, d\'un programme d\'échange ou d\'une convention de durée limitée',
            'poids' => 580,
          ),
          'H' => 
          array (
            'subpopulations' => 
            array (
              'F' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Enseignant-chercheur honoraire',
                'affiliations' => 
                array (
                  0 => 'faculty',
                  1 => 'retired',
                  2 => 'member',
                ),
                'definition' => 'Ancien enseignant, enseignant-chercheur ou chercheur à la retraite qui, au vu de ses activités passées dans l\'établissement, se voit accorder le droit d\'en conserver le titre et les prérogatives honorifiques (sans être titulaire de l\'éméritat)',
                'poids' => 500,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Personnel administratif ou technique honoraire',
                'affiliations' => 
                array (
                  0 => 'retired',
                  1 => 'member',
                  2 => 'staff',
                ),
                'definition' => 'Ancien personnel administratif ou technique à la retraite qui, au vu de ses activités passées dans l\'établissement, se voit accorder le droit d\'en conserver le titre et les prérogatives honorifiques',
                'poids' => 500,
              ),
            ),
            'label' => 'Personnel honoraire',
            'affiliations' => 
            array (
              0 => 'retired',
              1 => 'member',
            ),
            'definition' => 'Ancien personnel à la retraite qui, au vu de ses activités passées dans l\'établissement, se voit accorder le droit d\'en conserver le titre et les prérogatives honorifiques',
            'poids' => 500,
          ),
        ),
        'label' => 'Personnel hébergé',
        'affiliations' => 
        array (
          0 => 'member',
        ),
        'definition' => 'Personnel exerçant pour l\'établissement mais non employé par celui-ci',
        'poids' => NULL,
      ),
    ),
    'label' => 'Personnes Ressources',
    'affiliations' => 
    array (
    ),
    'definition' => '',
    'poids' => NULL,
  ),
  'A' => 
  array (
    'subpopulations' => 
    array (
      'G' => 
      array (
        'subpopulations' => 
        array (
          'A' => 
          array (
            'subpopulations' => 
            array (
              'H' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Candidat HDR',
                'affiliations' => 
                array (
                  0 => 'student',
                  1 => 'researcher',
                  2 => 'member',
                ),
                'definition' => 'Apprenant inscrit à une habilitation à diriger des recherches (HDR) délivrée par l\'établissement',
                'poids' => 650,
              ),
              'D' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Doctorant inscrit',
                'affiliations' => 
                array (
                  0 => 'student',
                  1 => 'researcher',
                  2 => 'member',
                ),
                'definition' => 'Apprenant inscrit en doctorat dans l\'établissement',
                'poids' => 650,
              ),
            ),
            'label' => 'Apprenant chercheur inscrit',
            'affiliations' => 
            array (
              0 => 'student',
              1 => 'researcher',
              2 => 'member',
            ),
            'definition' => 'Apprenant inscrit à une formation diplômante supérieure délivrée par l\'établissement avec participation aux activités de recherche',
            'poids' => 650,
          ),
          'E' => 
          array (
            'subpopulations' => 
            array (
              'I' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Etudiant',
                'affiliations' => 
                array (
                  0 => 'student',
                  1 => 'member',
                ),
                'definition' => 'Etudiant inscrit à une formation initiale diplômante de l\'établissement n\'impliquant pas de participation aux activités de recherche, participant aux cours/TD en présentiel',
                'poids' => 600,
              ),
              'D' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Etudiant en FOAD/EAD',
                'affiliations' => 
                array (
                  0 => 'student',
                  1 => 'member',
                ),
                'definition' => 'Etudiant inscrit à une formation diplômante de l\'établissement n\'impliquant pas de participation aux activités de recherche, via un régime de formation à distance',
                'poids' => 600,
              ),
              'C' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Etudiant en formation continue',
                'affiliations' => 
                array (
                  0 => 'student',
                  1 => 'member',
                ),
                'definition' => 'Etudiant inscrit à une formation diplômante de l\'établissement n\'impliquant pas de participation aux activités de recherche, en régime de formation continue',
                'poids' => 600,
              ),
            ),
            'label' => 'Etudiant inscrit',
            'affiliations' => 
            array (
              0 => 'student',
              1 => 'member',
            ),
            'definition' => 'Apprenant inscrit à une formation diplômante d\'un niveau supérieur au baccalauréat délivrée par l\'établissement (sans participation aux activités de recherche)',
            'poids' => 600,
          ),
          'S' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Élève inscrit',
            'affiliations' => 
            array (
              0 => 'student',
              1 => 'member',
            ),
            'definition' => 'Apprenant inscrit dans l\'établissement à une formation certifiante ou diplômante d\'un niveau inférieur ou égal au baccalauréat',
            'poids' => 600,
          ),
          'L' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Auditeur libre inscrit',
            'affiliations' => 
            array (
              0 => 'student',
              1 => 'member',
            ),
            'definition' => 'Auditeur libre inscrit dans l\'établissement',
            'poids' => 550,
          ),
          'G' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Apprenant stagiaire',
            'affiliations' => 
            array (
              0 => 'student',
            ),
            'definition' => 'Apprenant suivant une formation courte non diplômante délivrée par l\'établissement ou une de ses composantes, certifiante ou non certifiante',
            'poids' => 460,
          ),
        ),
        'label' => 'Apprenants gérés',
        'affiliations' => 
        array (
          0 => 'student',
          1 => 'member',
        ),
        'definition' => 'Apprenants inscrits dans l\'établissement au titre d\'une formation délivrée par celui-ci',
        'poids' => NULL,
      ),
      'H' => 
      array (
        'subpopulations' => 
        array (
          'H' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Apprenant co-habilité',
            'affiliations' => 
            array (
              0 => 'student',
            ),
            'definition' => 'Apprenant inscrit dans un autre établissement, mais suivant des enseignements ou accédant à des ressources de l\'établissement dans le cadre d\'une formation co-habilitée',
            'poids' => 500,
          ),
          'L' => 
          array (
            'subpopulations' => 
            array (
              'P' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Auditeur libre non inscrit',
                'affiliations' => 
                array (
                  0 => 'student',
                ),
                'definition' => 'Personne assistant à des cours ou enseignements en présentiel sans inscription formalisée',
                'poids' => 250,
              ),
              'D' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Auditeur en FOAD',
                'affiliations' => 
                array (
                  0 => 'student',
                ),
                'definition' => 'Personne suivant des cours ou des formations en ligne sans inscription formalisée',
                'poids' => 250,
              ),
            ),
            'label' => 'Auditeur',
            'affiliations' => 
            array (
              0 => 'student',
            ),
            'definition' => 'Apprenant suivant des formations de l\'établissement sans lien administratif ni contractuel',
            'poids' => 250,
          ),
        ),
        'label' => 'Apprenants hébergés',
        'affiliations' => 
        array (
          0 => 'student',
        ),
        'definition' => 'Apprenants suivant des enseignements dans l\'établissement au titre d\'une formation délivrée par un établissement tiers cohabilité ou conventionné',
        'poids' => NULL,
      ),
      'X' => 
      array (
        'subpopulations' => 
        array (
        ),
        'label' => 'Apprenant externe',
        'affiliations' => 
        array (
          0 => 'student',
        ),
        'definition' => 'Apprenant non géré ni référencé par l\'établissement, utilisant des ressources pédagogiques de l\'établissement dans le cadre d\'une formation effectuée pour le compte d\'un tiers',
        'poids' => 180,
      ),
    ),
    'label' => 'Apprenants',
    'affiliations' => 
    array (
      0 => 'student',
    ),
    'definition' => '',
    'poids' => 0,
  ),
  'P' => 
  array (
    'subpopulations' => 
    array (
      'X' => 
      array (
        'subpopulations' => 
        array (
          'C' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Membre extérieur de conseil',
            'affiliations' => 
            array (
              0 => 'affiliate',
            ),
            'definition' => 'Personne extérieure siégeant à l\'une des instances, conseils ou commissions de l\'établissement',
            'poids' => 450,
          ),
          'T' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Membre d\'autorité tutélaire',
            'affiliations' => 
            array (
              0 => 'affiliate',
            ),
            'definition' => 'Personne extérieure membre d\'une autorité de tutelle ou missionnée par celle-ci, assurant dans l\'établissement une mission ponctuelle d\'audit, de contrôle, d\'évaluation ou de gestion',
            'poids' => 450,
          ),
          'J' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Membre de jury',
            'affiliations' => 
            array (
              0 => 'affiliate',
            ),
            'definition' => 'Personne extérieure endossant un rôle ponctuel de membre de jury dans l\'établissement',
            'poids' => 430,
          ),
          'P' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Encadrant pédagogique',
            'affiliations' => 
            array (
              0 => 'affiliate',
              1 => 'teacher',
            ),
            'definition' => 'Personne extérieure endossant un rôle ponctuel de d\'encadrement pédagogique dans l\'établissement',
            'poids' => 430,
          ),
          'I' => 
          array (
            'subpopulations' => 
            array (
              'G' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Stagiaire invité',
                'affiliations' => 
                array (
                  0 => 'affiliate',
                ),
                'definition' => 'Stagiaire, participant aux activités administratives ou techniques de l\'établissement dans le cadre d\'un cursus de formation, non intégré à son équipe d\'accueil',
                'poids' => 300,
              ),
            ),
            'label' => 'Invité',
            'affiliations' => 
            array (
              0 => 'affiliate',
            ),
            'definition' => 'Personne externe à l\'établissement, invitée par celui-ci ou une de ses structures pour collaborer sur une activité ponctuelle',
            'poids' => 300,
          ),
          'S' => 
          array (
            'subpopulations' => 
            array (
              'E' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Prestataire enseignant',
                'affiliations' => 
                array (
                  0 => 'affiliate',
                  1 => 'teacher',
                ),
                'definition' => 'Prestataire de services effectuant des activités d\'enseignement',
                'poids' => 280,
              ),
              'S' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Prestataire administratif ou technique intégré',
                'affiliations' => 
                array (
                  0 => 'affiliate',
                  1 => 'staff',
                ),
                'definition' => 'Prestataire de services nécessitant une intégration forte avec le personnel de l\'établissement. Présent régulièrement sur site avec des horaires proches de ceux d\'un employé, reçoivent certaines listes de diffusion, sont au contact d\'un nombre important d\'usagers. Reste néanmoins aux ordres de son employeur, lequel est lié à l\'établissement par une relation de nature commerciale.',
                'poids' => 280,
              ),
              'P' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Prestataire autre',
                'affiliations' => 
                array (
                  0 => 'affiliate',
                ),
                'definition' => 'Prestataire de service extérieur avec un besoin minimum d\'interaction aves les usagers. Ne dialogue avec l\'établissement qu\'au travers de sa hiérarchie, a une mission définie d\'avance.',
                'poids' => 280,
              ),
            ),
            'label' => 'Prestataire de services',
            'affiliations' => 
            array (
              0 => 'affiliate',
            ),
            'definition' => 'Prestataire de service extérieur à l\'établissement, lié par un relation (contrat ou marché) de nature commerciale',
            'poids' => 280,
          ),
          'O' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Orateur externe',
            'affiliations' => 
            array (
              0 => 'affiliate',
              1 => 'teacher',
            ),
            'definition' => 'Personne extérieure à l\'établissement endossant un rôle ponctuel de conférencier, orateur ou toute autre activité d\'enseignement hors encadrement (sans contrat, convention ni rémunération)',
            'poids' => 260,
          ),
          'A' => 
          array (
            'subpopulations' => 
            array (
              'N' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Ancien apprenant non diplômé',
                'affiliations' => 
                array (
                  0 => 'alum',
                ),
                'definition' => 'Ancien apprenant, non diplômé de l\'établissement, maintenant des relations avec celui-ci',
                'poids' => 230,
              ),
              'D' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Ancien diplômé',
                'affiliations' => 
                array (
                  0 => 'alum',
                ),
                'definition' => 'Ancien diplômé de l\'établissement maintenant des relations avec celui-ci',
                'poids' => 230,
              ),
            ),
            'label' => 'Ancien apprenant',
            'affiliations' => 
            array (
              0 => 'alum',
            ),
            'definition' => 'Ancien étudiant ou apprenant maintenant des relations avec l\'établissement',
            'poids' => 230,
          ),
          'R' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Retraité',
            'affiliations' => 
            array (
              0 => 'retired',
            ),
            'definition' => 'Ancien personnel de l\'établissement à la retraite maintenant des relations avec celui-ci',
            'poids' => 220,
          ),
          'B' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Contact entreprise',
            'affiliations' => 
            array (
              0 => 'affiliate',
            ),
            'definition' => 'Représentant ou membre d\'une entreprise ou d\'un établissement externe maintenant des relation régulières avec l\'établissement dans le cadre d\'intérêts mutuels ou d\'une collaboration',
            'poids' => 200,
          ),
          'E' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Enseignant externe',
            'affiliations' => 
            array (
              0 => 'affiliate',
              1 => 'teacher',
            ),
            'definition' => 'Personne exerçant une activité d\'enseignement non endossée par l\'établissement, mais utilisant des ressources de celui-ci, notamment pédagogiques, dans le cadre d\'un contrat à titre onéreux ou d\'une convention',
            'poids' => 200,
          ),
          'U' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Utilisateur hébergé',
            'affiliations' => 
            array (
              0 => 'affiliate',
            ),
            'definition' => 'Personne externe à l\'établissement et sans activité liée à celui-ci, utilisant des ressources non pédagogiques de l\'établissement dans le cadre d\'un contrat à titre onéreux ou une convention',
            'poids' => 200,
          ),
          'L' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Lecteur de bibliothèque',
            'affiliations' => 
            array (
              0 => 'registered-reader',
            ),
            'definition' => 'Personne externe titulaire d\'une inscription (en général annuelle) à l\'une des bibliothèques rattachées à l\'établissement',
            'poids' => 190,
          ),
          'V' => 
          array (
            'subpopulations' => 
            array (
              'B' => 
              array (
                'subpopulations' => 
                array (
                ),
                'label' => 'Visiteur de bibliothèque',
                'affiliations' => 
                array (
                  0 => 'librery-walk-in',
                ),
                'definition' => 'Personne externe autorisée de façon ponctuelle à accéder aux locaux de la bibilothèque, sans autorisation d\'emprunt',
                'poids' => 110,
              ),
            ),
            'label' => 'Visiteur',
            'affiliations' => 
            array (
            ),
            'definition' => 'Personne sans activité régulière en rapport avec l\'établissement, ayant besoin d\'être référencée dans le SI pour un usage ponctuel ou occasionnel de ressources de celui-ci',
            'poids' => 100,
          ),
        ),
      ),
    ),
    'label' => 'Partenaires',
    'affiliations' => 
    array (
    ),
    'definition' => '',
    'poids' => NULL,
  ),
  'T' => 
  array (
    'subpopulations' => 
    array (
      'E' => 
      array (
        'subpopulations' => 
        array (
          'R' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Personnel entrant',
            'affiliations' => 
            array (
              0 => 'member',
            ),
            'definition' => 'Personne pour laquelle un rôle de PR doit être attribué de façon imminente',
            'poids' => 250,
          ),
          'A' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Candidat apprenant',
            'affiliations' => 
            array (
            ),
            'definition' => 'Personne ayant formulé une intention d\'inscription imminente et non déjà apprenant',
            'poids' => 110,
          ),
        ),
        'label' => 'Entrant',
        'affiliations' => 
        array (
        ),
        'definition' => 'Personne en instance d\'intégration dans l\'établissement',
        'poids' => NULL,
      ),
      'S' => 
      array (
        'subpopulations' => 
        array (
          'R' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Personnel sortant',
            'affiliations' => 
            array (
            ),
            'definition' => 'Ancienne personne ressource (gérée ou hébergée) dont le statut s\'est terminé récemment, à qui un sursis de droits d\'accès est accordé',
            'poids' => 130,
          ),
          'A' => 
          array (
            'subpopulations' => 
            array (
            ),
            'label' => 'Apprenant sortant',
            'affiliations' => 
            array (
              0 => 'alum',
            ),
            'definition' => 'Apprenant ayant terminé sa formation récemment, non réinscrit, à qui un sursis de droits d\'accès est accordé',
            'poids' => 120,
          ),
        ),
        'label' => 'Sortant',
        'affiliations' => 
        array (
        ),
        'definition' => 'Personne ayant quitté récemment l\'établissement',
        'poids' => NULL,
      ),
    ),
    'label' => 'Statuts transitoires',
    'affiliations' => 
    array (
    ),
    'definition' => '',
    'poids' => NULL,
  ),
);