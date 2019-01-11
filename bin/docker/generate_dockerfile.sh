#!/usr/bin/env bash

# Generate DockerFile
cp -f bin/docker/images/php7-1/Dockerfile bin/docker/images/php5-6/Dockerfile
cp -f bin/docker/images/php7-1/Dockerfile bin/docker/images/php7-2/Dockerfile

# Change the clause FROM
sed -i -e "s/FROM php:7.1-apache/FROM php:5.6-apache/" bin/docker/images/php5-6/Dockerfile
sed -i -e "s/PHP_VERSION=7.1/PHP_VERSION=5.6/" bin/docker/images/php5-6/Dockerfile

sed -i -e "s/FROM php:7.1-apache/FROM php:7.2-apache/" bin/docker/images/php7-2/Dockerfile
sed -i -e "s/PHP_VERSION=7.1/PHP_VERSION=7.2/" bin/docker/images/php7-2/Dockerfile

# Generate docker_compose
cp -f docker-compose.test.yml docker-compose.test-php5-6.yml
cp -f docker-compose.test.yml docker-compose.test-php7-2.yml
cp -f docker-compose.test.yml docker-compose.test-magento18.yml

sed -i -e "s/php7-1/php5-6/" docker-compose.test-php5-6.yml
sed -i -e "s/php7-1/php7-2/" docker-compose.test-php7-2.yml