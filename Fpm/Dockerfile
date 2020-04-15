FROM php:7.4-fpm

COPY App    /usr/share/html/JYPHP/App
COPY Module /usr/share/html/JYPHP/Module
COPY Service /usr/share/html/JYPHP/Service
COPY index.php /usr/share/html/JYPHP/
COPY composer.phar /usr/share/html/JYPHP/composer.phar
COPY composer.json /usr/share/html/JYPHP/composer.json
COPY composer.lock /usr/share/html/JYPHP/composer.lock

WORKDIR /usr/share/html/JYPHP

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && mv composer.phar /usr/local/bin/composer \
    && mkdir Data && chmod o+w Data

CMD ["php-fpm","-R"]

