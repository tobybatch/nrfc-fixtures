#!/usr/bin/env bash

if command -v brew > /dev/null 2>&1
then
    echo "==> Installing dependencies..."
    cd "$(dirname "$0")/.." || exit
    brew bundle > /dev/null 2>&1
else
    echo "==X brew not found, you'll need to install it, https://brew.sh/"
fi

if ! command -v commitlint > /dev/null 2>&1
then
    if [ "$SHELL" == "/bin/bash" ]; then
        if ! grep -q 'PATH=\$PATH:\$HOME/.cargo/bin' "$HOME/.bashrc"; then
            echo 'PATH=$PATH:$HOME/.cargo/bin' >>"$HOME/.bashrc"
        fi
    elif [ "$SHELL" == "/bin/zsh" ]; then
        if ! grep -q 'PATH=\$PATH:\$HOME/.cargo/bin' "$HOME/.zshrc"; then
            echo 'PATH=$PATH:$HOME/.cargo/bin' >>"$HOME/.zshrc"
        fi
    fi
fi

$(dirname $0)/install-dependencies.sh

echo "Dependencies installed"
echo

