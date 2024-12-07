#!/bin/bash

# Define the path to the directory where bundles are stored
BUNDLE_DIR="/home/yashmandal/git"

# Function to extract a specific version
extract_version() {
    local version=$1
    local current_dir=$(pwd)

    # Construct the bundle file name
    local bundle_file="$BUNDLE_DIR/repo-version-$version.tar.gz"

    # Check if the file exists
    if [[ -f "$bundle_file" ]]; then
        echo "Extracting version $version into $current_dir..."
        tar -xzf "$bundle_file" -C "$current_dir"
        echo "Version $version successfully extracted."
    else
        echo "Error: Version $version not found in $BUNDLE_DIR."
    fi
}

# Get the latest version from the versionTracker file
get_latest_version() {
    local version_tracker="$BUNDLE_DIR/versionTracker.txt"

    # Check if the versionTracker file exists
    if [[ -f "$version_tracker" ]]; then
        tail -n 1 "$version_tracker"
    else
        echo "Error: versionTracker.txt not found in $BUNDLE_DIR."
        exit 1
    fi
}

# Main script logic
echo "Would you like to extract the latest version (l) or a specific version (s)?"
read -r choice

if [[ "$choice" == "l" ]]; then
    latest_version=$(get_latest_version)
    if [[ -z "$latest_version" ]]; then
        echo "Error: Could not determine the latest version."
        exit 1
    fi
    extract_version "$latest_version"
elif [[ "$choice" == "s" ]]; then
    echo "Enter the version number to extract:"
    read -r version
    extract_version "$version"
else
    echo "Invalid choice. Exiting..."
    exit 1
fi
