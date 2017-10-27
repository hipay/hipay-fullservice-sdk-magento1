#!/usr/bin/env bash

MAGENTO_VERSION=1.9.2.3
SAMPLE_DATA_VERSION=1.9

################################
# PHP VERSION
################################
if [ "$1" = 'php5.6' ]; then
  PHP_VERSION=''
else
  PHP_VERSION=-$1
fi

sed -i -e "s/{SAMPLE_DATA_VERSION\}/$SAMPLE_DATA_VERSION/" docker-compose.stage$PHP_VERSION.yml
sed -i -e "s/{MAGENTO_VERSION\}/$MAGENTO_VERSION/" docker-compose.stage$PHP_VERSION.yml

################################
### MAGENTO VERSION
################################
if [ "$2" != '' ]; then
  MAGENTO_VERSION=$2
fi

if [ "$3" != '' ]; then
    SAMPLE_DATA_VERSION=$3

    echo "Build and start Magento $MAGENTO_VERSION and PHP : $PHP_VERSION "
    sed -i -e "s/{SAMPLE_DATA_VERSION\}/$SAMPLE_DATA_VERSION/" docker-compose.stage-magento18.yml
    sed -i -e "s/{MAGENTO_VERSION\}/$MAGENTO_VERSION/" docker-compose.stage-magento18.yml

    cat docker-compose.stage-magento18.yml

    docker-compose -f docker-compose.yml -f docker-compose.stage-magento18.yml build --no-cache
    docker-compose -f docker-compose.yml -f docker-compose.stage-magento18.yml up -d
else
  echo "Build and start Magnto latest and PHP : $PHP_VERSION "
  docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml build --no-cache
  docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml up -d
fi




