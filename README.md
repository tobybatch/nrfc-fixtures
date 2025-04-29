
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

## https://symfony.com/doc/7.0/security.html

## Next:

 1) In RegistrationController::verifyUserEmail():
    * Customize the last redirectToRoute() after a successful email verification.
    * Make sure you're rendering success flash messages or change the $this->addFlash() line.
 2) Review and customize the form, controller, and templates as needed.
 3) Run "php bin/console make:migration" to generate a migration for the newly added User::isVerified property.

 Then open your browser, go to "/register" and enjoy your new form!
