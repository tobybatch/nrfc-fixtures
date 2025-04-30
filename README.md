
## Set up xdebug

```
zend_extension=xdebug.so  # or xdebug.dll on Windows
xdebug.mode=debug
xdebug.start_with_request=trigger  # or "yes" if you want it always on
xdebug.client_port=9003  # Default is 9003 in Xdebug 3
xdebug.client_host=127.0.0.1
```

## Set uop the app

```
composer install
npm i
docker compose up -d
symfony serve
```

## Reset DB

```
./bin/console doctrine:schema:drop    # Not needed in intial set up
./bin/console doctrine:schema:create
./bin/console doctrine:fixtures:load
```

## Make a migration and run it

```
./bin/console make:migration
./bin/console doctrine:migrations:migrate
```

# TODO

 1. Fix route name
 2. Fix route mount points
3. check magic link detail in security.yaml, https://symfony.com/doc/current/security/login_link.html