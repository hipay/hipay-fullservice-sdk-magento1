#!/bin/bash

set -e

if [ "$2" != "" ]; then
    BASE_URL=$2
else
    BASE_URL=$MAGENTO_URL
fi

if [ "$URL_MAILCATCHER" = "" ];then
    URL_MAILCATCHER="http://smtp:1080/"
fi

cd bin/tests/000_lib
bower install hipay-casperjs-lib#develop --allow-root
cd ../../../;

header="bin/tests/"

if [ "$1" = "0" ];then
    echo "Execute part $1 of casperjs test"
   casperjs test ${header}000*/*/*/*.js ${header}000*/000*.js ${header}0[0-1][0-1]*/[0-1]*/[0-9][0-9][0-9][0-9]-*.js --url=$BASE_URL/ --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any --fail-fast
elif [ "$1" = "1" ];then
    echo "Execute part $1 of casperjs test"
    casperjs test ${header}000*/*/*/*.js ${header}000*/000*.js ${header}0[0-1][2]*/[0-1]*/[0-9][0-9][0-9][0-9]-*.js --url=$BASE_URL/ --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any --fail-fast
elif [ "$1" = "2" ];then
    echo "Execute part $1 of casperjs test"
    casperjs test ${header}000*/*/*/*.js ${header}000*/000*.js ${header}0[0-1][3]*/[0-1]*/[0-9][0-9][0-9][0-9]-*.js --url=$BASE_URL/ --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any --fail-fast
elif [ "$1" = "3" ];then
    echo "Execute part $1 of casperjs test"
    casperjs test ${header}000*/*/*/*.js ${header}000*/000*.js ${header}0[0-1][4-9]*/[0-1]*/[0-9][0-9][0-9][0-9]-*.js --url=$BASE_URL/ --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any --fail-fast
fi




