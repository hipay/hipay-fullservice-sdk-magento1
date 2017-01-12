#!/usr/bin/env bash

casperjs --log-level=debug test bin/tests/checkout-hipay_cc.js --url=$MAGENTO_URL --type-cc=VI
casperjs --log-level=debug test bin/tests/checkout-hipay_cc.js --url=$MAGENTO_URL --type-cc=MC