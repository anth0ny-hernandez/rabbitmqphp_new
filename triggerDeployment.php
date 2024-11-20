<?php
require_once('rabbitMQLib.inc');

// Configuration for the deployment
$versionNumber = "v1.0.0"; // Replace with the actual version number
$bundlePath = "/home/yashmandal/git/my-repo.tar.gz"; // Path to the transferred bundle on the deployment server

// Send a deployment request to the RabbitMQ server
try {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    $request = [
        "type" => "deploy",
        "version_number" => $versionNumber,
        "bundle_path" => $bundlePath
    ];

    echo "Sending deployment request...\n";
    $response = $client->send_request($request);

    if ($response['success']) {
        echo "Deployment Successful: " . $response['message'] . "\n";
    } else {
        echo "Deployment Failed: " . $response['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
