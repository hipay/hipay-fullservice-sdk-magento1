#!/usr/bin/env bash

if [ "$1" = 'php5.6' ]; then
  PHP_VERSION=''
else
  PHP_VERSION=-$1
fi

junit-viewer --results=bin/tests/result.xml --save=bin/tests/report$PHP_VERSION.html --minify=false --contracted
if [ -d bin/tests/errors/ ]; then mkdir $CIRCLE_ARTIFACTS/screenshots/$1; cp bin/tests/errors/* $CIRCLE_ARTIFACTS/screenshots/$1; rm -rf bin/tests/errors/; fi
