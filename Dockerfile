FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
        libgd-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd exif \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY ext/ ext/

RUN cd ext \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && echo "extension=image_generator.so" > /usr/local/etc/php/conf.d/99-image_generator.ini

COPY . .
