if ! command -v "commitlint" >/dev/null 2>&1; then
  echo "Installing commitlint"
  cargo install commitlint-rs
fi

if [ "$SHELL" == "/bin/bash" ]; then
    source $HOME/.bashrc
elif [ "$SHELL" == "/bin/zsh" ]; then
    source $HOME/.zshrc
fi
