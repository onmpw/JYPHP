FROM php:7.3-fpm

WORKDIR /usr/share/html/JYPHP

RUN sed -i "s@http://deb.debian.org@http://mirrors.163.com@g" /etc/apt/sources.list \
    && rm -Rf /var/lib/apt/lists/* \
    && sed  "/security.debian.org/d" /etc/apt/sources.list \
    && apt-get clean
RUN apt-get update --fix-missing && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        git \
	zip \
	unzip \
    && docker-php-ext-configure gd --with-freetype-dir --with-jpeg-dir \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql


CMD ["sh","install_project.sh"]

