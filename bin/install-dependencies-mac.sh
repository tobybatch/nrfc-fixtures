#!/usr/bin/env bash

if command -v brew > /dev/null 2>&1
then
    echo "==> Installing dependencies..."
    cd "$(dirname "$0")/.." || exit
    brew bundle > /dev/null 2>&1
else
    echo "==X brew not found, you'llneed to install it, https://brew.sh/"
fi

if ! command -v cargo > /dev/null 2>&1
then
    echo 'PATH=$PATH:$HOME/.cargo/bin>>$HOME/.bashrc'
    echo 'PATH=$PATH:$HOME/.cargo/bin>>$HOME/.zshrc'
fi

echo "Dependencies installed"
echo

