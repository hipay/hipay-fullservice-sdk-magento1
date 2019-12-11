#!/usr/bin/env bash

################################
# PHP VERSION
################################
if [ "$1" = 'php7.1' ]; then
  PHP_VERSION=''
else
  PHP_VERSION=-$1
fi

sed -i -e "s/{SAMPLE_DATA_VERSION\}/$SAMPLE_DATA_VERSION/" docker-compose.test$PHP_VERSION.yml
sed -i -e "s/{MAGENTO_VERSION\}/$MAGENTO_VERSION/" docker-compose.test$PHP_VERSION.yml

################################
### MAGENTO VERSION
################################
if [ "$2" != '' ]; then
  MAGENTO_VERSION=$2
fi

if [ "$3" != '' ]; then
    SAMPLE_DATA_VERSION=$3

    echo "Build and start Magento $MAGENTO_VERSION and PHP : $PHP_VERSION "
    sed -i -e "s/{SAMPLE_DATA_VERSION\}/$SAMPLE_DATA_VERSION/" docker-compose.test-magento18.yml
    sed -i -e "s/{MAGENTO_VERSION\}/$MAGENTO_VERSION/" docker-compose.test-magento18.yml

    cat docker-compose.test-magento18.yml

    docker-compose -f docker-compose.test-magento18.yml build
    docker-compose -f docker-compose.test-magento18.yml up -d
    docker-compose -f docker-compose.test-magento18.yml push
else
  echo "Build and start Magento latest and PHP : $PHP_VERSION "
  docker-compose  -f docker-compose.test$PHP_VERSION.yml build 
  docker-compose  -f docker-compose.test$PHP_VERSION.yml push
fi




