#!/bin/bash

# Variables Shell
COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'
ENV_DEVELOPMENT="development"
ENV_STAGE="stage"
PREFIX=$RANDOM

printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
printf "\n${COLOR_SUCCESS}       CHECK MAGENTO INSTALLATION        ${NC}\n"
printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
 if [ ! -f /var/www/htdocs/index.php ]; then

    printf "\n${COLOR_SUCCESS}  => MAGENTO IS NOT YET INSTALLED : INSTALLATION IS BEGINNING ${NC}\n"

    # Download MAGENTO from repository
    cd /tmp && curl -s https://codeload.github.com/OpenMage/magento-mirror/tar.gz/$MAGENTO_VERSION -o $MAGENTO_VERSION.tar.gz && tar xf $MAGENTO_VERSION.tar.gz && cp -rf magento-mirror-$MAGENTO_VERSION/* magento-mirror-$MAGENTO_VERSION/.htaccess /var/www/htdocs

    sleep 10

    # Install demo
    install-sampledata

    sleep 10

    # Install magento
    install-magento


    chown -R www-data:www-data /var/www/htdocs
    chmod -R a+rw /var/www/htdocs
    rm -f  /var/www/htdocs/install.php

     # override files for hipay after installation of magento #
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}              COPY HIPAY FILES           ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cp -Rf /tmp/src/app/code /var/www/htdocs/app/
    cp -Rf /tmp/src/app/design /var/www/htdocs/app/
    cp -Rf /tmp/src/app/etc /var/www/htdocs/app/
    cp -Rf /tmp/src/app/locale /var/www/htdocs/app/
    cp -Rf /tmp/src/skin /var/www/htdocs/

    printf "\n"
    echo "Files from local folder \"src\" are transfered in dockerized magento"
    printf "\n"
    n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" cache:clean

    # Prefix for Entity Order
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}          UPDATE TRANSACTION PREFIX      ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT"  db:query "INSERT INTO eav_entity_store values (9,5,2,1,2);"

    n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT"  db:query "UPDATE eav_entity_store
                               INNER JOIN eav_entity_type ON eav_entity_type.entity_type_id = eav_entity_store.entity_type_id
                               SET eav_entity_store.increment_prefix='$PREFIX'
                               ,eav_entity_store.increment_last_id='$PREFIX0000001'
                               WHERE eav_entity_type.entity_type_code='order';"

    echo " Prefix for order id : $PREFIX"

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}          SET HIPAY CREDENTIALS          ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set hipay/hipay_api/api_username_test $HIPAY_API_USER_TEST
    n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set --encrypt hipay/hipay_api/api_password_test $HIPAY_API_PASSWORD_TEST
    n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set hipay/hipay_api/api_tokenjs_publickey_test $HIPAY_TOKENJS_PUBLICKEY_TEST
    n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set --encrypt hipay/hipay_api/secret_passphrase_test $HIPAY_SECRET_PASSPHRASE_TEST
    n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set hipay/hipay_api/api_tokenjs_username_test $HIPAY_TOKENJS_USERNAME_TEST

    n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set hipay/hipay_api_moto/api_username_test $HIPAY_API_USER_TEST
    n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set --encrypt hipay/hipay_api_moto/api_password_test $HIPAY_API_PASSWORD_TEST
    n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set --encrypt hipay/hipay_api_moto/secret_passphrase_test $HIPAY_SECRET_PASSPHRASE_TEST

    printf "\n"
    echo " YOUR CREDENTIALS ARE : "
    echo "  API_USERNAME          : $HIPAY_API_USER_TEST"
    echo "  API_PASSWORD          : $HIPAY_API_PASSWORD_TEST"
    echo "  API_TOKEN_JS_USERNAME : $HIPAY_TOKENJS_USERNAME_TEST"
    echo "  API_TOKEN_JS_PASSWORD : $HIPAY_TOKENJS_PUBLICKEY_TEST"
    echo "  API_SECRET_PASSPHRASE : $HIPAY_API_USER_TEST\n"

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         ACTIVATE PAYMENT METHODS        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    methods=$(echo $ACTIVE_METHODS| tr "," "\n")
    for code in $methods
    do
        printf "\n"
        n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set payment/$code/active 1
        n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set payment/$code/debug 1
        n98-magerun.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:set payment/$code/is_test_mode 1
        printf "${COLOR_SUCCESS} Method $code is activated with test mode ${NC}\n"
    done

    # Configuration per environment
    if [ "$ENVIRONMENT" = "$ENV_DEVELOPMENT" ];then

        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
        printf "\n${COLOR_SUCCESS} APPLY CONFIGURATION :   $ENV_DEVELOPMENT ${NC}\n"
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" cache:disable
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" dev:log --on --global
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" dev:log:db --on

        # INSTALL X DEBUG
        echo '' | pecl install xdebug
        echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

        printf "\n"
        echo "XDebug installation : YES "

        cp -f /tmp/$ENVIRONMENT/php/php.ini /usr/local/etc/php/php.ini
    else
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
        printf "\n${COLOR_SUCCESS}     APPLY CONFIGURATION  $ENVIRONMENT     ${NC}\n"
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" cache:clean
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" cache:disable
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" dev:log --on --global
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" dev:log:db --off

        cp -f /tmp/$ENVIRONMENT/php/php.ini /usr/local/etc/php/php.ini
    fi

     if [ "$PORT_WEB" != "80" ] && [ "$ENVIRONMENT" != "production" ];then
         sed -i -e "s/80/$PORT_WEB/" /etc/apache2/sites-available/000-default.conf

         echo "Listen $PORT_WEB" >> /etc/apache2/ports.conf

         if [ "$PHP_VERSION" = "5.4" ];then
           echo "Listen $PORT_WEB" >> /etc/apache2/apache2.conf
         fi
     fi

    # SUPPORT PHP 7
    if [ "$PHP_VERSION" = "7.0" ];then
        sed -i -e "555s/\$callback\[0\])->\$callback\[1\]();/\$callback\[0\])->\{\$callback\[1\]\}();/" /var/www/htdocs/app/code/core/Mage/Core/Model/Layout.php
    fi

else
     printf "\n${COLOR_SUCCESS}  => MAGENTO IS ALREADY INSTALLED IN THE CONTAINER ${NC}\n"
fi

chown -R www-data:www-data /var/www/htdocs

export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data
export APACHE_PID_FILE=/var/run/apache2/apache2.pid
export APACHE_RUN_DIR=/var/run/apache2
export APACHE_LOCK_DIR=/var/lock/apache2
export APACHE_LOG_DIR=/var/log/apache2

printf "${COLOR_SUCCESS}    |======================================================================${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |               DOCKER MAGENTO TO HIPAY $ENVIRONMENT IS UP             ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL FRONT       : $MAGENTO_URL:$PORT_WEB                           ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL BACK        : $MAGENTO_URL:$PORT_WEB/admin                     ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL MAIL CATCHER: $MAGENTO_URL:1095/                               ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |   PHP VERSION     : $PHP_VERSION                                     ${NC}\n"
printf "${COLOR_SUCCESS}    |   MAGENTO VERSION : $MAGENTO_VERSION                                 ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================${NC}\n"

exec apache2 -DFOREGROUND

