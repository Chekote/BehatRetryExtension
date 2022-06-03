#!/usr/bin/env bash

set -euo pipefail

expected_version=2.3.6

#############################################
# Download the sha256-sum file from composer page for the expected version
#
# Globals:
#   $expected_version the expected composer version to install/validate
#
#############################################
downloadComposerSHA() {
  echo "Downloading SHA for Composer $expected_version"

  # Download the original sha256 sum for the expected version
  curl -L https://getcomposer.org/download/$expected_version/composer.phar.sha256sum > ./bin/composer.sha256sum
}

#############################################
# Delete the downloaded sha256-sum file
#############################################
clearComposerSHA() {
  rm ./bin/composer.sha256sum &> /dev/null || TRUE
}

#############################################
# Validate the sha256sum against the existing composer file
#
# Returns:
#   1 when the validation pass
#   0 when the sha validation fail
#
#############################################
isValidComposerSHA() {
  local parsed_sha256_sum
  local sha256_sum
  local validation_result

  echo "Validating SHA"

  sha256_sum=$(cat ./bin/composer.sha256sum)
  # The file includes sha256 string and file name with phar extension
  # The next method replaces the .phar from the string
  parsed_sha256_sum="${sha256_sum/ composer.phar/}"
  validation_result=$(echo "$parsed_sha256_sum ./bin/composer" | shasum -c -a 256) || TRUE
  if [ "$validation_result" == "./bin/composer: OK" ]; then
    echo "SHA matches"
    return
  fi

    echo "SHA does not match"
  false
}

#############################################
# Check if the project already have a composer installed and
# if exists validates the file sha256sum match the expected version
#
# Globals:
#   $expected_version the expected composer version to install/validate
#
#############################################
checkExistingInstallation() {
  if [ ! -e ./bin/composer ]; then
    echo "No existing Composer install found"
  else
    local found_version

    found_version=$(./bin/composer --version | tail -1 | awk '{print $3}')

    if isValidComposerSHA; then
        # Version is as expected. We're done.
        echo "Composer $expected_version already installed."
        clearComposerSHA
        exit 0
    else
        # Version does not match. Remove it.
        echo "Removing Composer version $found_version..."
        rm ./bin/composer
    fi
  fi
}

#############################################
# Download the desired composer binary
#
# Globals:
#   $expected_version the expected composer version to install/validate
#############################################
downloadComposerBinary() {
  # Install composer
  echo "Installing Composer $expected_version..."
  curl -L https://getcomposer.org/download/$expected_version/composer.phar > ./bin/composer
}

#############################################
# Validate the sha256sum of the downloaded file
#############################################
validateDownloadedComposer() {
  echo "Checking the SHA for the downloaded file..."
  if isValidComposerSHA; then
      clearComposerSHA
  else
      echo "ERROR: composer's sha256sum doesn't match. Download it again."
      rm ./bin/composer
      clearComposerSHA
      exit 1
  fi
}

#############################################
# Fix execution permissions
#############################################
fixComposerPermissions() {
  echo "Setting composer permissions..."
  chmod 755 ./bin/composer
}

#############################################
# Ensure ~/.composer exists for the Docker container to mount
#############################################
initializeComposerDir() {
  echo "Initializing ~/.composer directory..."
  if [ ! -d ~/.composer ]; then
      mkdir ~/.composer
  fi
}

# Download sha256sum for the expected version
downloadComposerSHA
# Check for existing installation
checkExistingInstallation
# Download composer
downloadComposerBinary
# Validate downloaded file sha256sum match with the expected version sha256sum
validateDownloadedComposer
# Fix permissions
fixComposerPermissions
# Initialize .composer directory
initializeComposerDir

echo "Composer v$expected_version installation complete."
