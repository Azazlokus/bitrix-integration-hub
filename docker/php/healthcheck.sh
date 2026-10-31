#!/bin/sh
# Ping php-fpm по FastCGI (ping.path в fpm-pool.conf)
SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET \
    cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong
