#!/bin/bash -x

mkdir -p /opt/nrfcfixtures/.yarn/cache
mkdir -p /opt/nrfcfixtures/.yarn/global
chown -R "${UID}:${GID}" /opt/nrfcfixtures/.yarn
chmod -R u+rwX /opt/nrfcfixtures/.yarn
yarn config set cache-folder /opt/nrfcfixtures/.yarn/cache
yarn config set global-folder /opt/nrfcfixtures/.yarn/global

ls -la
while [ -e .in_startup ]; do
  echo 'Encore waiting for start up to complete...'
  sleep 10
done
echo ">> Running yarn"
exec yarn install --ignore-prepare
echo ">> Watching..."
exec yarn watch
