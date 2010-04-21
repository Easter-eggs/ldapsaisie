#!/bin/sh

ROOT_DIR=$( cd `dirname $0`; pwd )
LOCAL_CFG_DIR=$ROOT_DIR/config.local

# Import config
if [ ! -f $LOCAL_CFG_DIR/local.sh ]
then
    echo "Error : You don't have create your own local.sh file in config.local directory. You could rely on the local.sh.example file to create your version."
    exit 1
fi

source $LOCAL_CFG_DIR/local.sh

if [ ! -n "$EXPORT_DOC_DIR" ]
then
    echo "The EXPORT_DOC_DIR variable is not define. Export doc is disabled."
    exit 0
fi

if [ ! -d "$EXPORT_DOC_DIR" ]
then
    echo "Error : Export directory $EXPORT_DOC_DIR does not exist !"
    exit 2
fi

DOC_DIR=$ROOT_DIR/doc
TMP_DIR=`mktemp -d`
ERROR=0

echo "-> Export documentation in $EXPORT_DOC_DIR :"

# PDF
if [ -n "$PDF" ]
then
    echo -en "\t- PDF : "
    # PDF
    cp -f $DOC_DIR/exports/pdf/LdapSaisie.pdf $EXPORT_DOC_DIR/LdapSaisie.pdf
    if [ $? -ne 0 ]
    then
        echo -e "\n-> Error"
        ERROR=1
    else
        echo Ok
    fi
fi


# ALL-IN-ONE
if [ -n "$ALL_IN_ONE" ]
then
    echo -en "\t- All-In-One : "
    rm -fr $TMP_DIR/$ALL_IN_ONE
    mkdir $TMP_DIR/$ALL_IN_ONE

    cp $DOC_DIR/exports/html/all-in-one/LdapSaisie.html $TMP_DIR/$ALL_IN_ONE/
    sed -i 's/\.\.\/\.\.\/\.\.\///g' $TMP_DIR/$ALL_IN_ONE/LdapSaisie.html

    # IMAGES
    cp -fr $IMAGES $TMP_DIR/$ALL_IN_ONE/images

    mkdir $TMP_DIR/$ALL_IN_ONE/styles
    cp $CSS $TMP_DIR/$ALL_IN_ONE/styles/

    echo "done. Build archive and move it later ..."
fi


# ONLINE
if [ -n "$ONLINE" ]
then
    echo -en "\t- On-line : "
    rm -fr $TMP_DIR/$ONLINE
    mkdir $TMP_DIR/$ONLINE
    
    cp -fr $DOC_DIR/exports/html/online/*.html $TMP_DIR/$ONLINE
    sed -i 's/\.\.\/\.\.\/\.\.\///g' $TMP_DIR/$ONLINE/*

    # IMAGES
    cp -fr $IMAGES $TMP_DIR/$ONLINE/images

    mkdir $TMP_DIR/$ONLINE/styles
    cp $CSS $TMP_DIR/$ONLINE/styles/

    echo "done. Build archive and move it later ..."
fi


# DOCBOOK
if [ -n "$DOCBOOK" ]
then
    echo -en "\t- Docbook : "

    rm -fr $TMP_DIR/$DOCBOOK
    mkdir $TMP_DIR/$DOCBOOK
    
    cd $DOC_DIR
    for i in `find -type d|grep -v 'export'`
    do
        mkdir -p $TMP_DIR/$DOCBOOK/$i
    done
    
    for i in `find -type f|egrep -v '(Makefile|^./export)'`
    do
        cp $i $TMP_DIR/$DOCBOOK/$i
    done
    
    echo "done. Build archive and move it later ..."
fi

echo "-> Build archives and move all in export directory :"
cd $TMP_DIR/
for i in $ALL_IN_ONE $ONLINE $DOCBOOK
do
    echo -e "\t$i : "
    echo -en "\t\t+ Archive : "
    tar -cjf LdapSaisie--Doc--$i.tar.bz2 $i && mv LdapSaisie--Doc--$i.tar.bz2 $EXPORT_DOC_DIR/
    if [ $? -eq 0 ]
    then
        echo Ok
    else
        echo -e "\n-> Error"
        ERROR=1
    fi

    echo -en "\t\t+ Web dir : "
    [ ! -d "$EXPORT_DOC_DIR/$i" ] && echo "you must create export $i directory manualy before run this script. (path : $EXPORT_DOC_DIR/$i)" && continue
    rm -fr $EXPORT_DOC_DIR/$i/* && cp -fr $i/* $EXPORT_DOC_DIR/$i/ && rm -fr $i
    if [ $? -eq 0 ]
    then
        echo Ok
    else
        echo -e "\n-> Error"
        ERROR=1
    fi
done

if [ -n "$LAST_UPDATE_FILE" ]
then
    echo -n "-> Create last-update file : "
    echo "Last update :" > $LAST_UPDATE_FILE
    date >> $LAST_UPDATE_FILE
    cd $ROOT_DIR
    git log|head -n 1 >> $LAST_UPDATE_FILE
    echo >> $LAST_UPDATE_FILE
    echo done.
fi

rm -fr $TMP_DIR

exit $ERROR
