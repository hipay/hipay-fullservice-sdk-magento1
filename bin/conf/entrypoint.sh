#!/bin/sh -x

sleep 30

install-sampledata

sleep 30

install-magento



echo "\n* Almost ! Starting Apache now\n";
exec apache2 -DFOREGROUND

