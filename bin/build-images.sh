#!/bin/bash

if type realpath; then
  NRFC_DIR="$(realpath "$(dirname "$0")/..")"
else
  NRFC_DIR="$(dirname "$0")/.."
fi

docker build -t ghcr.io/tobybatch/nrfc-fixtures:fpm-prod --build-arg BASE=fpm "$NRFC_DIR"
docker build -t ghcr.io/tobybatch/nrfc-fixtures:apache-prod --build-arg BASE=fpm "$NRFC_DIR"
docker build -t ghcr.io/tobybatch/nrfc-fixtures:fpm-dev --target=dev --build-arg BASE=fpm "$NRFC_DIR"
docker build -t ghcr.io/tobybatch/nrfc-fixtures:apache-dev --target=dev --build-arg BASE=fpm "$NRFC_DIR"