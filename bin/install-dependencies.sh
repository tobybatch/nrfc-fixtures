#!/bin/bash

OPDIR="$(dirname "$0")"
OS_TYPE="$(uname -s)"

case "$OS_TYPE" in
    Darwin)
        echo "Running on macOS"
        "$OPDIR/install-dependencies-mac.sh"
        ;;
    Linux)
        echo "Running on Linux"
        "$OPDIR/install-dependencies-linux.sh"
        ;;
#    CYGWIN*|MINGW32*|MSYS*|MINGW*)
#        echo "Running on Windows"
#        "$OPDIR/install-dependencies-win.sh"
#        ;;
    *)
        echo "Unknown OS: $OS_TYPE"
        "$OPDIR/install-dependencies-unsupported.sh"
        ;;
esac

function ensurePath {
    TARGET=$HOME/$1
    if [ "$SHELL" == "/bin/bash" ]; then
        DOTFILE=$HOME/.bashrc
    elif [ "$SHELL" == "/bin/zsh" ]; then
        DOTFILE=$HOME/.zshrc
    fi

    resolved_path="$(realpath -m "$TARGET")"
    if [[ ":$PATH:" != *":$resolved_path:"* ]]; then
        # shellcheck disable=SC2016
        echo 'PATH=$PATH:$HOME/'$1 >> $DOTFILE
        export PATH=$PATH:$HOME/$1
    fi
}

echo "==> Installing dependencies..."

if ! command -v "commitlint" >/dev/null 2>&1; then
  echo "Installing commitlint"
  cargo install commitlint-rs
fi

if ! command -v "git-cliff" >/dev/null 2>&1; then
  echo "Installing git-cliff"
  cargo install git-cliff
fi

ensurePath .local/bin
ensurePath .cargo/bin

DOCKER_DIR=$(realpath "$OPDIR"/../.docker)
if [ ! -e "$DOCKER_DIR/dev.env" ]; then
  cp "$DOCKER_DIR/sample.dev.env" "$DOCKER_DIR/dev.env"
fi

echo "Dependencies installed"
$SHELL
