<?php
require_once('rabbitMQLib.inc');

// Path to the version tracker file
$versionTrackerFile = "/home/yashmandal/test/rabbitmqphp_new/versionTracker.txt"; // Adjust path if necessary

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

// Define the directories
$bundlingDir = "/home/yashmandal/test/bundling"; // Development machine's bundling directory
$deploymentDir = "/home/yashmandal/git/deployment"; // Deployment machine's target directory

// Find the tarball matching the latest version in the bundling directory
$bundleFiles = glob("$bundlingDir/*-version-$version_number.tar.gz");

if (empty($bundleFiles)) {
    die("Error: No bundle found for version $version_number in $bundlingDir.\n");
}

// Extract the bundle name (e.g., frontend, server, dmz) from the file name
$bundleFileName = basename($bundleFiles[0]);
preg_match('/^(frontend|server|dmz)-version-/', $bundleFileName, $matches);

if (empty($matches[1])) {
    die("Error: Could not determine the bundle type from the file name.\n");
}

$bundleType = $matches[1];
$bundlePath = "$deploymentDir/$bundleFileName"; // The path where the bundle will be deployed on the deployment machine

// SCP command to transfer the bundle from the development machine to the deployment machine
echo "Transferring $bundleFileName to the deployment machine...\n";
$scpCommand = "scp $bundlingDir/$bundleFileName yashmandal@172.22.217.86:$bundlePath";
exec($scpCommand, $output, $result);

if ($result !== 0) {
    die("Error: Failed to transfer the bundle to the deployment machine.\n");
}

// Send a deployment request to the RabbitMQ server
try {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    $request = [
        "type" => "deploy",
        "version_number" => $version_number,
        "bundle_path" => $bundlePath,
        "bundle_type" => $bundleType
    ];

    echo "Sending deployment request for version $version_number ($bundleType)...\n";
    $response = $client->send_request($request);

    if ($response['success']) {
        echo "Deployment Successful: " . $response['message'] . "\n";
    } else {
        echo "Deployment Failed: " . $response['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
