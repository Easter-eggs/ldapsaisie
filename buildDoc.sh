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

cd $ROOT_DIR/doc

make clean >> $LOG_FILE 2>&1
make >> $LOG_FILE 2>&1 &

PID=$!

while [ -d /proc/$PID ]
do
	echo -n .
	sleep 1
done
echo done.

kill -9 $PID 2> /dev/null
