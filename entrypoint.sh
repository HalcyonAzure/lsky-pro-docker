#!/bin/bash
set -eu

envsubst '${APACHE_PORT}' < /etc/apache2/sites-enabled/000-default.conf.template > /etc/apache2/sites-enabled/000-default.conf
envsubst '${APACHE_PORT}' < /etc/apache2/ports.conf.template > /etc/apache2/ports.conf

if [ ! -e '/var/www/html/public/index.php' ]; then
    cp -a /var/www/lsky/* /var/www/html/
    cp -a /var/www/lsky/.env.example /var/www/html
fi
    chown -R www-data /var/www/html
    chgrp -R www-data /var/www/html
    chmod -R 755 /var/www/html/

exec "$@"
