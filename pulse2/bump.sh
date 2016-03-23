#!/bin/bash

version=$1

if [ ! -z $1 ]; then

    sed -i "s/^AC_INIT.*$/AC_INIT\(project, [$1], [http:\/\/www.siveo.org]\)/" configure.ac

    git diff
    git commit -a -m "pulse: bump version to $1"
    git tag -s pulse_$1 -m "pulse_$1"
    #git push
    #git push --tags

else

    echo Usage: ./bump.sh version

fi

