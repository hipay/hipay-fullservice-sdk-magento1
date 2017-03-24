#!/usr/bin/env bash

BASE_URL="http://localhost"
PORT_WEB="8095"
if [ "$PORT_WEB" != "80" ];then
    BASE_URL=$BASE_URL:$PORT_WEB
fi

if [ "$URL_MAILCATCHER" = "" ];then
    URL_MAILCATCHER="http://localhost:1095/"
fi

# voir pour mettre des options en paramètres de la commande casperjs afin de lancer des scénarios de test précisément sur une action

casperjs  test ./admin/admin-checkout-moto-hipay_hosted.js --url=$BASE_URL/ --type-cc=VI --url-mailcatcher=$URL_MAILCATCHER
casperjs  test ./admin/admin-checkout-normal-hipay_hosted.js --url=$BASE_URL/ --type-cc=VI
casperjs  test ./frontend/checkout-hipay_cc.js --url=$BASE_URL/ --type-cc=VI
casperjs  test ./frontend/checkout-hipay_cc.js --url=$BASE_URL/ --type-cc=MC
