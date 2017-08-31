#!/usr/bin/env bash

if [ "$1" = '' ]; then
  PHP_VERSION=''
else
  PHP_VERSION=-$1
fi

echo "docker-compose.stage$PHP_VERSION.yml"
ls
docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml build --no-cache
docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml up -d
sleep 60
curl --retry 30 --retry-delay 3 -v $MAGENTO_URL:$PORT_WEB
curl --retry 30 --retry-delay 3 -v $URL_MAILCATCHER
sh bin/tests/casper_run_circle.sh
junit-viewer --results=bin/tests/result.xml --save=bin/tests/report$PHP_VERSION.html --minify=false --contracted
if [ -d bin/tests/errors/ ]; then mkdir $CIRCLE_ARTIFACTS/screenshots/$1; cp bin/tests/errors/* $CIRCLE_ARTIFACTS/screenshots/$1; rm -rf bin/tests/errors/; fi