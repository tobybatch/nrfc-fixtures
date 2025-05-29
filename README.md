
## Set up xdebug

```
zend_extension=xdebug.so  # or xdebug.dll on Windows
xdebug.mode=debug
xdebug.start_with_request=trigger  # or "yes" if you want it always on
xdebug.client_port=9003  # Default is 9003 in Xdebug 3
xdebug.client_host=127.0.0.1
```

## Tooling

```
curl -o .git/hooks/pre-commit https://raw.githubusercontent.com/tobybatch/bashdotdee/refs/heads/main/bin/commit-lint.sh
chmod 755 .git/hooks/pre-commit
```

## Set up the app

```
composer install
npm i
docker compose up -d
symfony serve --listen-ip=0.0.0.0 --port=7000
```

## Reset DB

```
./bin/console doctrine:schema:drop --force   # Not needed in intial set up
./bin/console doctrine:schema:create -n
./bin/console doctrine:fixtures:load -n
```

## Init/Reset the live DB

```
docker compose down
docker volume rm nrfc-fixtures-prod-dbdata
docker compose up -d
docker compose exec fixtures ./bin/console nrfc:fixtures:import -s -t club ./assets/clubs.csv
docker compose exec fixtures ./bin/console nrfc:fixtures:import -s ./assets/fixtures.csv
```

## Make a migration and run it

```
./bin/console make:migration
./bin/console doctrine:migrations:migrate
```

## Panther

```
sudo apt install chromium-browser chromium-chromedriver firefox
# or
vendor/bin/bdi detect drivers
```

## Tests

```
XDEBUG_MODE=coverage ./vendor/bin/phpunit 
```
# TODO

 1. Fix route name
 2. check magic link detail in security.yaml, https://symfony.com/doc/current/security/login_link.html
