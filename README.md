# NRFC Fixtures

Supports managing fixtures for Norwich Rugby Club, I could easily be repurposed for other clubs or sports.

## Set up to dev TL;DR

You will need:

 * [docker](https://docs.docker.com/get-started/introduction/get-docker-desktop/)
 * On Mac you'll need [brew](https://brew.sh/)
 * On windows you'll need bash, either [WSL](https://learn.microsoft.com/en-us/windows/wsl/install), [Git bash](https://git-scm.com/downloads)

```shell
git clone git@github.com:tobybatch/nrfc-fixtures.git
cd nrfc-fixtures
./bin/install-dependencies.sh
tilt up
```

Open the manager page in a browser, click on the fixtures container, and watch it build. It'll take a few minutes first time it runs. When it's up go to http://localhost:8001

 * Login users are [here](https://github.com/tobybatch/nrfc-fixtures/blob/main/src/DataFixtures/Users.php)
 * Changes in your local should be reflected in the site.

### Quick commands

**You must provide a compose file to run the following commands**

```shell
export COMPOSE_FILE=" -f .docker/compose.dev.yml"
```

Open a shell in the containers:

```shell
docker compose ${COMPOSE_FILE} exec nrfcfixtures bash
```

Reset the database (soft):

```shell
docker compose ${COMPOSE_FILE} exec nrfcfixtures bin/console doctrine:schema:drop --force
docker compose ${COMPOSE_FILE} exec nrfcfixtures bin/console doctrine:schema:create -n
docker compose ${COMPOSE_FILE} exec nrfcfixtures bin/console doctrine:fixtures:load -n
```

Reset the whole stack, keeps files, removes _all_ data/cache/etc:

```shell
docker compose ${COMPOSE_FILE} down
docker compose ${COMPOSE_FILE} volume rm nrfc-fixtures-dev-dbdata
rm -rf var/cache
tilt up # OR docker compose ${COMPOSE_FILE} up
```
