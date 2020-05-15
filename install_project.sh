#!/bin/bash

(cp ./composer.phar /usr/bin/composer ; composer install)
(php-fpm -R)