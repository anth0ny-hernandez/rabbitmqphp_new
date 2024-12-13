<?php
require_once('rabbitMQLib.inc');

// Configuration
$localPath = "/home/yashmandal/git/deployment"; // Update this to the environment's directory
$deploymentServerUser = "yashmandal";      // Deployment server username
$deploymentServerIP = "172.22.217.86";    // Deployment server IP
$localVersionFile = $localPath . "/current_version.txt"; // File to track the current version
$versionTrackerFile = "/home/yashmandal/test/rabbitmqphp_new/versionTracker.txt"; // Update to use the correct path

// Function to clean up version number (remove any "v" prefix)
function cleanVersionNumber($versionNumber) {
    return ltrim($versionNumber, 'v');
}

// Function to pull the latest version using SCP
function pullVersion($versionNumber, $bundlePath) {
    global $localPath, $deploymentServerUser, $deploymentServerIP, $localVersionFile;

    echo "Pulling version $versionNumber from $bundlePath...\n";

    // SCP command to fetch the file
    $command = "scp $deploymentServerUser@$deploymentServerIP:$bundlePath $localPath";
    exec($command, $output, $status);

    if ($status === 0) {
        echo "Successfully pulled version $versionNumber.\n";
        
        // Update the local version file
        file_put_contents($localVersionFile, $versionNumber);

        return true;
    } else {
        echo "Failed to pull version $versionNumber.\n";
        return false;
    }
}

// Function to update the version tracker file
function updateVersionTracker($versionNumber) {
    global $versionTrackerFile;

    // Always update the file with the latest version
    $cleanedVersion = cleanVersionNumber($versionNumber);
    file_put_contents($versionTrackerFile, $cleanedVersion . "\n", FILE_APPEND);
    echo "Updated versionTracker.txt with version: $cleanedVersion\n";
}

// Function to get the currently deployed version locally
function getCurrentVersion() {
    global $localVersionFile;

    if (file_exists($localVersionFile)) {
        return trim(file_get_contents($localVersionFile));
    }
    return null;
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

            // Always update versionTracker.txt, regardless of whether the latest version is already deployed locally
            updateVersionTracker($versionNumber);

            // Check if the latest version is already deployed locally
            $currentVersion = getCurrentVersion();
            if ($currentVersion === $versionNumber) {
                echo "Version $versionNumber is already deployed locally. Skipping pull.\n";
            } else {
                // Pull the latest version
                if (!pullVersion($versionNumber, $bundlePath)) {
                    echo "Error: Failed to pull the latest version.\n";
                }
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
