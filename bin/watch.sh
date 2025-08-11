#!/bin/bash -x

mkdir -p /opt/nrfcfixtures/.home
chown -R "${UID}:${GID}" /opt/nrfcfixtures/.home
chmod -R u+rwX /opt/nrfcfixtures/.home
export HOME=/opt/nrfcfixtures/.home

# Set Yarn cache and global folders
mkdir -p /opt/nrfcfixtures/.yarn/cache
mkdir -p /opt/nrfcfixtures/.yarn/global
chown -R "${UID}:${GID}" /opt/nrfcfixtures/.yarn
chmod -R u+rwX /opt/nrfcfixtures/.yarn

# Configure Yarn to use the custom directories
yarn config set cache-folder /opt/nrfcfixtures/.yarn/cache --home-dir /opt/nrfcfixtures/.home
yarn config set global-folder /opt/nrfcfixtures/.yarn/global --home-dir /opt/nrfcfixtures/.home
yarn config set rc-path /opt/nrfcfixtures/.home/.yarnrc --home-dir /opt/nrfcfixtures/.home

while [ -e .in_startup ]; do
  echo 'Encore waiting for start up to complete...'
  sleep 10
done
echo ">> Running yarn"
yarn install #--ignore-prepare
echo ">> Watching..."
exec yarn watch
