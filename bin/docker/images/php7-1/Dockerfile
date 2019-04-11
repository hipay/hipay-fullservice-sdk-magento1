FROM hipay/php:7.1-apache

ENV PHP_VERSION=7.1

MAINTAINER HIPAY Fullservice <support.tpp@hipay.com>

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
