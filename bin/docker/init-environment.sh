#!/usr/bin/env bash

VERSION=1.9.2.3
VERSION_SAMPLE_DATA=1.9

################################
# PHP VERSION
################################
if [ "$1" = 'php5.6' ]; then
  PHP_VERSION=''
else
  PHP_VERSION=-$1
fi

################################
### MAGENTO VERSION
################################
if [ "$2" != '' ]; then
  VERSION=$2
fi

if [ "$3" != '' ]; then
VERSION_SAMPLE_DATA=$3
fi

sed -i -e "s/{SAMPLE_DATA_VERSION\}/$VERSION_SAMPLE_DATA/" ./bin/conf/stage/mage.env.sample
sed -i -e "s/{MAGENTO_VERSION\}/$VERSION/" ./bin/conf/stage/mage.env.sample
docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml build --no-cache

sed -i -e "s/$VERSION_SAMPLE_DATA/{SAMPLE_DATA_VERSION\}/" ./bin/conf/stage/mage.env.sample
sed -i -e "s/$VERSION/{MAGENTO_VERSION\}/" ./bin/conf/stage/mage.env.sample

docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml up -d
