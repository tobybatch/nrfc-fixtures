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

echo "==> Installing dependencies..."

# curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash

DOCKER_DIR=$(realpath "$OPDIR"/../.docker)
if [ ! -e "$DOCKER_DIR/dev.env" ]; then
  cp "$DOCKER_DIR/sample.dev.env" "$DOCKER_DIR/dev.env"
fi

touch .env
echo "Dependencies installed"
