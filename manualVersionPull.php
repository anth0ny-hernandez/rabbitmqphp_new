<?php
require_once('rabbitMQLib.inc');

// Configuration
$localPath = "/path/to/qa-or-production/"; // Update this to the environment's directory
$deploymentServerUser = "yashmandal";      // Deployment server username
$deploymentServerIP = "172.22.217.86";    // Deployment server IP

// Function to pull a specific version using SCP
function pullVersion($versionNumber, $bundlePath) {
    global $localPath, $deploymentServerUser, $deploymentServerIP;

    echo "Pulling version $versionNumber from $bundlePath...\n";

    // SCP command to fetch the file
    $command = "scp $deploymentServerUser@$deploymentServerIP:$bundlePath $localPath";
    exec($command, $output, $status);

    if ($status === 0) {
        echo "Successfully pulled version $versionNumber.\n";
        return true;
    } else {
        echo "Failed to pull version $versionNumber.\n";
        return false;
    }
}

// Function to pull a specific version
function pullSpecificVersion($versionNumber) {
    $client = new rabbitMQClient("deploymentClient.ini", "deploymentServer");

    // Request the specific version
    $request = [
        "type" => "pullSpecificVersion",
        "version_number" => $versionNumber
    ];
    $response = $client->send_request($request);

    if ($response['success']) {
        $bundlePath = $response['bundle_path'];

        // Pull the specific version
        return pullVersion($versionNumber, $bundlePath);
    } else {
        echo "Error: " . $response['message'] . "\n";
        return false;
    }
}

// Prompt the user for input
echo "Enter the version number to pull (e.g., v1.0.0): ";
$versionNumber = trim(fgets(STDIN));

if (!empty($versionNumber)) {
    pullSpecificVersion($versionNumber);
} else {
    echo "Invalid version number.\n";
}
