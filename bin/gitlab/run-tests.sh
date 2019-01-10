#!/bin/bash
set -e

docker-compose -p $1 -f $2 up -d
sleep 250
docker-compose -p $1 -f $2 logs
curl --retry 3 --retry-delay 20 -v http://${DOCKER_SERVICE}-${CI_JOB_ID}-web
bash bin/tests/casper_run_gitlab.sh $3 http://${DOCKER_SERVICE}-${CI_JOB_ID}-web