#!/usr/bin/env bash

# Determines the UID of the user running the Docker containers.

if [ "$(uname)" == 'Linux' ]; then
  # We're on Linux. There's no virtualization, so we use our own UID.
  DOCKER_HOST_USER_ID="$(id -u)"
else
  # We're not on Linux. The User ID doesn't really matter on non-linux hosts as modern Docker allows any user within
  # the container to have full read/write access to mounted volumes. But to make it easier for users of this script
  # to work with the env var, we'll set this to something innocuous so it can still be passed to commands and used in
  # docker compose config files.
  DOCKER_HOST_USER_ID=1000
fi

export DOCKER_HOST_USER_ID
