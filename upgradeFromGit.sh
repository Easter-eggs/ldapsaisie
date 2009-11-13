#!/bin/sh


ROOT_DIR=$( cd `dirname $0`; pwd )
LOCAL_SAV_DIR="$ROOT_DIR/config.local"

# Import config
if [ ! -f $LOCAL_SAV_DIR/local.sh ]
then
    echo "Error : You don't have create your own local.sh file in config.local directory. You could rely on the local.sh.example file to create your version."
    exit 1
fi

source $LOCAL_SAV_DIR/local.sh

function msg() {
    echo $2 "$1" | tee -a "$LOG_FILE"
}

cd $ROOT_DIR

msg "Clean git repos : "
for i in $LOCAL_FILES
do
	msg "\t-> $i : " -en
	if [ -L $i ]
	then
        msg "\n\t\t-> Delete file : " -en
		rm -fr $i >> $LOG_FILE 2>&1
		if [ $? -gt 0 ]
		then
			msg "Error"
			exit 1
		else
			msg "Ok"
		fi
		if [ -f $i.sav ]
		then
    		msg "\t\t-> Restore orignal file : " -en
			mv $i.sav $i >> $LOG_FILE 2>&1
			if [ $? -gt 0 ]
	                then
        	                msg "Error"
				exit 1
	                else
        	                msg "Ok"
                	fi
		fi
	else
		msg "file does not exist, pass..."
	fi
done

if [ $DO_DOC -eq 1 ]
then
	msg "-> Clean the doc : " -en
	cd $ROOT_DIR/doc >> $LOG_FILE && make clean >> $LOG_FILE && cd - >> $LOG_FILE
	if [ $? -gt 0 ]
	then
	        msg "Error"
	        exit 1
	else
        	msg "Ok"
	fi
fi

if [ "$THEME" != "" ]
then
	msg "\t\t-> Remove theme : " -en
	rm -f $ROOT_DIR/public_html/templates/$THEME $ROOT_DIR/public_html/images/$THEME $ROOT_DIR/public_html/css/$THEME >> $LOG_FILE 2>&1
	if [ $? -gt 0 ]
	then
  	msg "Error"
    exit 1
  else
  	msg "Ok"
  fi
fi

msg "Verification of git repos state : "
git status >> $LOG_FILE 2>&1
if [ "$ETAT" != "" ]
then
	msg "\n\t-> [Error] Some changes have been made to source code since the last update." -e
	exit 1
fi
msg "\t->[OK]" -e

msg "Upgrade git repos : "
RES_GIT=`git pull`
RES=$?
msg "$RES_GIT" -e
if [ $RES -gt 0 ]
then
	msg "\t-> [Error] Problem during git repos pull." -e
	exit 1
fi
msg "\t-> [OK]" -e

msg "Install local files : "
for i in $LOCAL_FILES
do
	msg "\t-> $i : " -ne
	SRC="$LOCAL_SAV_DIR/`basename $i`"
	if [ -f $SRC ]
	then
		msg
        if [ -f $ROOT_DIR/$i ]
		then
			msg "\t\t-> Backup original file : " -en
			mv $ROOT_DIR/$i $ROOT_DIR/$i.sav >> $LOG_FILE 2>&1
			if [ $? -gt 0 ]
			then
				msg "Error"
				exit 1
			fi
			msg "Ok"

			msg "\t\t-> Check possible change of the original file since last upgrade : " -en
			if [ -f $SRC.orig ]
			then
				DIFF=`diff $ROOT_DIR/$i.sav $SRC.orig`
				if [ "$DIFF" != "" ]
				then	
				    msg "\n$DIFF\n\t\t\t-> Caution : This file changed. Do you want edit this file now ? [y/N] " -en
					read a
					echo "Reponse : $a"  >> $LOG_FILE
					if [ "$a" == "y" -o "$a" == "Y" ]
					then
						vi -d $SRC $ROOT_DIR/$i.sav
					fi
				else
					msg "No change"
				fi
			else
				msg "Original backup file does not exist. Pass ..."
			fi
			msg "\t\t-> Backup file for next upgrade : " -en
			cp -f $ROOT_DIR/$i.sav $SRC.orig >> $LOG_FILE 2>&1
			if [ $? -gt 0 ]
			then
				msg "Error"
                exit 1
			fi
			msg "Ok"
		fi
		msg "\t\t-> Install local file : " -en
		ln -s $SRC $ROOT_DIR/$i >> $LOG_FILE 2>&1
		if [ $? -gt 0 ]
		then
			msg "Error"
			exit 1
		fi
		msg "Ok"
	else
		msg "file does not exist. Pass..."
	fi
done
if [ "$THEME" != "" ]
then
	msg "-> Install theme : " -en
	ln -s $LOCAL_SAV_DIR/theme/templates $ROOT_DIR/public_html/templates/$THEME >> $LOG_FILE 2>&1
	ln -s $LOCAL_SAV_DIR/theme/images $ROOT_DIR/public_html/images/$THEME >> $LOG_FILE 2>&1
	ln -s $LOCAL_SAV_DIR/theme/css $ROOT_DIR/public_html/css/$THEME >> $LOG_FILE 2>&1
	msg "Ok"
fi

if [ $DO_DOC -eq 1 ]
then
	msg "-> Do you want export the documentation (y/N) ? " -en
	read a
	if [ "$a" == "y" -o "$a" == "Y" ]
	then
        msg "-> Export the doc : " -en
		cd $ROOT_DIR/doc >> $LOG_FILE 2>&1 && make >> $LOG_FILE 2>&1 && cd - >> $LOG_FILE 2>&1
		if [ $? -gt 0 ]
		then
		        msg "Error"
		        exit 1
		else
	        	msg "Ok"
		fi
	fi
fi
