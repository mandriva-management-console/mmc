#!/bin/bash

rm -f mds-*.tar.gz mds-*.tar.gz.md5
git clean -fdx && ./autogen.sh && ./configure --sysconfdir=/etc --localstatedir=/var --disable-conf && make distcheck
if [ $? -eq 0 ]; then
    for tarball in mds-*.tar.gz; do
        md5sum $tarball > $tarball.md5
    done
fi
