# Get the current user's UID and GID
uid = os.getenv("USER_ID", "1000")  # Fallback to 1000 if not set
gid = os.getenv("GROUP_ID", "1000")  # Fallback to 1000 if not set

docker_compose(
    "./.docker/compose.dev.yml",
    env={
        "USER_ID": uid,
        "GROUP_ID": gid,
    }
)

