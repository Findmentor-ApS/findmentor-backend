FROM php:8.1.0-zts

RUN apt-get update && apt-get install -y git ssh zip unzip
RUN pecl install xdebug-3.1.3 && docker-php-ext-enable xdebug

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_autostart = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_log = /var/www/api/logs/xdeubg.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/swagger

EXPOSE 8081
CMD php -S 0.0.0.0:8081
