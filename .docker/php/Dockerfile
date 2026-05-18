FROM php:8.4-fpm

WORKDIR /app

RUN export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libicu-dev \
        libpq-dev \
        libxpm-dev \
        libvpx-dev \
        libgmp-dev \
        libmagickwand-dev \
        libzip-dev \
        libcurl4-openssl-dev \
        libmagic-dev \
        libevent-dev \
        libidn2-0-dev \
        libidn11-dev \
        zlib1g-dev \
        unzip \
        git \
        librdkafka-dev \
    && rm -rf /var/lib/apt/lists/* \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/

# Установка PHP расширений через PECL
RUN pecl install imagick && \
    docker-php-ext-enable imagick

# Настройка и установка встроенных расширений
RUN docker-php-ext-configure gmp && \
    docker-php-ext-install gmp bcmath zip intl pgsql pdo_pgsql exif && \
    docker-php-ext-configure gd --with-freetype=/usr/include/freetype2 --with-jpeg=/usr/include && \
    docker-php-ext-install -j$(nproc) gd

# Настройка pgsql
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

# Установка Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
     && php composer-setup.php --install-dir=/usr/bin --filename=composer \
     && php -r "unlink('composer-setup.php');"

# Копируем конфиги
ADD .docker/php/cronjobs /etc/cron.d/cronjobs
ADD .docker/php/php.ini /usr/local/etc/php/

# Установка cron
RUN apt-get update && apt-get install -y cron && \
    chmod 0644 /etc/cron.d/cronjobs && \
    crontab /etc/cron.d/cronjobs && \
    touch /var/log/cron.log

# Установка Xdebug
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# ========== Установка librdkafka и PHP расширения ==========
RUN pecl install rdkafka && \
    docker-php-ext-enable rdkafka

# ========== Установка Redis ==========
RUN pecl install redis && \
    docker-php-ext-enable redis

# ========== ВАЖНО: КОПИРУЕМ КОД ==========
COPY . /app/

RUN if [ -d "/app/storage" ]; then \
        chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || true \
        && chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true; \
    fi
