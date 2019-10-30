#!/bin/bash
set -e

docker-compose -f $1 pull -q
docker-compose -f $1 up -d
until docker-compose -f $1 logs | grep -m 1 "DOCKER MAGENTO TO HIPAY test IS UP" ; do sleep 1 ; done
docker-compose -f $1 logs
curl --retry 3 --retry-delay 20 -v http://localhost:80
