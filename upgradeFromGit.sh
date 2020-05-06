#!/bin/bash


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

function check_file_or_symlink() {
    [ -f "$1" ] && echo 0 && return 0
    if [ -L "$1" ]
    then
        [ -r "$1" ] && echo 0 && return 0
        rm -f "$1"
    fi
    echo 1 && return 1
}

cd $ROOT_DIR

msg "-> Store gettext MO files state : "
MO_STATE_BEFORE=$( find $ROOT_DIR/src/lang/ -type f -name '*.mo'|sort -u|xargs md5sum )
msg "done."

msg "-> Clean git repos : "
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

if [ $BUILD_DOC -eq 1 ]
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
	rm -f $ROOT_DIR/src/templates/$THEME $ROOT_DIR/src/images/$THEME $ROOT_DIR/src/css/$THEME >> $LOG_FILE 2>&1
	if [ $? -gt 0 ]
	then
  	msg "Error"
    exit 1
  else
  	msg "Ok"
  fi
fi

msg "\t\t-> Clean template cache : " -en
rm -f $ROOT_DIR/src/tmp/*.tpl.php
if [ $? -gt 0 ]
then
	msg "Error"
	exit 1
else
	msg "Ok"
fi

msg "-> Verification of git repos state : "
git status >> $LOG_FILE 2>&1
if [ "$ETAT" != "" ]
then
	msg "\n\t-> [Error] Some changes have been made to source code since the last update." -e
	exit 1
fi
msg "\t->[OK]" -e

msg "-> Upgrade git repos : "
RES_GIT=`git pull`
RES=$?
msg "$RES_GIT" -e
if [ $RES -gt 0 ]
then
	msg "\t-> [Error] Problem during git repos pull." -e
	exit 1
fi
msg "\t-> [OK]" -e

msg "-> Install local files : "
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
	msg "-> Install theme : "

	# TPL
	msg "\t- Template : " -e
	ln -s $LOCAL_SAV_DIR/theme/templates $ROOT_DIR/src/templates/$THEME >> $LOG_FILE 2>&1
	if [ -d $ROOT_DIR/src/templates/$THEME_TPL_REF ]
	then
		msg "\t\t-> Vérification de la présence des fichiers : " -e
		for i in $ROOT_DIR/src/templates/$THEME_TPL_REF/*
		do
			f=`basename $i`
			msg "\t\t\t- $f : " -en
			if [ `check_file_or_symlink "$ROOT_DIR/src/templates/$THEME/$f"` -eq 0 ]
			then
				msg "present."
			else
				ln -s $ROOT_DIR/src/templates/$THEME_TPL_REF/$f $ROOT_DIR/src/templates/$THEME/$f
				msg "link."
			fi
		done
	fi

	# IMG
	msg "\t- Images : " -e
	ln -s $LOCAL_SAV_DIR/theme/images $ROOT_DIR/src/images/$THEME >> $LOG_FILE 2>&1
	if [ -d $ROOT_DIR/src/images/$THEME_IMG_REF ]
	then
		msg "\t\t-> Vérification de la présence des fichiers : " -e
		for i in $ROOT_DIR/src/images/$THEME_IMG_REF/*
		do
			f=`basename $i`
			msg "\t\t\t- $f : " -en
			if [ `check_file_or_symlink "$ROOT_DIR/src/images/$THEME/$f"` -eq 0 ]
			then
				msg "present."
			else
				ln -s $ROOT_DIR/src/images/$THEME_IMG_REF/$f $ROOT_DIR/src/images/$THEME/$f
				msg "link."
			fi
		done
	fi

	# CSS
	msg "\t- CSS : " -e
	ln -s $LOCAL_SAV_DIR/theme/css $ROOT_DIR/src/css/$THEME >> $LOG_FILE 2>&1
	if [ -d $ROOT_DIR/src/css/$THEME_CSS_REF ]
	then
		msg "\t\t-> Vérification de la présence des fichiers : " -e
		for i in $ROOT_DIR/src/css/$THEME_CSS_REF/*
		do
			f=`basename $i`
			msg "\t\t\t- $f : " -en
			if [ `check_file_or_symlink "$ROOT_DIR/src/css/$THEME/$f"` -eq 0 ]
			then
				msg "present."
			else
				ln -s $ROOT_DIR/src/css/$THEME_CSS_REF/$f $ROOT_DIR/src/css/$THEME/$f
				msg "link."
			fi
		done
	fi
fi

msg "-> Check for gettext MO files changes : "
MO_STATE_AFTER=$( find $ROOT_DIR/src/lang/ -type f -name '*.mo'|sort -u|xargs md5sum )
if [ "$MO_STATE_AFTER" == "$MO_STATE_BEFORE" ]
then
	msg "No change detected."
elif [ -n "$WEBSERVER_RELOAD_CMD" ]
then
	msg "Changed detected : try to webserver to handle changes..."
	$WEBSERVER_RELOAD_CMD
	if [ $? -eq 0 ]
	then
		msg "done."
	else
		msg "ERROR"
	fi
else
	msg "Changed detected :\n\n/!\\ You have to force-reload your webserver to handle it ! /!\\\n\n"
fi

if [ $BUILD_DOC -eq 1 ]
then
	[ -n "$LAST_UPDATE_FILE" ] && [ "`$ROOT_DIR/checkDocExportsNecessity.sh`" == "" ] && echo "Export documentation is not necessary. Pass." && exit
	msg "-> Do you want build the documentation (y/N) ? " -en
	read a
	if [ "$a" == "y" -o "$a" == "Y" ]
	then
        	msg "-> Build the doc : " -en
		cd $ROOT_DIR/doc

		make clean >> $LOG_FILE 2>&1
		make >> $LOG_FILE 2>&1 &

		export P=$!

		trap exitwhell INT

		function exitwhell() {
			[ -n "$P" ] && kill -9 $P 2> /dev/null
			echo " -- INT -- "
			exit 1
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
	fi
fi
