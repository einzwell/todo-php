FROM php:8.4-apache as dev
RUN apt update &&  \
    apt install -y libzip-dev unzip && \
    docker-php-ext-install pdo pdo_mysql zip

FROM dev as prod
COPY public /var/www/public
COPY src /var/www/html
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www/html
