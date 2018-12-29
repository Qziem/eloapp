FROM php:7.2-fpm
RUN apt-get update && apt-get install -y mysql-client git zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN docker-php-ext-install pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && useradd composer \
    && mkdir /home/composer \
    && chown composer:composer /home/composer \
    && usermod -aG sudo composer