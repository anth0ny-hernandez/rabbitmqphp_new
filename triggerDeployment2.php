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

// Prompt the user to select the bundle type
echo "Select the bundle type for deployment:\n";
echo "1. frontend\n";
echo "2. server\n";
echo "3. dmz\n";
$bundleType = readline("Enter the number corresponding to your choice: ");

// Determine the bundle type based on the user input
switch ($bundleType) {
    case '1':
        $bundleName = "frontend";
        break;
    case '2':
        $bundleName = "server";
        break;
    case '3':
        $bundleName = "dmz";
        break;
    default:
        die("Invalid choice. Exiting...\n");
}

// Define the bundle path dynamically based on the version number and bundle type
$bundlePath = "/home/yashmandal/git/deployment/${bundleName}-version-${version_number}.tar.gz";

if (!file_exists($bundlePath)) {
    die("Error: The specified bundle file does not exist: $bundlePath\n");
}

// Send a deployment request to the RabbitMQ server
try {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    $request = [
        "type" => "deploy",
        "version_number" => $version_number,
        "bundle_path" => $bundlePath,
        "bundle_type" => $bundleName // Adding bundle type for better tracking
    ];

    echo "Sending deployment request for version $version_number ($bundleName)...\n";
    $response = $client->send_request($request);

    if ($response['success']) {
        echo "Deployment Successful: " . $response['message'] . "\n";
    } else {
        echo "Deployment Failed: " . $response['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
