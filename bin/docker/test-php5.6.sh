#!/usr/bin/env bash
docker-compose -f docker-compose.yml -f docker-compose.stage.yml build --no-cache
docker-compose -f docker-compose.yml -f docker-compose.stage.yml up -d
sleep 60
curl --retry 30 --retry-delay 3 -v $MAGENTO_URL:$PORT_WEB
curl --retry 30 --retry-delay 3 -v $URL_MAILCATCHER
sh bin/tests/casper_run_circle.sh
junit-viewer --results=bin/tests/result.xml --save=bin/tests/report-php5-6.html --minify=false --contracted
if [ -d bin/tests/errors/ ]; then mkdir $CIRCLE_ARTIFACTS/screenshots/php5-6; cp bin/tests/errors/* $CIRCLE_ARTIFACTS/screenshots/php5-6; rm -rf bin/tests/errors/; fi
