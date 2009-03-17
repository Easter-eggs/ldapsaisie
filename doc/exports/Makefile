DOCBOOK_FILE=../LdapSaisie.docbook

all:    html pdf

# HTML
html:		html/LdapSaisie.html

html/LdapSaisie.html:
				cd html; make html

clean_html:
				cd html; make clean

# PDF
pdf:		pdf/LdapSaisie.pdf

pdf/LdapSaisie.pdf:
				cd pdf; make pdf

clean_pdf:
				cd pdf; make clean

clean:  clean_html clean_pdf
