FROM php:8.0-apache
RUN apt-get update && apt-get install -y python3
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
COPY . /var/www/html/
