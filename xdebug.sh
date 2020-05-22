#!/bin/bash

docker exec -it fpm bash -c ' cd && curl -fsSL https://pecl.php.net/get/xdebug-2.9.5.tgz -o xdebug.tgz && \
mkdir xdebug && \
tar -zxvf xdebug.tgz -C xdebug --strip-components=1 && \
rm xdebug.tgz && \
cd xdebug && \
phpize && \
./configure && \
make && make install && \
cd .. && rm -rf xdebug && \
docker-php-ext-enable xdebug'