#!/bin/bash

#=============================================================================
#  Use this script build hipays images and run our containers
#
#  WARNING : Put your credentials in hipay.env
#==============================================================================

if [ "$1" = '' ] || [ "$1" = '--help' ];then
    printf "\n ==================================================== "
    printf "\n                     HIPAY'S HELPER                 "
    printf "\n ==================================================== "
    printf "\n      - init      : Build images and run containers (Delete existing volumes)        "
    printf "\n      - restart   : Run all containers if they already exist"
fi

if [ "$1" = 'init' ];then
    if [ -f ./bin/conf/development/hipay.env ];then
        docker-compose stop
        docker-compose rm -fv
        sudo rm -Rf data/ log/ web/
        docker-compose -f docker-compose.yml -f docker-compose.dev.yml build --no-cache
        docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
        docker-compose logs -f
    else
        echo "Put your credentials in auth.env and hipay.env before start update the docker-compose.dev to link this files"
    fi
fi

if [ "$1" = 'restart' ];then
    docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
fi

if [ "$1" = 'logs' ];then
    docker-compose logs -f
fi