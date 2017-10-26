#!/usr/bin/env bash

################################
# PHP VERSION
################################
if [ "$1" = 'php5-6' ]; then
  PHP_VERSION=''
else
  PHP_VERSION=-$1
fi

################################
### MAGENTO VERSION
################################
if [ "$2" != '' ]; then
  sed -i -e "s/{MAGENTO_VERSION\}/$2/" ./bin/conf/stage/mage.env.sample
else
  sed -i -e "s/{MAGENTO_VERSION\}/1.9.2.3/" ./bin/conf/stage/mage.env.sample
fi

if [ "$3" != '' ]; then
  sed -i -e "s/{SAMPLE_DATA_VERSION\}/$3/" ./bin/conf/stage/mage.env.sample
else
  sed -i -e "s/{SAMPLE_DATA_VERSION\}/1.9/" ./bin/conf/stage/mage.env.sample
fi

docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml build --no-cache
docker-compose -f docker-compose.yml -f docker-compose.stage$PHP_VERSION.yml up -d
