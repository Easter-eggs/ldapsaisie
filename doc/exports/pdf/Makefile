DOCBOOK_FILE=../../LdapSaisie.docbook
JW=jw
PDF_FILE=LdapSaisie.pdf

all:		pdf

pdf:		LdapSaisie.pdf

LdapSaisie.pdf:		$(DOCBOOK_FILE)
						SP_ENCODING=XML $(JW) -f docbook -b pdf "$(DOCBOOK_FILE)"

clean:
				rm -f $(PDF_FILE)
