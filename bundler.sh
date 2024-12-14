#!/bin/bash

# Prompt the user to choose a name for the bundle
echo "Choose the type of bundle:"
echo "1. frontend"
echo "2. server"
echo "3. dmz"
read -p "Enter the number corresponding to your choice: " bundleChoice

# Map the choice to the bundle name
case $bundleChoice in
    1)
        bundleName="frontend"
        ;;
    2)
        bundleName="server"
        ;;
    3)
        bundleName="dmz"
        ;;
    *)
        echo "Invalid choice. Exiting..."
        exit 1
        ;;
esac

# Find the latest version number tracked and add 1 to it
versionTrackerFile="/home/yashmandal/test/rabbitmqphp_new/versionTracker.txt"
latestVersionNum=$(tail -n 1 "$versionTrackerFile")

# Convert string to number and increment
newVersionNum=$(($latestVersionNum + 1))
echo "New version number: $newVersionNum"

# Overwrite the versionTracker file with the new version number
echo $newVersionNum > "$versionTrackerFile"

# Prompt for file paths to bundle if no arguments are given
if [ $# -eq 0 ]; then
    read -p "Enter the full paths of files to bundle, separated by spaces: " inputPaths
    set -- $inputPaths
fi

# Create the tar.gz bundle with the chosen name and version
bundleFileName="${bundleName}-version-${newVersionNum}.tar.gz"
tar --transform='s|.*/||' -czf $bundleFileName "$@"  # Remove directory structure

# Create SCP command to send the bundle to the deployment server
scp $bundleFileName yashmandal@172.22.217.86:/home/yashmandal/git/deployment

# Trigger deployment script
php "/home/yashmandal/test/rabbitmqphp_new/triggerDeployment2.php"

echo "Bundling and deployment process completed for $bundleName (version $newVersionNum)."
