FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip vim \
    libzip-dev libonig-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libicu-dev libxml2-dev \
    sqlite3 libsqlite3-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY docker/scripts/auto-init.sh /usr/local/bin/auto-init.sh
RUN chmod +x /usr/local/bin/auto-init.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/auto-init.sh"]
CMD ["php-fpm"]
