ARG BASE_IMAGE

FROM $BASE_IMAGE

COPY . .

RUN composer install

COPY .docker/production/php/main/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY .docker/production/php/main/docker-php-entrypoint /usr/local/bin/
RUN chmod 755 /usr/local/bin/docker-php-entrypoint
