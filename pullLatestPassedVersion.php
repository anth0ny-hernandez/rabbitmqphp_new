<?php
require_once('rabbitMQLib.inc');

// Function to pull the latest version with `pass` status
function pullLatestPassedVersion() {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    // Request the latest passed version
    $request = [
        "type" => "pullLatestPassedVersion"
    ];
    $response = $client->send_request($request);

    if (isset($response['success']) && $response['success']) {
        $versionNumber = $response['version_number'];
        $bundlePath = $response['bundle_path'];
        $localPath = "/home/yashmandal/git/deployment"; // Update to the production directory

        echo "Pulling version $versionNumber...\n";

        // Use SCP to pull the file
        $deploymentServerUser = "yashmandal";
        $deploymentServerIP = "172.22.217.86"; // Replace with actual IP
        $command = "scp $deploymentServerUser@$deploymentServerIP:$bundlePath $localPath";

        exec($command, $output, $status);

        if ($status === 0) {
            echo "Successfully pulled version $versionNumber to production.\n";
        } else {
            echo "Failed to pull version $versionNumber. Check your connection and permissions.\n";
        }
    } else {
        echo "Error: " . ($response['message'] ?? "Unknown error") . "\n";
    }
}

// Execute the function
pullLatestPassedVersion();
