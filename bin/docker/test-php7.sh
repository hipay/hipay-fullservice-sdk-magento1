#!/usr/bin/env bash
docker-compose stop
docker-compose rm -fv
docker-compose -f docker-compose.yml -f docker-compose.stage-php7-0.yml build --no-cache
docker-compose -f docker-compose.yml -f docker-compose.stage-php7-0.yml up -d
sleep 60
curl --retry 30 --retry-delay 3 -v $MAGENTO_URL:$PORT_WEB
sh bin/tests/casper_run_circle.sh
junit-viewer --results=bin/tests/result.xml --save=bin/tests/report-php7-0.html --minify=false --contracted
if [ -d bin/tests/errors/ ]; then mkdir $CIRCLE_ARTIFACTS/screenshots/php7-0; cp bin/tests/errors/* $CIRCLE_ARTIFACTS/screenshots/php7-0; fi