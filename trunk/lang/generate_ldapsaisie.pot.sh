#!/bin/bash

cd ../

rm -fr tmp/*

xgettext --from-code utf-8 -o lang/ldapsaisie.pot $( find -name "*.php" )
