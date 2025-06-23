# NRFC Fixtures

Supports managing fixtures for Norwich Rugby Club, I could easily be repurposed for other clubs or sports.

## Setting up for dev

See [SETUP.md](./SETUP.md)

## Updating in prod

It runs on the NRFC web host. To update ti just pull the new image and re-start the docker.

```
docker compose pull
docker compose up -d
```

## Deploying to a new prod

May need `sudo`

```
DEPLOY_TARGET=/opt/dockers/fixtures
mkdir -p $DEPLOY_TARGET
cd $DEPLOY_TARGET
wget -O compse.yaml https://raw.githubusercontent.com/tobybatch/nrfc-fixtures/refs/heads/main/.docker/compose.prod.yml
docker compose up -d
```

### Importing from CSV

You may need to copy in or mount the folder containing the assets.

```
docker compose exec fixtures ./bin/console nrfc:fixtures:import -s -t club ./assets/clubs.csv
docker compose exec fixtures ./bin/console nrfc:fixtures:import -s ./assets/fixtures.csv
```