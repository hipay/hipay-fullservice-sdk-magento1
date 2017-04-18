#!/bin/bash

BASE_URL=$MAGENTO_URL
if [ "$PORT_WEB" != "80" ];then
    BASE_URL=$BASE_URL:$PORT_WEB
fi

if [ "$URL_MAILCATCHER" = "" ];then
    URL_MAILCATCHER="http://smtp:1080/"
fi

header="bin/tests/"

# voir pour mettre des options en paramètres de la commande casperjs afin de lancer des scénarios de test précisément sur une action

casperjs test ${header}000*/*.js ${header}00[123]*/*/*.js --url=$BASE_URL/ --type-cc=VISA --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}resultVisa.xml \
&& casperjs test ${header}000*/*.js ${header}001*/*/*.js --url=$BASE_URL/ --type-cc=MasterCard --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND
