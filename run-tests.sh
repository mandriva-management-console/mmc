#!/bin/bash

# Git pre-commit checks
# To use this test script with git
# ln -s ../../run-tests.sh .git/hooks/pre-commit

# get the list of the files to checkout

#git diff-index --name-only --cached HEAD
files="$(git diff-index --name-only --cached HEAD)"
error="0"

echo $files |grep '\.py'|xargs python3 -m pyflakes || error=1
echo $files |grep '\.sh'|xargs sh -n || error=1
for file in $files; do
  [ ! -s "$file" ] && error=1 && echo "File $file is empty, please check it"
done

exit $error
