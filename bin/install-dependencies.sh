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
ENV_FILE="$DOCKER_DIR/../.env"

if [ -e "$ENV_FILE" ]; then
  mv "$ENV_FILE" "$ENV_FILE.$(date +%Y%m%m-%H%M%S)"
fi
cp "$DOCKER_DIR/sample.dev.env" "$ENV_FILE"

USER_UID=$(id -u)
USER_GID=$(id -g)

echo "Detected UID: $USER_UID"
echo "Detected GID: $USER_GID"

# Determine sed command based on OS
if [[ "$OS_TYPE" == "Darwin" ]]; then
    SED_IN_PLACE=(-i '')
else
    SED_IN_PLACE=(-i)
fi

# Update or add UID in .env
if grep -q "^UID=" "$ENV_FILE"; then
    sed "${SED_IN_PLACE[@]}" "s/^UID=.*/UID=$USER_UID/" "$ENV_FILE"
else
    echo "UID=$USER_UID" >> "$ENV_FILE"
fi

# Update or add GID in .env
if grep -q "^GID=" "$ENV_FILE"; then
    sed "${SED_IN_PLACE[@]}" "s/^GID=.*/GID=$USER_GID/" "$ENV_FILE"
else
    echo "GID=$USER_GID" >> "$ENV_FILE"
fi

echo "✅ UID and GID have been updated in $ENV_FILE"
echo "Dependencies installed"
