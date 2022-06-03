#!/usr/bin/env bash

# Determines the UID of the user running the Docker containers.

if [ "$(uname)" == 'Darwin' ]; then
  # We're on Mac OS  X. We're Virtualized using xhyve, and have a UID of 1000.
  DOCKER_HOST_USER_ID=1000
else
  # We're on Linux. There's no virtualization, so we use our own UID.
  DOCKER_HOST_USER_ID="$(id -u)"
fi

export DOCKER_HOST_USER_ID
