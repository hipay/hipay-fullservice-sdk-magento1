#!/bin/bash
set -e

bash bin/tests/casper_run_gitlab.sh $1 http://localhost
curl -s http://localhost/var/log/system.log | grep -i hipay
if [[ $? -eq 0 ]]
then
    exit 1;
else
    exit 0;
fi;
