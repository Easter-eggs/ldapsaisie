#!/bin/bash

# Detect
PUBLIC_HTML=$( realpath $( dirname $0 )/../ )

# Clean php file in tmp directory
[ -d "$PUBLIC_HTML/tmp" ] && rm -fr "$PUBLIC_HTML/tmp/*.php"

# Extract messages from LdapSaisie PHP files using xgettext
xgettext	--from-code utf-8 \
		-o "$PUBLIC_HTML/lang/ldapsaisie-main.pot" \
		--omit-header \
		--copyright-holder="Easter-eggs" \
		--keyword="__" \
		$( find "$PUBLIC_HTML" -name "*.php" )

# Extract other messages from LdapSaisie templates files
$PUBLIC_HTML/lang/generate_lang_file.php	-o "$PUBLIC_HTML/lang/ldapsaisie-templates.pot" \
						-f pot \
						--only templates

# Merge previous results in ldapsaisie.pot file
msgcat $PUBLIC_HTML/lang/ldapsaisie-main.pot $PUBLIC_HTML/lang/ldapsaisie-templates.pot -o $PUBLIC_HTML/lang/ldapsaisie.pot
