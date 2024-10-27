FROM composer:2 AS builder

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --prefer-dist

FROM php:8.2-fpm-bullseye AS runner

RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libxpm-dev \
    libzip-dev \
    zip \
    libonig-dev && \
    docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-webp=/usr/include/ --with-xpm=/usr/include/ && \
    docker-php-ext-install pdo_mysql mbstring gd zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=builder /var/www/html /var/www/html
COPY .deploy/nginx.conf /etc/nginx/nginx.conf
COPY .deploy/supervisord.conf /etc/supervisord.conf
COPY .deploy/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

WORKDIR /var/www/html
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
