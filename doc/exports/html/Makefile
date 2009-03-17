DOCBOOK_FILE=../../LdapSaisie.docbook
XSL_FILE=../../styles/LS.xsl
XSL_MULTI_FILE=../../styles/LS-multi.xsl
XSL_HELP_FILE=../../styles/LS-help.xsl

XSLTPROC=xsltproc

all:		html

html:		all-in-one/LdapSaisie.html online/index.html help/index.html

all-in-one/LdapSaisie.html:		$(DOCBOOK_FILE)
						$(XSLTPROC) --output all-in-one/LdapSaisie.html $(XSL_FILE) $(DOCBOOK_FILE)

online/index.html:		$(DOCBOOK_FILE)
						cd online; $(XSLTPROC) -stringparam chunker.output.indent yes \
						../$(XSL_MULTI_FILE)	../$(DOCBOOK_FILE)

help/index.html:		$(DOCBOOK_FILE)
						cd help; $(XSLTPROC) ../$(XSL_HELP_FILE)	../$(DOCBOOK_FILE)

clean:
				rm -f all-in-one/LdapSaisie.html online/* help/*
