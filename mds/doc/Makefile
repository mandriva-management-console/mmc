html:
	mkdir build
	cp -r MDS-EP MMC build
	-find build -name .svn -exec rm -fr {} \;
	for f in `find build -name \*xml`; do \
		echo $$f; \
		xmlto -o `dirname $$f` -m param.html.xsl xhtml-nochunks $$f; \
		rm -f $$f; \
		tidy -utf8 $${f%.xml}.html > $${f%.xml}.prehtml; \
		./mandrify $${f%.xml}.prehtml $${f%.xml}.html; \
		rm -f $${f%.xml}.prehtml; \
	done

tar: build
	(cd build; tar czf ../lds-doc.tar.gz MDS-EP MMC)

clean:
	rm -fr build
