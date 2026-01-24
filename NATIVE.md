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
wget https://get.symfony.com/cli/installer -O - | bash
```

```shell
cp env.local .env
composer install
yarn
screen -dmS yarn-watch yarn watch
docker compose -f compose.local.yaml up -d
./bin/console doctrine:schema:create -n
./bin/console doctrine:fixtures:load -n
cp .env.local .env
XDEBUG_MODE=debug symfony serve --listen-ip=0.0.0.0 --port=7000
```

Login users are [here](https://github.com/tobybatch/nrfc-fixtures/blob/main/src/DataFixtures/Users.php)
