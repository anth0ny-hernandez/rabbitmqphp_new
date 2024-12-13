<?php
require_once('rabbitMQLib.inc');

// Configuration
$localPath = "/home/yashmandal/git/deployment"; // Directory to store the pulled bundles
$deploymentServerUser = "yashmandal";      // Deployment server username
$deploymentServerIP = "172.22.217.86";    // Deployment server IP
$versionTrackerFile = "/home/yashmandal/test/rabbitmqphp_new/versionTracker.txt"; // Path for version tracker

// Function to clean up version number (remove any "v" prefix)
function cleanVersionNumber($versionNumber) {
    return ltrim($versionNumber, 'v');
}

// Function to pull the latest version using SCP
function pullVersion($versionNumber, $bundlePath) {
    global $localPath;

    echo "Pulling version $versionNumber from $bundlePath...\n";

    // SCP command to fetch the file
    $command = "scp $bundlePath $localPath";
    exec($command, $output, $status);

    if ($status === 0) {
        echo "Successfully pulled version $versionNumber.\n";
        return true;
    } else {
        echo "Failed to pull version $versionNumber.\n";
        return false;
    }
}

// Function to update the version tracker file
function updateVersionTracker($versionNumber) {
    global $versionTrackerFile;

    // Overwrite the file with the latest version
    $cleanedVersion = cleanVersionNumber($versionNumber);
    file_put_contents($versionTrackerFile, $cleanedVersion . "\n");
    echo "Replaced versionTracker.txt content with version: $cleanedVersion\n";
}

// Function to listen for the latest version deployment
function listenForLatestVersion() {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    while (true) {
        // Request the latest version
        $request = ["type" => "pullLatestVersion"];
        $response = $client->send_request($request);

        if ($response['success']) {
            $versionNumber = $response['version_number'];
            $bundlePath = $response['bundle_path'];

            // Update versionTracker.txt with the latest version
            updateVersionTracker($versionNumber);

            // Pull the latest version
            if (!pullVersion($versionNumber, $bundlePath)) {
                echo "Error: Failed to pull the latest version.\n";
            }
        } else {
            echo "Error: " . $response['message'] . "\n";
        }

        // Sleep before checking again (e.g., every 60 seconds)
        sleep(60);
    }
}

// Start listening
echo "Listening for the latest version...\n";
listenForLatestVersion();
