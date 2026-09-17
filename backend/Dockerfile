FROM php:8.4-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
EXPOSE 80