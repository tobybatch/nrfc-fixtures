#!/bin/bash

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

if [ ! -e "$DOCKER_ENV" ]; then
  cp "$DOCKER_DIR/sample.dev.env" "$DOCKER_ENV"
fi

USER_ID=$(id -u)
GROUP_ID=$(id -g)

if ! grep -q "\bUSER_ID\b" "$DOCKER_ENV"; then
    echo "USER_ID=$USER_ID" >> "$DOCKER_ENV"
fi

if ! grep -q "\bGROUP_ID\b" "$DOCKER_ENV"; then
    echo "GROUP_ID=$GROUP_ID" >> "$DOCKER_ENV"
fi

touch .env
echo "Dependencies installed"
