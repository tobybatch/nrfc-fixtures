#!/bin/bash -x

NRFCFIXTURES=$(cat /opt/nrfcfixtures/version.txt)

touch .in_startup
echo "Wait for database connection ..."
until php /dbtest.php "$DATABASE_URL"; do
  echo Checking DB: $?
  sleep 3
done
echo "Connection established"

cp /assets/monolog.yaml /opt/nrfcfixtures/config/packages/monolog.yaml
touch .env
/opt/nrfcfixtures/bin/console cache:clear
composer install
/opt/nrfcfixtures/bin/console doctrine:migrations:migrate --no-interaction

if [ "$APP_ENV" == "dev" ]; then
    bin/console doctrine:schema:create -n
    bin/console doctrine:fixtures:load -n
fi

yarn build

echo "$NRFCFIXTURES" > /opt/nrfcfixtures/var/installed
echo "NRFC Fixtures is ready"
rm -f .in_startup
if [ -e /use_apache ]; then
  exec apache2-foreground
elif [ -e /use_fpm ]; then
  exec php-fpm
else
  echo "Error, unknown server type"
fi
