#!/bin/bash -x

ls -la
while [ -e .in_startup ]; do
  echo 'Encore waiting for start up to complete...'
  sleep 10
done
echo ">> Running yarn"
exec yarn install --ignore-prepare
echo ">> Watching..."
exec yarn watch
