#!/usr/bin/env bash

casperjs test /tmp/tests/frontend/checkout-hipay_cc.js --url=$MAGENTO_URL --type-cc=VI
casperjs test /tmp/tests/frontend/checkout-hipay_cc.js --url=$MAGENTO_URL --type-cc=MC