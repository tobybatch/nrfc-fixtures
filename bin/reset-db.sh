#!/bin/bash

docker compose down
docker compose up -d
sleep 5
./bin/console doctrine:schema:create
./bin/console doctrine:fixtures:load --group=users -n
wget -O /dev/null http://localhost:7000/admin/clubs/initialise