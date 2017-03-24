#!/usr/bin/env bash

BASE_URL=$MAGENTO_URL
if [ "$PORT_WEB" != "80" ];then
    BASE_URL=$BASE_URL:$PORT_WEB
fi

if [ "$URL_MAILCATCHER" = "" ];then
    URL_MAILCATCHER="http://smtp:1080/"
fi

# voir pour mettre des options en paramètres de la commande casperjs afin de lancer des scénarios de test précisément sur une action

casperjs  test ./admin/admin-checkout-moto-hipay_hosted.js --url=$BASE_URL/ --type-cc=VI --url-mailcatcher=$URL_MAILCATCHER
casperjs  test ./admin/admin-checkout-normal-hipay_hosted.js --url=$BASE_URL/ --type-cc=VI
casperjs  test ./frontend/checkout-hipay_cc.js --url=$BASE_URL/ --type-cc=VI
casperjs  test ./frontend/checkout-hipay_cc.js --url=$BASE_URL/ --type-cc=MC
