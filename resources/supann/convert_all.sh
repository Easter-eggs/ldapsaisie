#!/bin/bash

cd $(realpath $(dirname $0))

rm -f ../../src/includes/resources/supann/populations.php
[ -f supann_population.csv.bz2 ] && bzip2 -d supann_population.csv.bz2
php convert_supann_population_from_csv.php supann_population.csv ../../src/includes/resources/supann/populations.php
bzip2 supann_population.csv

[ -f BCN_n_diplome_sise.csv.bz2 ] && bzip2 -d BCN_n_diplome_sise.csv.bz2
rm -f ../src/includes/resources/supann/BCN_diplome_sise.php
php convert_supann_etuDiplome_from_csv.php BCN_n_diplome_sise.csv ../../src/includes/resources/supann/BCN_diplome_sise.php
bzip2 BCN_n_diplome_sise.csv

[ -f BCN_n_type_diplome_sise.csv.bz2 ] && bzip2 -d BCN_n_type_diplome_sise.csv.bz2
rm -f ../../src/includes/resources/supann/BCN_type_diplome_sise.php
php convert_supann_etuTypeDiplome_from_csv.php BCN_n_type_diplome_sise.csv ../../src/includes/resources/supann/BCN_type_diplome_sise.php
bzip2 BCN_n_type_diplome_sise.csv

[ -f supann_role_generique.csv.bz2 ] && bzip2 -d supann_role_generique.csv.bz2
rm -f ../../src/includes/resources/supann/role_generique.php
php convert_supann_role_generique_from_csv.php supann_role_generique.csv ../../src/includes/resources/supann/role_generique.php
bzip2 supann_role_generique.csv

[ -f supann_type_entite.csv.bz2 ] && bzip2 -d supann_type_entite.csv.bz2
rm -f ../../src/includes/resources/supann/type_entite.php
php convert_supann_type_entite_from_csv.php supann_type_entite.csv ../../src/includes/resources/supann/type_entite.php
bzip2 supann_type_entite.csv

[ -f BCN_n_corps.csv.bz2 ] && bzip2 -d BCN_n_corps.csv.bz2
rm -f ../../src/includes/resources/supann/BCN_emp_corps.php
php convert_supann_empCorps_from_csv.php BCN_n_corps.csv ../../src/includes/resources/supann/BCN_emp_corps.php
bzip2 BCN_n_corps.csv

[ -f BCN_n_regime_inscription.csv.bz2 ] && bzip2 -d BCN_n_regime_inscription.csv.bz2
rm -f ../../src/includes/resources/supann/BCN_regime_inscription.php
php convert_supann_etuRegimeInscription_from_csv.php BCN_n_regime_inscription.csv ../../src/includes/resources/supann/BCN_regime_inscription.php
bzip2 BCN_n_regime_inscription.csv

[ -f BCN_n_secteur_disciplinaire_sise.csv.bz2 ] && bzip2 -d BCN_n_secteur_disciplinaire_sise.csv.bz2
rm -f ../../src/includes/resources/supann/BCN_secteur_disciplinaire.php
php convert_etuSecteurDisciplinaire_from_csv.php BCN_n_secteur_disciplinaire_sise.csv ../../src/includes/resources/supann/BCN_secteur_disciplinaire.php
bzip2 BCN_n_secteur_disciplinaire_sise.csv
