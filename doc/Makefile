DOCBOOK_FILE=LdapSaisie.docbook

all:    validate export

# Validation
validate:
				xmllint --valid --noout $(DOCBOOK_FILE) 

export:
				cd exports; make all

clean:
				cd exports; make clean
