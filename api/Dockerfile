FROM php:8.1.11-zts

RUN apt-get update && apt-get install -y git ssh zip unzip
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN pecl install xdebug-3.1.3 && docker-php-ext-enable xdebug
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_autostart = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_log = /var/www/api/logs/xdeubg.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

ARG UID=1000
ARG GID=1000
RUN groupadd -g $GID findmentor && useradd -u $UID -g findmentor -s /bin/sh findmentor
USER findmentor

WORKDIR /var/www/api
EXPOSE 8080
ENTRYPOINT ["php", "build.local.php"]
