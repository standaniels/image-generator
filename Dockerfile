FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
        libgd-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd exif \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .
