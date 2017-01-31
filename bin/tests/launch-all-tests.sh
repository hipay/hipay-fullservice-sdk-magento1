#!/usr/bin/env bash

BASE_URL=$MAGENTO_URL
if [ "$PORT_WEB" != "80" ];then
    BASE_URL=$BASE_URL:$PORT_WEB
fi

casperjs test ./frontend/checkout-hipay_cc.js --url=$BASE_URL/ --type-cc=VI
casperjs test ./frontend/checkout-hipay_cc.js --url=$BASE_URL/ --type-cc=MC
casperjs test ./admin/admin-checkout-moto-hipay_hosted.js --url=$BASE_URL/ --type-cc=VI
casperjs test ./admin/admin-checkout-normal-hipay_hosted.js --url=$BASE_URL/ --type-cc=VI