#!/bin/bash
echo $$ > /tmp/tailwind-watch.pid
while [ -e .in_startup ]; do
  echo 'Encore waiting for start up to complete...'
  sleep 1
done
exec npm run watch
