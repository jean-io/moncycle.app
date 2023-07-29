FROM php:apache

RUN apt-get update \
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev  unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

#RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY ./server_conf/moncycleapp_apache.conf /etc/apache2/conf-enabled/
COPY ./server_conf/moncycleapp_php.ini $PHP_INI_DIR/conf.d

RUN mkdir -p /var/lib/php/session && mkdir -p /var/lib/php/soap_cache && chown -R www-data:www-data /var/lib/php/

COPY ./www_data /var/www/html/
COPY ./www_data/config.docker.php /var/www/html/config.php

RUN bash /var/www/html/module/install.sh

