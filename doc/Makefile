DOCBOOK_FILE=LdapSaisie.docbook

all:    validate export

# Validation
validate:
				xmllint --valid --noout $(DOCBOOK_FILE) 

export:
				cd exports; make all

html:
				cd exports/html; make all-in-one/LdapSaisie.html

clean:
				cd exports; make clean
