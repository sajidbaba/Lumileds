#!/bin/bash
set -e

until nc -z $MYSQL_HOST $MYSQL_PORT; do
    echo "$(date) - waiting for mysql..."
    sleep 1
done

composer install

chown -R www-data:www-data var

php bin/console doctrine:database:create --if-not-exists -n || echo "DB creation failed"
php bin/console doctrine:migrations:migrate -n || echo "Migrations failed"
php bin/console rabbitmq-supervisor:rebuild || echo "Fixtures to rebuild supervisor config"

exec "$@"
