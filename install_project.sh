#!/bin/bash

(cp ./composer.phar /usr/bin/composer ; composer install --no-interaction)
(php-fpm -R)
