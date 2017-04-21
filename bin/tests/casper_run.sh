#!/bin/bash

if [ "$MAGENTO_URL" != "" ]; then
	BASE_URL=$MAGENTO_URL
	if [ "$PORT_WEB" != "80" ]; then
		BASE_URL=$BASE_URL:$PORT_WEB
	fi
else
	BASE_URL="http://localhost:8095/"
fi

if [ "$URL_MAILCATCHER" = "" ]; then
    URL_MAILCATCHER="http://localhost:1095/"
fi

header="bin/tests/"

# Complete
# casperjs test ${header}000*/000[0-1]*.js ${header}0[0-1][0-9]*/[0-1]*/[0-9][0-9][0-9][0-9]-*.js --url=$BASE_URL/ --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}result.xml

# Specific
casperjs test ${header}000*/000[0-1]*.js ${header}001*/*/0100*.js --url=$BASE_URL --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any