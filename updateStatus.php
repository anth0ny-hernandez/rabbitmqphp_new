<?php
require_once('rabbitMQLib.inc');

// Function to update deployment status
function updateStatus($version, $status) {
    $client = new rabbitMQClient("deploymentClient.ini", "deploymentServer");

    $request = [
        "type" => "updateStatus",
        "version" => $version,
        "status" => $status
    ];

    $response = $client->send_request($request);

    if ($response['success']) {
        echo "Status for version $version updated to $status successfully.\n";
    } else {
        echo "Error: " . $response['message'] . "\n";
    }
}

// CLI interface for QA to update status
echo "Enter the version number to update: ";
$version = trim(fgets(STDIN));

echo "Enter the status (pass/fail): ";
$status = trim(fgets(STDIN));

// Validate status
if (!in_array($status, ['pass', 'fail'])) {
    echo "Invalid status. Please enter 'pass' or 'fail'.\n";
    exit;
}

updateStatus($version, $status);
