FROM php:7.1-apache-stretch

ENV PHP_VERSION=7.1

MAINTAINER HIPAY Fullservice <support.tpp@hipay.com>

# INSTALL PHP EXTENSION
RUN apt-get update \
    && apt-get install -y curl gnupg \
    && curl -sL https://deb.nodesource.com/setup_10.x | bash - \
    && requirements="libpng-dev libxml2-dev libmcrypt-dev libmcrypt4 libcurl3-dev libfreetype6 libjpeg62-turbo git zip unzip default-libmysqlclient-dev libfreetype6-dev libjpeg62-turbo-dev mysql-client bzip2 ssmtp npm libfontconfig libicu57 libsqlite3-0 vim rsyslog cron" \
    && apt-get update && apt-get install -y $requirements && rm -rf /var/lib/apt/lists/* \
    && echo "sendmail_path = /usr/sbin/ssmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini \
    && echo "mailhub=common-smtp:1025\n UseTLS=NO\n FromLineOverride=YES" > /etc/ssmtp/ssmtp.conf \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install soap \
    && docker-php-ext-install pdo mysqli \
    && curl -sS https://getcomposer.org/installer | php -- --filename=composer -- --install-dir=/usr/local/bin \
    && requirementsToRemove="libpng12-dev libcurl3-dev libpng12-dev libfreetype6-dev libjpeg62-turbo-dev" \
    && apt-get purge --auto-remove -y $requirementsToRemove

# ADD PHANTOM AND CASPER
RUN curl -sSL https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-2.1.1-linux-x86_64.tar.bz2 | tar xj -C /usr/local/bin --strip 2 --wildcards '*/bin/phantomjs' \
    && chmod +x /usr/local/bin/phantomjs
RUN npm install -g casperjs

RUN usermod -u 1000 www-data
RUN a2enmod rewrite
RUN sed -i -e 's/\/var\/www\/html/\/var\/www\/htdocs/' /etc/apache2/apache2.conf

# REMOVE OLD FILES IN VOLUME
RUN rm -Rf /var/www/htdocs
RUN rm -Rf /var/lib/mysql

# COPY CONF AND HIPAY MODULE
COPY ./bin/docker /tmp
COPY ./bin/tests /tmp/tests
COPY ./src /tmp/src

# COPY INSTALLER AND SAMPLE DATA
COPY ./bin/docker/images/install/install-magento /usr/local/bin/install-magento
RUN chmod +x /usr/local/bin/install-magento

COPY ./bin/docker/images/sampledata/magento-sample-data-1.9.1.0.tgz /opt/
COPY ./bin/docker/images/sampledata/magento-sample-data-1.6.1.0.tar.gz /opt/

COPY ./bin/docker/images/install/install-sampledata-1.9 /usr/local/bin/install-sampledata-1.9
RUN chmod +x /usr/local/bin/install-sampledata-1.9
COPY ./bin/docker/images/install/install-sampledata-1.6_1.8 /usr/local/bin/install-sampledata-1.6_1.8
RUN chmod +x /usr/local/bin/install-sampledata-1.6_1.8

RUN sed -i -e 's/\/var\/www\/html/\/var\/www\/htdocs/' /etc/apache2/sites-available/000-default.conf

# INSTALL MAGERUN
RUN curl -O  https://raw.githubusercontent.com/netz98/n98-magerun/master/n98-magerun.phar && \
    chmod +x ./n98-magerun.phar && \
    ./n98-magerun.phar selfupdate && \
    cp ./n98-magerun.phar /usr/local/bin/ && \
    rm ./n98-magerun.phar && \
    apt-get purge -y --auto-remove

WORKDIR /var/www/htdocs

ENV MAGENTO_VERSION=1.9.4.0
ENV SAMPLE_DATA_VERSION=1.9

ENTRYPOINT ["/tmp/images/entrypoint.sh"]
