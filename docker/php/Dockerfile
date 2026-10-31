# syntax=docker/dockerfile:1.7
ARG PHP_VERSION=8.4.27

FROM composer:2.11.1 AS composer
FROM mlocati/php-extension-installer:2.13.1 AS ext-installer

FROM php:${PHP_VERSION}-fpm-trixie AS base

ARG UID=1000
ARG GID=1000
ENV TZ=Europe/Moscow

COPY --from=ext-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Расширения, которые требует БУС (bitrix_server_test) + нужные проекту:
# sockets/pcntl — консьюмеры RabbitMQ с graceful shutdown, redis — кеш/сессии/rate limit.
# DL3008: версии apt не пиннем — в Debian они меняются с каждым point-релизом; воспроизводимость даёт тег базового образа.
# hadolint ignore=DL3008
RUN install-php-extensions \
        bcmath exif gd intl mysqli opcache pcntl pdo_mysql redis-6.3.0 sockets zip \
 && apt-get update \
 && apt-get install -y --no-install-recommends libfcgi-bin msmtp-mta unzip \
 && rm -rf /var/lib/apt/lists/*

# www-data получает UID/GID пользователя хоста — файлы ядра в ./www не будут принадлежать root
RUN groupmod -o -g "${GID}" www-data \
 && usermod -o -u "${UID}" -g www-data www-data \
 && mkdir -p /app/www/local /var/log/php \
 && chown -R www-data:www-data /app /var/log/php

COPY php.ini /usr/local/etc/php/conf.d/zz-bitrix.ini
COPY fpm-pool.conf /usr/local/etc/php-fpm.d/zz-pool.conf
COPY msmtprc /etc/msmtprc
COPY healthcheck.sh /usr/local/bin/healthcheck

WORKDIR /app
USER ${UID}:${GID}

HEALTHCHECK --interval=10s --timeout=3s --retries=5 CMD ["healthcheck"]
