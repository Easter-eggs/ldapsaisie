#!/bin/bash

# Detect
SRC=$( realpath $( dirname $0 )/../ )

# Clean php file in tmp directory
[ -d "$SRC/tmp" ] && rm -fr "$SRC/tmp/*.php"

# Extract messages from LdapSaisie PHP files using xgettext
xgettext	--from-code utf-8 \
		-o "$SRC/lang/ldapsaisie-main.pot" \
		--omit-header \
		--copyright-holder="Easter-eggs" \
		--keyword="__" \
		--keyword="___" \
		$( find "$SRC" -name "*.php" )

# Extract other messages from LdapSaisie templates files
$SRC/bin/ldapsaisie.php generate_lang_file \
		-o "$SRC/lang/ldapsaisie-templates.pot" \
		-f pot \
		--only templates

# Merge previous results in ldapsaisie.pot file
msgcat $SRC/lang/ldapsaisie-main.pot $SRC/lang/ldapsaisie-templates.pot -o $SRC/lang/ldapsaisie.pot
