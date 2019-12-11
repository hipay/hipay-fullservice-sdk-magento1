#!/bin/bash

#=============================================================================
#  Use this script build hipays images and run our containers
#
#  WARNING : Put your credentials in hipay.env
#==============================================================================

BASE_URL="http://localhost:8095/"
URL_MAILCATCHER="http://localhost:1095/"
header="bin/tests/"
pathPreFile=${header}000*/*.js
pathLibHipay=${header}000*/*/*/*.js
pathDir=${header}0*

setBackendCredentials() {
    if [ "$LOGIN_BACKEND" = "" ] || [ "$PASS_BACKEND" = "" ]; then
        printf "\n"
        while [ "$LOGIN_BACKEND" = "" ]; do
            read -p "LOGIN_BACKEND variable is empty. Insert your BO TPP login here : " login
            LOGIN_BACKEND=$login
        done
        while [ "$PASS_BACKEND" = "" ]; do
            read -p "PASS_BACKEND variable is empty. Insert your BO TPP password here : " pass
            PASS_BACKEND=$pass
        done
    fi
}
setPaypalCredentials() {
    printf "\n"
    if [ "$LOGIN_PAYPAL" = "" ] || [ "$PASS_PAYPAL" = "" ]; then
        while [ "$LOGIN_PAYPAL" = "" ]; do
            read -p "LOGIN_PAYPAL variable is empty. Insert your PayPal login here : " login
            LOGIN_PAYPAL=$login
            export LOGIN_PAYPAL=$login
            echo  "Please execute export LOGIN_PAYPAL=$login to avoid the question the next time"
        done
        while [ "$PASS_PAYPAL" = "" ]; do
            read -p "PASS_PAYPAL variable is empty. Insert your PayPal password here : " pass
            PASS_PAYPAL=$pass
            export PASS_PAYPAL=$pass
            echo  "Please execute export PASS_PAYPAL=$pass to avoid the question the next time"
        done
        printf "\n"
    fi
}

manageComposerForData() {
    COMPOSER_JSON_FILE="src/lib/Hipay/composer.json"

    echo "Setting up git pre-commit hook..."

    echo "#!/bin/bash" > .git/hooks/pre-commit
    echo "COMPOSER_JSON_FILE='"$COMPOSER_JSON_FILE"'" >> .git/hooks/pre-commit
    echo "git status --porcelain -uno | grep \$COMPOSER_JSON_FILE" >> .git/hooks/pre-commit
    echo "if [ $? -eq 0 ]" >> .git/hooks/pre-commit
    echo "then" >> .git/hooks/pre-commit
    echo "    cp \$COMPOSER_JSON_FILE \$COMPOSER_JSON_FILE.bak" >> .git/hooks/pre-commit
    echo "    cat \$COMPOSER_JSON_FILE.bak | python -c \"import sys, json; composerObj=json.load(sys.stdin); composerObj['scripts'] = None; del composerObj['scripts']; print json.dumps(composerObj, False, True, True, True, None, 2);\" > \$COMPOSER_JSON_FILE" >> .git/hooks/pre-commit
    echo "    git add \$COMPOSER_JSON_FILE" >> .git/hooks/pre-commit
    echo "fi" >> .git/hooks/pre-commit
    echo "exit 0" >> .git/hooks/pre-commit

    chmod 775 .git/hooks/pre-commit


    echo "Setting up git post-commit hook..."

    echo "#!/bin/bash" > .git/hooks/post-commit
    echo "COMPOSER_JSON_FILE='"$COMPOSER_JSON_FILE"'" >> .git/hooks/post-commit
    echo "if [ -f \$COMPOSER_JSON_FILE.bak ]" >> .git/hooks/post-commit
    echo "then" >> .git/hooks/post-commit
    echo "    cp \$COMPOSER_JSON_FILE.bak \$COMPOSER_JSON_FILE" >> .git/hooks/post-commit
    echo "    rm \$COMPOSER_JSON_FILE.bak" >> .git/hooks/post-commit
    echo "fi" >> .git/hooks/post-commit
    echo "exit 0" >> .git/hooks/post-commit

    chmod 775 .git/hooks/post-commit
}

manageComposerForData

if [ "$1" = '' ] || [ "$1" = '--help' ]; then
    echo " ==================================================== "
    echo "                     HIPAY'S HELPER                 "
    echo " ==================================================== "
    echo "      - init        : Build images and run containers (Delete existing volumes)"
    echo "      - restart     : Run all containers if they already exist"
    echo "      - logs        : Show all containers logs continually"
    echo "      - test        : Execute the tests battery"
    echo "      - test-engine : Launch advanced shell script for tests battery"
    echo "      - notif       : Simulate a notification to Magento server"
    echo ""
elif [ "$1" = 'init' ]; then
    if [ -f ./bin/docker/conf/development/hipay.env ]; then
        docker-compose -f docker-compose.dev.yml stop
        docker-compose -f docker-compose.dev.yml rm -fv
        sudo rm -Rf data/ log/ web/

        docker-compose -f docker-compose.dev.yml build --no-cache
        docker-compose -f docker-compose.dev.yml up
    else
        echo "Put your credentials in auth.env and hipay.env before start update the docker-compose.dev to link this files"
    fi
elif [ "$1" = 'restart' ]; then
    docker-compose -f docker-compose.dev.yml up
elif [ "$1" = 'logs' ]; then
    docker-compose logs -f
elif [ "$1" = 'test' ]; then
    #setBackendCredentials
    #setPaypalCredentials

    cd bin/tests/000_lib
  #  bower install hipay-casperjs-lib#develop --allow-root --force
    cd ../../../;

    if [ "$(ls -A ~/.local/share/Ofi\ Labs/PhantomJS/)" ]; then
        rm -rf ~/.local/share/Ofi\ Labs/PhantomJS/*
        printf "Cache cleared !\n\n"
    else
        printf "Pas de cache à effacer !\n\n"
    fi

    casperjs test $pathLibHipay $pathPreFile ${pathDir}/[0-1]*/[0-1][7-7][0-9][0-9]-*.js --url=$BASE_URL --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any --fail-fast
    #casperjs test $pathLibHipay $pathPreFile ${pathDir}/[0-1]*/02**-*.js --url=$BASE_URL --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any

elif [ "$1" = "test-engine" ]; then
    bash bin/tests/casper_debug.sh $BASE_URL $URL_MAILCATCHER
elif [ "$1" = "notif" ]; then
    setBackendCredentials

    while [ "$order" = "" ]; do
        read -p "In order to simulate notification to Magento server, put here an order ID : " order
    done

    casperjs test $pathLibHipay $pathPreFile ${pathDir}/[0-1]*/0200-*.js --url=$BASE_URL --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any

else
    echo "Incorrect argument ! Please check the HiPay's Helper via the following command : 'sh magento.sh' or 'sh magento.sh --help'"
fi
