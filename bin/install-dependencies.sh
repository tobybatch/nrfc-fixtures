cargo -q install commitlint-rs

if [ "$SHELL" == "/bin/bash" ]; then
    source $HOME/.bashrc
elif [ "$SHELL" == "/bin/zsh" ]; then
    source $HOME/.zshrc
fi
