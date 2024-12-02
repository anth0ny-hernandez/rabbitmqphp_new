<?php
require_once('rabbitMQLib.inc');

// Configuration
$localPath = "/path/to/qa-or-production/"; // Update this to the environment's directory
$deploymentServerUser = "yashmandal";      // Deployment server username
$deploymentServerIP = "172.22.217.86";    // Deployment server IP

// Function to pull the latest version using SCP
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

// Function to listen for the latest version deployment
function listenForLatestVersion() {
    $client = new rabbitMQClient("deploymentClient.ini", "deploymentServer");

    while (true) {
        // Request the latest version
        $request = ["type" => "pullLatestVersion"];
        $response = $client->send_request($request);

        if ($response['success']) {
            $versionNumber = $response['version_number'];
            $bundlePath = $response['bundle_path'];

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
