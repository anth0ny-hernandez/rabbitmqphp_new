<?php
require_once('rabbitMQLib.inc');

// Path to the version tracker file
$versionTrackerFile = "/home/yashmandal/test/rabbitmqphp_new/versionTracker.txt"; // Update this path if necessary

// Read the last line of the version tracker file to get the latest version
$version_number = "";
if (file_exists($versionTrackerFile)) {
    $lines = file($versionTrackerFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $version_number = end($lines); // Get the last line
} else {
    die("Error: versionTracker.txt file not found.\n");
}

if (empty($version_number)) {
    die("Error: Could not determine the latest version from versionTracker.txt.\n");
}

// Define the bundle path dynamically based on the version number
$bundlePath = "/home/yashmandal/git/deployment/myRepo-$version_number.tar.gz"; // Adjust if necessary

// Send a deployment request to the RabbitMQ server
try {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    $request = [
        "type" => "deploy",
        "version_number" => "v$version_number", // Example: "v1.0.0"
        "bundle_path" => $bundlePath
    ];

    echo "Sending deployment request for version $version_number...\n";
    $response = $client->send_request($request);

    if ($response['success']) {
        echo "Deployment Successful: " . $response['message'] . "\n";
    } else {
        echo "Deployment Failed: " . $response['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
