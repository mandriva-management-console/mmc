#!/bin/sh

# get the list of the files to checkout
files=`git diff-index --name-only --cached HEAD`
echo $files |grep py
exit 0
