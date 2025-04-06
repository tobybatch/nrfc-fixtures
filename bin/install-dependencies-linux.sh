# Rust up
if ! command -v "rustup" >/dev/null 2>&1; then
    echo "You are missing core dependencies.  I'll need root to run:"
    echo "    sudo apt install rustup"
    sudo apt install rustup
fi

rustup default stable
curl -fsSL https://raw.githubusercontent.com/tilt-dev/tilt/master/scripts/install.sh | bash
