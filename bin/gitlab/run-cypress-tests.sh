#!/bin/bash
set -e

cp ./bin/tests/tests-cypress/.npmrc.sample ./bin/tests/tests-cypress/.npmrc
cd bin/tests/tests-cypress
yarn install

echo "bash /tools/run-cypress-test.sh -f $TESTS_FOLDERS_1 --config baseUrl=http://localhost --env $CYPRESS_ENV "
bash /tools/run-cypress-test.sh -f $TESTS_FOLDERS_1 --config baseUrl=http://localhost --env $CYPRESS_ENV