# syntax=docker/dockerfile:1.7
ARG PHP_VERSION=8.4.27

FROM php:${PHP_VERSION}-cli-trixie

# Встроенный сервер с несколькими воркерами: моки вызывают сайт (webhook-callback) и сайт вызывает моки — один
# поток привёл бы к взаимной блокировке.
ENV PHP_CLI_SERVER_WORKERS=4

ARG UID=1000
ARG GID=1000
RUN groupmod -o -g "${GID}" www-data && usermod -o -u "${UID}" -g www-data www-data

WORKDIR /app
USER ${UID}:${GID}
EXPOSE 8080

HEALTHCHECK --interval=10s --timeout=3s --retries=5 \
    CMD ["php", "-r", "exit(@file_get_contents('http://127.0.0.1:8080/health') === false ? 1 : 0);"]

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/index.php"]
