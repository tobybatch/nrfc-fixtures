#!/bin/bash -x

OPDIR="$(dirname "$0")"
OS_TYPE="$(uname -s)"

echo "==> Installing dependencies..."

case "$OS_TYPE" in
    Darwin)
        echo "Running on macOS"
        "$OPDIR/_install-dependencies-mac.sh"
        ;;
    Linux)
        echo "Running on Linux"
        "$OPDIR/_install-dependencies-linux.sh"
        ;;
#    CYGWIN*|MINGW32*|MSYS*|MINGW*)
#        echo "Running on Windows"
#        "$OPDIR/install-dependencies-win.sh"
#        ;;
    *)
        echo "Unknown OS: $OS_TYPE"
        "$OPDIR/_install-dependencies-unsupported.sh"
        ;;
esac

# curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash

DOCKER_DIR=$(realpath "$OPDIR"/../.docker)
DOCKER_ENV="$DOCKER_DIR/dev.env"
COMPOSE_ENV="$DOCKER_DIR/.env"

if [ ! -e "$DOCKER_ENV" ]; then
  cp "$DOCKER_DIR/sample.dev.env" "$DOCKER_ENV"
fi

UID=$(id -u)
GID=$(id -g)

if [ ! -e "$COMPOSE_ENV" ]; then
  echo "UID=$UID" > "$COMPOSE_ENV"
  echo "GID=$GID" >> "$COMPOSE_ENV"
fi

sed -i 's/^UID=$/UID=$(id -u)/' "$COMPOSE_ENV"
sed -i 's/^GID=$/GID=$(id -g)/' "$COMPOSE_ENV"

touch .env
echo "Dependencies installed"
