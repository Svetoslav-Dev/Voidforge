FROM php:8.3-fpm

ARG WWWUSER=1000
ARG WWWGROUP=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        mariadb-client \
        curl \
        nginx-light \
        openssl \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupmod -o -g ${WWWGROUP} www-data \
    && usermod -o -u ${WWWUSER} -g www-data www-data

WORKDIR /var/www/html

COPY docker/app/entrypoint.sh /usr/local/bin/voidforge-entrypoint
RUN chmod +x /usr/local/bin/voidforge-entrypoint

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/app/start.sh /usr/local/bin/voidforge-start
RUN chmod +x /usr/local/bin/voidforge-start

ENTRYPOINT ["voidforge-entrypoint"]
CMD ["voidforge-start"]
