FROM php:8.2.27-apache
RUN apt update && apt install -y vim git libzip-dev zip unzip npm
RUN docker-php-ext-install pdo pdo_mysql zip
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www/html

# DANGEROUS: Do not enable directory listing unless you know what you're doing
# RUN sed -i 's/Options -Indexes/Options Indexes/' /etc/apache2/conf-enabled/docker-php.conf
