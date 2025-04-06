#!/usr/bin/env bash

if command -v brew > /dev/null 2>&1
then
    cd "$(dirname "$0")/.." || exit
    brew bundle > /dev/null 2>&1
else
    echo "Brew not found, you'll need to install it, https://brew.sh/"
fi
