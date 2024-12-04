<?php
require_once('rabbitMQLib.inc');

// Function to update deployment status
function updateStatus($version, $status) {
    $client = new rabbitMQClient("deploymentClient.ini", "deploymentServer");

    // Prepare the request
    $request = [
        "type" => "updateStatus",
        "version_number" => $version,
        "status" => $status
    ];

    // Send the request to the deployment server
    $response = $client->send_request($request);

    // Handle the response
    if (isset($response['success']) && $response['success']) {
        echo "Status for version $version updated to $status successfully.\n";
    } else {
        echo "Error: " . ($response['message'] ?? "Unknown error") . "\n";
    }
}

// CLI interface for QA to update status
echo "Enter the version number to update: ";
$version = trim(fgets(STDIN));

echo "Enter the status (pass/fail): ";
$status = trim(fgets(STDIN));

// Validate the status input
if (!in_array($status, ['pass', 'fail'])) {
    echo "Invalid status. Please enter 'pass' or 'fail'.\n";
    exit;
}

// Call the function to update the status
updateStatus($version, $status);
