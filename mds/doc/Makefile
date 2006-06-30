html:
	mkdir build
	cp -r LDS-EP LMC build
	-find build -name .svn -exec rm -fr {} \;
	for f in `find build -name \*xml`; do \
		echo $$f; \
		xmlto -o `dirname $$f` xhtml-nochunks $$f; \
		rm -f $$f; \
		tidy -utf8 $${f%.xml}.html > $${f%.xml}.prehtml; \
		./linboxify $${f%.xml}.prehtml $${f%.xml}.html; \
		rm -f $${f%.xml}.prehtml; \
	done

tar: build
	(cd build; tar czf ../lds-doc.tar.gz LDS-EP LMC)

clean:
	rm -fr build
