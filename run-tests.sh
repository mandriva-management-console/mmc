#!/bin/bash

# Git pre-commit checks
# To use this test script with git
# ln -s ../../run-tests.sh .git/hooks/pre-commit

# get the list of the files to checkout
files=$(git diff-index --name-only --cached HEAD)
error="0"
echo $files |grep '\.py'|xargs pyflakes || error=1
echo $files |grep '\.sh'|xargs sh -n || error=1
echo $files |while read file; do
  if [ ! -s $file ]; then
    error=1
    echo "File $file is empty, please check it"
  fi
done

if [ "$error" -eq "1" ]; then
   exit 1
fi
exit 0
