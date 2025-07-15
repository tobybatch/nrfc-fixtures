#!/bin/bash -x

NRFCFIXTURES=$(cat /opt/nrfcfixtures/version.txt)

function waitForDB() {
  echo "Wait for database connection ..."
  until php /dbtest.php "$DATABASE_URL"; do
    echo Checking DB: $?
    sleep 3
  done
  echo "Connection established"
}

function setMemoryLimit() {
  # set mem limits and copy in custom logger config
  if [ -z "$memory_limit" ]; then
    memory_limit=512M
  fi
  sed -i "s/memory_limit.*/memory_limit=$memory_limit/g" /usr/local/etc/php/php.ini
}

function createUserAndGroup() {
  if [ -z "$USER_ID" ]; then
    USER_ID=$(id -u www-data)
  fi
  if [ -z "$GROUP_ID" ]; then
    GROUP_ID=$(id -g www-data)
  fi

  # Create group if needed
  if grep -w "$GROUP_ID" /etc/group &>/dev/null; then
    echo "Group already exists"
  else
    echo "www-nrfcfixtures:x:$GROUP_ID:" >> /etc/group
    grpconv
  fi

  # Create user if needed
  if getent passwd "$USER_ID" &>/dev/null; then
    echo "User already exists"
  else
    echo "www-nrfcfixtures:x:$USER_ID:$GROUP_ID:www-nrfcfixtures:/var/www:/usr/sbin/nologin" >> /etc/passwd
    pwconv
  fi

  # Return the username for the UID
  getent passwd "$USER_ID" | cut -d: -f1
}

function configureWebserver() {
  if [ -e /use_apache ]; then
    export APACHE_RUN_USER=$(id -nu "$USER_ID")
    # This doesn't _exactly_ run as the specified GID, it runs as the GID of the specified user but WTF
    export APACHE_RUN_GROUP=$(id -ng "$USER_ID")
    export APACHE_PID_FILE=/var/run/apache2/apache2.pid
    export APACHE_RUN_DIR=/var/run/apache2
    export APACHE_LOCK_DIR=/var/lock/apache2
    export APACHE_LOG_DIR=/var/log/apache2
    export LANG=C
  elif [ -e /use_fpm ]; then
    sed -i "s/user = .*/user = $USER_ID/g" /usr/local/etc/php-fpm.d/www.conf
    sed -i "s/group = .*/group = $GROUP_ID/g" /usr/local/etc/php-fpm.d/www.conf
    echo "Setting fpm to run as ${USER_ID}:${GROUP_ID}"
  else
    echo "Error, unknown server type"
  fi
}

function tweakSymfony() {
  cp /assets/monolog.yaml /opt/nrfcfixtures/config/packages/monolog.yaml
  touch .env
  /opt/nrfcfixtures/bin/console cache:clear
  composer install
  /opt/nrfcfixtures/bin/console doctrine:migrations:migrate --no-interaction

  if [ "$APP_ENV" == "dev" ]; then
      bin/console doctrine:schema:create -n
      bin/console doctrine:fixtures:load -n
  fi
}

function runServer() {
  echo "$NRFCFIXTURES" > /opt/nrfcfixtures/var/installed
  echo "NRFC Fixtures is ready"
  if [ -e /use_apache ]; then
    exec /usr/sbin/apache2 -D FOREGROUND
  elif [ -e /use_fpm ]; then
    exec php-fpm
  else
    echo "Error, unknown server type"
  fi
}

touch .in_startup
# needs root
setMemoryLimit
createUserAndGroup
configureWebserver

# run as user
DELEGATED_USER=$(createUserAndGroup)
su - "$USERNAME" -c waitForDB
su - "$USERNAME" -c tweakSymfony
rm -f .in_startup
su - "$USERNAME" -c runServer
