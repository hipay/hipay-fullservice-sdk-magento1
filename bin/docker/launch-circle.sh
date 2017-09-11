#!/usr/bin/env bash

if [ "$1" = '' ]; then
  PHP_VERSION=''
else
  PHP_VERSION=-$1
fi

docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml build --no-cache
docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml up -d
