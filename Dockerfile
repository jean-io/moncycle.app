FROM php:apache
RUN docker-php-ext-install pdo pdo_mysql
COPY ./www_data /var/www/html/
