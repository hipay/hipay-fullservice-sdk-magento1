FROM php:5.6-apache

# Add PhantomJS & CasperJS
ENV PHANTOM_VER 2.1.1
ENV PHANTOM_URL https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-${PHANTOM_VER}-linux-x86_64.tar.bz2
ENV PHANTOM_DIR /usr/local/bin

ENV CASPER_VER 1.1.3
ENV CASPER_URL https://github.com/casperjs/casperjs/archive/${CASPER_VER}.tar.gz
ENV CASPER_DIR /usr/local/casperjs

# INSTALL PHP EXTENSION
RUN requirements="libpng12-dev libxml2-dev libmcrypt-dev libmcrypt4 libcurl3-dev libfreetype6 libjpeg62-turbo libpng12-dev libfreetype6-dev libjpeg62-turbo-dev mysql-client bzip2" \
    && apt-get update && apt-get install -y $requirements && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-install mbstring \
    &&  docker-php-ext-install soap \
    && docker-php-ext-install pdo mysqli \
    && curl -sSL $PHANTOM_URL | tar xj -C $PHANTOM_DIR --strip 2 --wildcards '*/bin/phantomjs' \
    && chmod +x /usr/local/bin/phantomjs \
    && mkdir -p $CASPER_DIR \
    && curl -sSL $CASPER_URL | tar xz --strip 1 -C $CASPER_DIR \
    && ln -sf $CASPER_DIR/bin/casperjs /usr/local/bin/ \
    && requirementsToRemove="libpng12-dev libmcrypt-dev libcurl3-dev libpng12-dev libfreetype6-dev libjpeg62-turbo-dev bzip2" \
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
COPY ./bin/tests /tmp
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
