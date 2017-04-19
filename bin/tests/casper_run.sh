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

# voir pour mettre des options en paramètres de la commande casperjs afin de lancer des scénarios de test précisément sur une action

# Complete
# casperjs test ${header}000*/*.js ${header}00[123]*/*/*.js --url=$BASE_URL/ --type-cc=VISA --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}resultVisa.xml \
# && casperjs test ${header}000*/*.js ${header}001*/*/*.js --url=$BASE_URL/ --type-cc=MasterCard --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}resultMastercard.xml

# Specific
casperjs test ${header}000*/*.js ${header}001*/*/0101*.js --url=$BASE_URL --type-cc=VISA --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}result.xml