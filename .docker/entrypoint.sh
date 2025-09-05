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
if [ ! -e .env ]; then
  touch .env
fi

if [ "$APP_ENV" == "dev" ]; then
    composer install
fi

/opt/nrfcfixtures/bin/console cache:clear
/opt/nrfcfixtures/bin/console doctrine:migrations:migrate --no-interaction

if [ "$APP_ENV" == "dev" ]  && [ "$LOAD_FIXTURES" == 'true' ]; then
    /opt/nrfcfixtures/bin/console doctrine:fixtures:load -n
else
    echo "skipping fixtures as LOAD_FIXTURES is absent"
fi

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
