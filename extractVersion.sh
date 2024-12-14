#!/bin/bash

# Define the path to the directory where bundles are stored
BUNDLE_DIR="/home/yashmandal/git/deployment"

# Function to extract a specific version
extract_version() {
    local version=$1
    local current_dir=$(pwd)

    # Construct the bundle file name
    local bundle_file="$BUNDLE_DIR/$bundle_type-version-$version.tar.gz"

    # Check if the file exists
    if [[ -f "$bundle_file" ]]; then
        echo "Extracting $bundle_type version $version into $current_dir..."
        tar -xzf "$bundle_file" -C "$current_dir"
        echo "Version $version successfully extracted."

        # Restart services based on the bundle type
        case "$bundle_type" in
            frontend)
                echo "Restarting Apache service for frontend..."
                sudo systemctl restart apache2
                echo "Apache service restarted successfully."
                ;;
            server)
                echo "Restarting RabbitMQ server for backend..."
                sudo systemctl restart rabbitmq-server
                echo "RabbitMQ server restarted successfully."
                ;;
            dmz)
                echo "Restarting DMZ services (custom logic for your application)..."
                # Replace with actual service restart command if applicable
                echo "DMZ services restarted successfully."
                ;;
            *)
                echo "Unknown bundle type. No services restarted."
                ;;
        esac
    else
        echo "Error: Version $version not found in $BUNDLE_DIR."
    fi
}

# Get the latest version from the versionTracker file
get_latest_version() {
    local version_tracker="/home/yashmandal/test/rabbitmqphp_new/versionTracker.txt"

    # Check if the versionTracker file exists
    if [[ -f "$version_tracker" ]]; then
        tail -n 1 "$version_tracker"
    else
        echo "Error: versionTracker.txt not found in $BUNDLE_DIR."
        exit 1
    fi
}

# Main script logic
echo "Choose the type of bundle to extract:"
echo "1. frontend"
echo "2. server"
echo "3. dmz"
read -p "Enter the number corresponding to your choice: " bundle_choice

# Map the choice to the bundle type
case "$bundle_choice" in
    1)
        bundle_type="frontend"
        ;;
    2)
        bundle_type="server"
        ;;
    3)
        bundle_type="dmz"
        ;;
    *)
        echo "Invalid choice. Exiting..."
        exit 1
        ;;
esac

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
