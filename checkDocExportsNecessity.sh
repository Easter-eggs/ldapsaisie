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

if [ ! -n "$LAST_UPDATE_FILE" ]
then
    echo "Error : The LAST_UPDATE_FILE is necessary for update detection !"
    exit 3
fi

cd $ROOT_DIR

CURRENT=`grep ^commit $LAST_UPDATE_FILE | cut -d ' ' -f 2`
if [ "`git diff $CURRENT -- doc`" != "" ]
then
    echo "Export documentation is necessary"
    echo "================================="
    echo "Current doc exports was generated from the commit $CURRENT."
    echo "Changes have been made since."
fi
