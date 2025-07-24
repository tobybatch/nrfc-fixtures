# Running native (no Docker)

## Deps

 * git
 * rust
 * commitlint-rs
 * PHP 8.3
 * composer
 * symfony-cli
 * postgres
 * yarn

## Process

```shell
git clone git@github.com:tobybatch/nrfc-fixtures.git
cd nrfc-fixtures
composer install
yarn
yarn watch
docker compose up -d
XDEBUG_MODE=debug symfony serve --listen-ip=0.0.0.0 --port=7000
./bin/console doctrine:schema:create -n
./bin/console doctrine:fixtures:load -n
```

Login users are [here](https://github.com/tobybatch/nrfc-fixtures/blob/main/src/DataFixtures/Users.php)
