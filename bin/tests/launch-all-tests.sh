#!/usr/bin/env bash

if [ "$MAGENTO_URL" != "" ]; then
	BASE_URL=$MAGENTO_URL
	if [ "$PORT_WEB" != "80" ]; then
		BASE_URL=$BASE_URL:$PORT_WEB
	fi
else
	BASE_URL="http://localhost:8095"
fi

if [ "$URL_MAILCATCHER" == "" ]; then
    URL_MAILCATCHER="http://localhost:1095/"
fi

header="bin/tests/"

# voir pour mettre des options en paramètres de la commande casperjs afin de lancer des scénarios de test précisément sur une action

casperjs test $header/admin/admin-checkout-moto-hipay_hosted.js --url=$BASE_URL/ --type-cc=VI --url-mailcatcher=$URL_MAILCATCHER &&
casperjs test $header/admin/admin-checkout-normal-hipay_hosted.js --url=$BASE_URL/ --type-cc=VI &&
casperjs test $header/frontend/checkout-hipay_cc.js --url=$BASE_URL/ --type-cc=VI &&
casperjs test $header/frontend/checkout-hipay_cc.js --url=$BASE_URL/ --type-cc=MC