#!/bin/bash
set -e

docker-compose -p $1 -f $2 up -d
sleep 250
docker-compose -p $1 -f $2 logs
curl --retry 3 --retry-delay 20 -v http://${DOCKER_SERVICE}-${CI_JOB_ID}-web
cd bin/tests/tests-cypress
yarn install
bash /tools/run-cypress-test.sh -f $TESTS_FOLDERS_1 --config baseUrl=http://${DOCKER_SERVICE}-${CI_JOB_ID}-web/ --env $CYPRESS_ENV
