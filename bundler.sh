#!/bin/bash

# Define the path to the version tracker file
VERSION_TRACKER="../rabbitmqphp_new/versionTracker.txt"

# Check if the version tracker exists
if [[ ! -f "$VERSION_TRACKER" ]]; then
    echo "Version tracker file not found: $VERSION_TRACKER"
    exit 1
fi

# Find the latest version number from the tracker and increment it
latestVersionNum=$(tail -n 1 "$VERSION_TRACKER")
newVersionNum=$((latestVersionNum + 1))

# Append the new version number to the tracker
echo $newVersionNum >> "$VERSION_TRACKER"

# Define the output tar file name
tarFile="myRepo-${newVersionNum}.tar.gz"
echo "Creating tarball: $tarFile"

# Check if arguments are provided
if [[ $# -eq 0 ]]; then
    echo "No specific files provided. Zipping up the entire repository..."
    repoPath="../rabbitmqphp_new" # Update this to the full path of your repository if needed
    tar -czf "$tarFile" -C "$repoPath" .
else
    echo "Zipping up specified files..."
    # Bundle the specified files only (without directory structure)
    tar -czf "$tarFile" --transform='s!.*/!!' "$@"
fi

if [[ $? -eq 0 ]]; then
    echo "Successfully created tarball: $tarFile"
else
    echo "Error creating tarball. Exiting."
    exit 1
fi

# Transfer the tarball to the deployment server
deploymentPath="/home/yashmandal/git/deployment"
scp "$tarFile" yashmandal@172.22.217.86:"$deploymentPath"

if [[ $? -eq 0 ]]; then
    echo "Successfully transferred $tarFile to deployment server."
else
    echo "Error transferring tarball to deployment server."
    exit 1
fi

# Trigger deployment on the server
php "/home/yashmandal/test/rabbitmqphp_new/triggerDeployment2.php"

if [[ $? -eq 0 ]]; then
    echo "Deployment triggered successfully."
else
    echo "Error triggering deployment."
    exit 1
fi
