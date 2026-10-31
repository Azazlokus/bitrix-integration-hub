# Общий обработчик PHP для server-блоков (include внутри location ~ \.php$)
include fastcgi_params;
fastcgi_pass php:9000;
fastcgi_index index.php;
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
fastcgi_param HTTPS $lab_fcgi_https if_not_empty;  # см. map в webhooks.conf
fastcgi_param HTTP_PROXY "";   # httpoxy
fastcgi_read_timeout 300;
fastcgi_buffers 16 32k;
fastcgi_buffer_size 64k;
