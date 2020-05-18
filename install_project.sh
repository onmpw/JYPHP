#!/bin/bash

(cp ./composer.phar /usr/bin/composer ; composer update)
(php-fpm -R)