## Set up xdebug (native only ATM)

```shell
zend_extension=xdebug.so  # or xdebug.dll on Windows
xdebug.mode=debug
xdebug.start_with_request=trigger  # or "yes" if you want it always on
xdebug.client_port=9003  # Default is 9003 in Xdebug 3
xdebug.client_host=127.0.0.1
```

## Init/Reset the live DB

```shell
docker compose down
docker volume rm nrfc-fixtures-prod-dbdata
docker compose up -d
docker compose exec fixtures ./bin/console nrfc:fixtures:import -t club ./assets/clubs.csv
docker compose exec fixtures ./bin/console nrfc:fixtures:import ./assets/fixtures-youth-2025-6.csv
docker compose exec fixtures ./bin/console nrfc:fixtures:import ./assets/fixtures-senior-2025-6.csv
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

## Change log Incremental updates

```
git-cliff --latest -o CHANGELOG.md
```

## Releasing

```
git-cliff v1.0.0..HEAD -o CHANGELOG.md

```

## Dev bare bones tear up

```
docker compose down
docker compose up -d
./bin/console doctrine:schema:create

```