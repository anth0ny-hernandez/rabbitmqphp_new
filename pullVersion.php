<?php
require_once('rabbitMQLib.inc');

// Function to pull the latest version
function pullLatestVersion() {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    // Request the latest version
    $request = ["type" => "pullLatestVersion"];
    $response = $client->send_request($request);

    if ($response['success']) {
        echo "Latest version: " . $response['version_number'] . "\n";
        echo "Pulling from: " . $response['bundle_path'] . "\n";

        // Use SCP to pull the bundle
        $bundlePath = $response['bundle_path'];
        $localPath = "/home/yashmandal/git/deployment"; // Update with your environment path

        $command = "scp yashmandal@172.22.217.86:$bundlePath $localPath";
        exec($command, $output, $status);

        if ($status === 0) {
            echo "Successfully pulled latest version.\n";
        } else {
            echo "Failed to pull latest version.\n";
        }
    } else {
        echo "Error: " . $response['message'] . "\n";
    }
}

// Function to pull a specific version
function pullSpecificVersion($versionNumber) {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    // Request a specific version
    $request = [
        "type" => "pullSpecificVersion",
        "version_number" => $versionNumber
    ];
    $response = $client->send_request($request);

    if ($response['success']) {
        echo "Version: " . $response['version_number'] . "\n";
        echo "Pulling from: " . $response['bundle_path'] . "\n";

        // Use SCP to pull the bundle
        $bundlePath = $response['bundle_path'];
        $localPath = "/home/yashmandal/git/deployment"; // Update with your environment path

        $command = "scp yashmandal@172.22.217.86:$bundlePath $localPath";
        exec($command, $output, $status);

        if ($status === 0) {
            echo "Successfully pulled version $versionNumber.\n";
        } else {
            echo "Failed to pull version $versionNumber.\n";
        }
    } else {
        echo "Error: " . $response['message'] . "\n";
    }
}

// Usage example:
// Uncomment one of the following to test:

// Pull the latest version
//pullLatestVersion();

// Pull a specific version (replace 'v1.0.0' with the actual version number)
pullSpecificVersion('v2.0.0');
