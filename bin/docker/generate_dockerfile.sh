#!/usr/bin/env bash

# Generate DockerFile
cp -f bin/docker/php5-6/Dockerfile bin/docker/php5-3/Dockerfile
cp -f bin/docker/php5-6/Dockerfile bin/docker/php5-4/Dockerfile
cp -f bin/docker/php5-6/Dockerfile bin/docker/php7-0/Dockerfile

# Change the clause FROM
sed -i -e "s/FROM php:5.6-apache/FROM php:5.4-apache/" bin/docker/php5-4/Dockerfile
sed -i -e "s/PHP_VERSION=5.6/PHP_VERSION=5.4/" bin/docker/php5-4/Dockerfile

sed -i -e "s/FROM php:5.6-apache/FROM php:7.0-apache/"  bin/docker/php7-0/Dockerfile
sed -i -e "s/PHP_VERSION=5.6/PHP_VERSION=7.0/" bin/docker/php7-0/Dockerfile

# Change the clause FROM
sed -i -e "s/FROM php:5.6-apache/php5.3.29-apache/" bin/docker/php5-3/Dockerfile
sed -i -e "s/PHP_VERSION=5.6/PHP_VERSION=5.3/" bin/docker/php5-3/Dockerfile

# Generate docker_compose
cp -f docker-compose.stage.yml docker-compose.stage-php5-4.yml
cp -f docker-compose.stage.yml docker-compose.stage-php7-0.yml
cp -f docker-compose.stage.yml docker-compose.stage-php5-3.yml

sed -i -e "s/php5-6/php5-4/" docker-compose.stage-php5-4.yml
sed -i -e "s/php5-6/php7-0/" docker-compose.stage-php7-0.yml
sed -i -e "s/php5-6/php5-3/" docker-compose.stage-php5-3.yml