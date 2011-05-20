#!/bin/bash

ROOT_DIR=$( cd `dirname $0`; pwd )
LOCAL_CFG_DIR=$ROOT_DIR/config.local

# Import config
if [ ! -f $LOCAL_CFG_DIR/local.sh ]
then
    echo "Error : You don't have create your own local.sh file in config.local directory. You could rely on the local.sh.example file to create your version."
    exit 1
fi

source $LOCAL_CFG_DIR/local.sh

cd $ROOT_DIR/doc

make clean >> $LOG_FILE 2>&1
make >> $LOG_FILE 2>&1 &

export P=$!


trap exitwhell INT

function exitwhell() {
	kill -9 $P 2> /dev/null
	echo " -- INT -- "
}

while [ -d /proc/$P ]
do
	echo -n .
	sleep 1
done
echo done.

if [ -n "$EXPORT_DOC_DIR" ]
then
    $ROOT_DIR/buildDocExports.sh
fi
