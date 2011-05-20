#!/bin/bash

ROOT_DIR=$( cd `dirname $0`; pwd )

cd $ROOT_DIR

git fetch -q

CURRENT=`git show HEAD|head -n1`
LASTEST=`git show FETCH_HEAD|head -n1`

[ "$1" = "-d" ] && echo -e "Current : $CURRENT\nLastest : $LASTEST"

if [ "$CURRENT" != "$LASTEST" ]
then
    echo "New update is available"
    echo "======================="
    echo "Current installation of LdapSaisie is from the $CURRENT."
    echo "Changes have been made since."
    echo
    echo "The lastest commit is : "
    echo 
    git show FETCH_HEAD|cat
    echo
    echo "** /!\\ You have to run the script upgradeFromGit.sh to upgrade your installation. /!\\ **"
fi
