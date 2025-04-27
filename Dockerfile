FROM php:8.4-apache as dev
RUN apt update &&  \
    apt install -y libzip-dev unzip && \
    docker-php-ext-install pdo pdo_mysql zip

FROM dev as prod
COPY public /var/www/html
COPY src /var/www/src
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www/html
