FROM php:5.6-apache

# INSTALL PHP EXTENSION
RUN requirements="libpng12-dev libxml2-dev libmcrypt-dev libmcrypt4 libcurl3-dev libfreetype6 libjpeg62-turbo libpng12-dev libfreetype6-dev libjpeg62-turbo-dev mysql-client" \
    && apt-get update && apt-get install -y $requirements && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-install mbstring \
    &&  docker-php-ext-install soap \
    && docker-php-ext-install pdo mysqli \
    && requirementsToRemove="libpng12-dev libmcrypt-dev libcurl3-dev libpng12-dev libfreetype6-dev libjpeg62-turbo-dev" \
    && apt-get purge --auto-remove -y $requirementsToRemove

RUN usermod -u 1000 www-data
RUN a2enmod rewrite
RUN sed -i -e 's/\/var\/www\/html/\/var\/www\/htdocs/' /etc/apache2/apache2.conf

# REMOVE OLD FILES IN VOLUME
RUN rm -Rf /var/www/htdocs
RUN rm -Rf /var/lib/mysql

# REMOVE OLD FILE I<>ddN LOCAL
#RUN rm -Rf ./data/
#RUN rm -Rf ./web/
#RUN rm -Rf ./log/

# COPY CONF AND HIPAY MODULE
COPY ./bin/conf /tmp
COPY ./src /tmp/src

# COPY INSTALLER AND SAMPLE DATA
COPY ./bin/install/install-magento /usr/local/bin/install-magento
RUN chmod +x /usr/local/bin/install-magento
COPY ./bin/sampledata/magento-sample-data-1.9.1.0.tgz /opt/
COPY ./bin/install/install-sampledata-1.9 /usr/local/bin/install-sampledata
RUN chmod +x /usr/local/bin/install-sampledata

RUN sed -i -e 's/\/var\/www\/html/\/var\/www\/htdocs/' /etc/apache2/sites-available/000-default.conf

# INSTALL MAGERUN 
RUN curl -O  https://raw.githubusercontent.com/netz98/n98-magerun/master/n98-magerun.phar && \
    chmod +x ./n98-magerun.phar && \
    ./n98-magerun.phar selfupdate && \
    cp ./n98-magerun.phar /usr/local/bin/ && \
    rm ./n98-magerun.phar && \
    apt-get purge -y --auto-remove

VOLUME /var/www/htdocs

ENTRYPOINT ["/tmp/entrypoint.sh"]
