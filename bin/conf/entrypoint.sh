#!/bin/bash -x

ENV_DEVELOPMENT="development"
ENV_STAGE="stage"

echo "\n* STEP 1 : CHECK MAGENTO INSTALLATION   \n";
 if [ ! -f /var/www/htdocs/index.php ]; then
    echo "\n* MAGENTO IS NOT INSTALLED : INSTALLATION IS BEGINNING   \n";

    rm -rf /var/www/htdocs/*

    # Download MAGENTO from repository
    cd /tmp && curl https://codeload.github.com/OpenMage/magento-mirror/tar.gz/$MAGENTO_VERSION -o $MAGENTO_VERSION.tar.gz && tar xvf $MAGENTO_VERSION.tar.gz && cp -rf magento-mirror-$MAGENTO_VERSION/* magento-mirror-$MAGENTO_VERSION/.htaccess /var/www/htdocs

    sleep 10

    # Install demo
    install-sampledata

    sleep 10

    # Install magento
    install-magento

    chown -R www-data:www-data /var/www/htdocs
    chmod -R a+rw /var/www/htdocs
    rm -f  /var/www/htdocs/install.php

    if [ "$ENVIRONMENT" = "$ENV_DEVELOPMENT" ];then
        echo "\n* APPLY CONFIGURATION :   $ENV_DEVELOPMENT  \n";
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" cache:disable
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" dev:log --on --global
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" dev:log:db --on

        echo "\n* SET CREDENTIALS \n";
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" config:set hipay/hipay_api/api_username_test $HIPAY_API_USER_TEST
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" config:set --encrypt hipay/hipay_api/api_password_test $HIPAY_API_PASSWORD_TEST
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" config:set hipay/hipay_api/api_tokenjs_publickey_test $HIPAY_TOKENJS_PUBLICKEY_TEST
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" config:set --encrypt hipay/hipay_api/secret_passphrase_test $HIPAY_SECRET_PASSPHRASE_TEST
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" config:set hipay/hipay_api/api_tokenjs_username_test $HIPAY_TOKENJS_USERNAME_TEST

        echo "\n* ACTIVATE PAYMENT METHODS \n";
        methods=$(echo $ACTIVE_METHODS| tr "," "\n")
        for code in $methods
        do
            n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" config:set payment/$code/active 1
            n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" config:set payment/$code/debug 1
            n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" config:set payment/$code/is_test_mode 1
        done

        # INSTALL X DEBUG
        yes | pecl install xdebug
        echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

        cp -f /tmp/$ENVIRONMENT/php/php.ini /usr/local/etc/php/php.ini
    else
        echo "\n* APPLY CONF  $ENV_STAGE  \n";
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" cache:enable
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" dev:log --on --global
        n98-magerun.phar --skip-root-check --root-dir="$MAGENTO_ROOT" dev:log:db --off

        cp -f /tmp/$ENVIRONMENT/php/php.ini /usr/local/etc/php/php.ini
    fi
fi

# override files for hipay after installation of magento #
echo "\n* STEP 2 : CHECK MAGENTO INSTALLATION   \n";
cp -Rf /tmp/src/app/code /var/www/htdocs/app/
cp -Rf /tmp/src/app/design /var/www/htdocs/app/
cp -Rf /tmp/src/app/etc /var/www/htdocs/app/
cp -Rf /tmp/src/app/locale /var/www/htdocs/app/
cp -Rf /tmp/src/skin /var/www/htdocs/

chown -R www-data:www-data /var/www/htdocs

export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data
export APACHE_PID_FILE=/var/run/apache2/apache2.pid
export APACHE_RUN_DIR=/var/run/apache2
export APACHE_LOCK_DIR=/var/lock/apache2
export APACHE_LOG_DIR=/var/log/apache2

echo "\n* STEP 3 : STARTING APACHE  \n";
exec apache2 -DFOREGROUND

