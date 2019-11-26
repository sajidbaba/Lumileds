#!/bin/bash
set -e

echo $MYSQL_HOST
echo $MYSQL_PORT

rm -rf var/cache/*
bin/console cache:clear --env=prod --no-warmup
bin/console cache:warmup --env=prod
chown -R www-data:www-data var
bin/console doctrine:database:create --if-not-exists
bin/console doctrine:migration:migrate --no-interaction
bin/console rabbitmq-supervisor:rebuild

exec "$@"
