DOCBOOK_FILE=../../LdapSaisie.docbook
DBTOEPUB=dbtoepub
EPUB_FILE=LdapSaisie.epub

all:		epub

epub:		$(EPUB_FILE)

LdapSaisie.epub:	$(DOCBOOK_FILE)
			$(DBTOEPUB) -o $(EPUB_FILE) "$(DOCBOOK_FILE)"

clean:
				rm -f $(EPUB_FILE)
