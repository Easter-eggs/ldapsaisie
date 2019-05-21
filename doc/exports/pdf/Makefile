DOCBOOK_FILE=../../LdapSaisie.docbook
JW=jw
BASE_FILENAME=LdapSaisie
PDF_FILE=$(BASE_FILENAME).pdf

all:		pdf

pdf:		$(PDF_FILE)

LdapSaisie.pdf:		$(DOCBOOK_FILE)
	SP_ENCODING=XML $(JW) -f docbook -b pdf "$(DOCBOOK_FILE)"

clean:
	rm -f $(BASE_FILENAME).*
