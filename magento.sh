#!/bin/bash

#=============================================================================
#  Use this script build hipays images and run our containers
#
#  WARNING : Put your credentials in hipay.env
#==============================================================================

BASE_URL="http://localhost:8095/"
URL_MAILCATCHER="http://localhost:1095/"
header="bin/tests/"
pathPreFile=${header}000*/*.js
pathDir=${header}001*

setBackendCredentials() {
    printf "\n"
    if [ "$LOGIN_BACKEND" = "" ] || [ "$PASS_BACKEND" = "" ]; then
        while [ "$LOGIN_BACKEND" = "" ]; do
            read -p "LOGIN_BACKEND variable is empty. Insert your BO TPP login here : " login
            LOGIN_BACKEND=$login
        done
        while [ "$PASS_BACKEND" = "" ]; do
            read -p "PASS_BACKEND variable is empty. Insert your BO TPP password here : " pass
            PASS_BACKEND=$pass
        done
        printf "\n"
    fi
}

if [ "$1" = '' ] || [ "$1" = '--help' ]; then
    echo " ==================================================== "
    echo "                     HIPAY'S HELPER                 "
    echo " ==================================================== "
    echo "      - init        : Build images and run containers (Delete existing volumes)"
    echo "      - restart     : Run all containers if they already exist"
    echo "      - logs        : Show all containers logs continually"
    echo "      - test        : Execute the tests battery"
    echo "      - test-engine : Launch advanced shell script for tests battery"
    echo "      - notif       : Simulate a notification to Magento server"
    echo ""
elif [ "$1" = 'init' ]; then
    if [ -f ./bin/conf/development/hipay.env ]; then
        docker-compose stop
        docker-compose rm -fv
        sudo rm -Rf data/ log/ web/
        docker-compose -f docker-compose.yml -f docker-compose.dev.yml build --no-cache
        docker-compose -f docker-compose.yml -f docker-compose.dev.yml up
    else
        echo "Put your credentials in auth.env and hipay.env before start update the docker-compose.dev to link this files"
    fi
elif [ "$1" = 'restart' ]; then
    docker-compose -f docker-compose.yml -f docker-compose.dev.yml up
elif [ "$1" = 'logs' ]; then
    docker-compose logs -f
elif [ "$1" = 'test' ]; then
    setBackendCredentials

    if [ "$(ls -A ~/.local/share/Ofi\ Labs/PhantomJS/)" ]; then
        rm -rf ~/.local/share/Ofi\ Labs/PhantomJS/*
        printf "Cache cleared !\n\n"
    else
        printf "Pas de cache à effacer !\n\n"
    fi

    casperjs test $pathPreFile ${pathDir}/[0-1]*/[0-9][0-9][0-9][0-9]-*.js --url=$BASE_URL --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any
elif [ "$1" = "test-engine" ]; then
    bash bin/tests/casper_debug.sh
elif [ "$1" = "notif" ]; then
    setBackendCredentials

    while [ "$order" = "" ]; do
        read -p "In order to simulate notification to Magento server, put here an order ID : " order
    done

    casperjs test $pathPreFile ${header}001*/1*/0101-*.js --url=$BASE_URL --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --ignore-ssl-errors=true --ssl-protocol=any --order=$order
else
    echo "Incorrect argument ! Please check the HiPay's Helper via the following command : 'sh magento.sh' or 'sh magento.sh --help'"
fi