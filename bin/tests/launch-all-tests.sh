#!/usr/bin/env bash

casperjs test ./frontend/checkout-hipay_cc.js --url=$MAGENTO_URL --type-cc=VI
casperjs test ./frontend/checkout-hipay_cc.js --url=$MAGENTO_URL --type-cc=MC
#casperjs test --verbose --log-level=debug /tmp/tests/admin/admin-checkout-sendemail-hipay_hosted.js --url=$MAGENTO_URL --type-cc=VI