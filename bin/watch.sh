#!/bin/bash
echo $$ > /tmp/tailwind-watch.pid
exec npm run watch
