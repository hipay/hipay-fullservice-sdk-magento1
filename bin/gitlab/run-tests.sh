#!/bin/bash
set -e

docker-compose -p $1 -f $2 up -d
sleep 250
docker-compose -p $1 -f $2 logs
curl --retry 3 --retry-delay 20 -v http://${DOCKER_SERVICE}-${CI_JOB_ID}-web
bash bin/tests/casper_run_gitlab.sh $3 http://${DOCKER_SERVICE}-${CI_JOB_ID}-web
curl -s http://${DOCKER_SERVICE}-${CI_JOB_ID}-web/var/log/system.log | grep -i hipay
if [[ $? -eq 0 ]]
then
    exit 1;
else
    exit 0;
fi;
