#!/bin/bash

export MYUID=$(id -u)
export MYGID=$(id -g):
docker compose -f $(dirname $0)/../.docker/compose.dev.yml up
