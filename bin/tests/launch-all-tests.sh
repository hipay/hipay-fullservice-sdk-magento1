#!/usr/bin/env bash

casperjs test bin/tests/checkout-hipay_cc.js --url=$MAGENTO_URL --type-cc=VI
casperjs test bin/tests/checkout-hipay_cc.js --url=$MAGENTO_URL --type-cc=MC