# Cargo and then commitlint-rs
if ! command -v "cargo" >/dev/null 2>&1; then
    echo "You are missing core dependencies.  Execute the following and re-run this script."
    echo "sudo apt install cargo"
    exit 1
fi

# Install tilt

if ! command -v "tilt" >/dev/null 2>&1; then
  TARGET=$HOME/.local/bin
  resolved_path="$(realpath -m "$TARGET")"
  if [[ ":$PATH:" != *":$resolved_path:"* ]]; then
      # shellcheck disable=SC2016
      echo 'PATH=$PATH:$HOME/.local/bin' >> ~/.bashrc
      export PATH=$PATH:$HOME/.local/bin
  fi
fi

curl -fsSL https://raw.githubusercontent.com/tilt-dev/tilt/master/scripts/install.sh | bash


"$(dirname $0)/install-dependencies.sh"