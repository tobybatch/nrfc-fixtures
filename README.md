
## Set up xdebug

```shell
zend_extension=xdebug.so  # or xdebug.dll on Windows
xdebug.mode=debug
xdebug.start_with_request=trigger  # or "yes" if you want it always on
xdebug.client_port=9003  # Default is 9003 in Xdebug 3
xdebug.client_host=127.0.0.1
```

## Tooling

```shell
curl -o .git/hooks/pre-commit https://raw.githubusercontent.com/tobybatch/bashdotdee/refs/heads/main/bin/commit-lint.sh
chmod 755 .git/hooks/pre-commit
```

## Set up the app

```shell
composer install
npm i
docker compose up -d
symfony serve --listen-ip=0.0.0.0 --port=7000
```

## Reset DB

```shell
./bin/console doctrine:schema:drop --force   # Not needed in intial set up
./bin/console doctrine:schema:create -n
./bin/console doctrine:fixtures:load -n
```

## Init/Reset the live DB

```shell
docker compose down
docker volume rm nrfc-fixtures-prod-dbdata
docker compose up -d
docker compose exec fixtures ./bin/console nrfc:fixtures:import -s -t club ./assets/clubs.csv
docker compose exec fixtures ./bin/console nrfc:fixtures:import -s ./assets/fixtures.csv
```

## Make a migration and run it

```shell
./bin/console make:migration
./bin/console doctrine:migrations:migrate
```

## Panther

```shell
sudo apt install chromium-browser chromium-chromedriver firefox
# or
vendor/bin/bdi detect drivers
```

## Tests

```shell
XDEBUG_MODE=coverage ./vendor/bin/phpunit 
```

## Docker

### Build images

```shell
./bin/build-images.sh
```

### Set up envs to run dockers locally

#### dev

```shell
if [ ! -e '.docker/dev.env' ]; then cp .docker/sample.dev.env .docker/dev.env; fi
export COMPOSE_FILE=.docker/compose.dev.yml
```

#### prod

```shell
if [ ! -e '.docker/prod.env' ]; then cp .docker/sample.prod.env .docker/prod.env; fi
export COMPOSE_FILE=" -f .docker/compose.prod.yml"
```

#### Run the service

```shell
docker compose ${COMPOSE_FILE} up -d
docker compose ${COMPOSE_FILE} exec fixtures bin/console cache:clear
```

#### Reset to fixture IRREVERSIBLY DESTRUCTIVE

```shell
docker compose ${COMPOSE_FILE} exec fixtures bin/console doctrine:schema:drop
docker compose ${COMPOSE_FILE} exec fixtures bin/console doctrine:schema:create
docker compose ${COMPOSE_FILE} exec fixtures ./bin/console nrfc:fixtures:import -s -t club ./assets/clubs.csv
docker compose ${COMPOSE_FILE} exec fixtures ./bin/console nrfc:fixtures:import -s ./assets/fixtures.csv
```

#### Open a shell

```shell
docker compose ${COMPOSE_FILE} exec fixtures bash
```

# TODO

/bin/console doctrine:query:sql "update users set roles='[\"ROLE_EDITOR\"]' where email='toby@nfn.org.uk'"

1. Fix route names
2. check magic link detail in security.yaml, https://symfony.com/doc/current/security/login_link.html
3. Admin screens
4. Remove encore js from prod deps, build assets as part of the prod build