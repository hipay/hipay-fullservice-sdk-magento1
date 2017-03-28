#!/usr/bin/env bash

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

casperjs test ${header}000*/*.js ${header}002*/*/*.js --url=$BASE_URL --type-cc=VISA --url-mailcatcher=$URL_MAILCATCHER \
&& casperjs test ${header}000*/*.js ${header}001*/*/*.js --url=$BASE_URL --type-cc=VISA \
&& casperjs test ${header}000*/*.js ${header}001*/*/*.js --url=$BASE_URL --type-cc=MasterCard