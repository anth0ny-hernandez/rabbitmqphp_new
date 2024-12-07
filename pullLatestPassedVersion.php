<?php
require_once('rabbitMQLib.inc');

// Function to get the latest passed version
function getLatestPassedVersion() {
    $client = new rabbitMQClient("deploymentServer.ini", "deploymentServer");

    $request = [
        "type" => "pullLatestPassedVersion"
    ];
    $response = $client->send_request($request);

    if (isset($response['success']) && $response['success']) {
        return [
            "version_number" => $response['version_number'],
            "bundle_path" => $response['bundle_path']
        ];
    } else {
        echo "Error: " . ($response['message'] ?? "Unknown error") . "\n";
        return null;
    }
}

// Function to pull a version
function pullVersion($version, $bundlePath) {
    $localPath = "/home/yashmandal/git/deployment"; // Update to the production directory
    $deploymentServerUser = "yashmandal";
    $deploymentServerIP = "172.22.217.86"; // Replace with actual IP

    echo "Pulling version $version...\n";
    $command = "scp $deploymentServerUser@$deploymentServerIP:$bundlePath $localPath";

    exec($command, $output, $status);

    if ($status === 0) {
        echo "Successfully pulled version $version to production.\n";
        return true;
    } else {
        echo "Failed to pull version $version. Check your connection and permissions.\n";
        return false;
    }
}

// Main script to continuously listen for updates
function listenForLatestPassedVersion() {
    $currentVersion = null;

    while (true) {
        echo "Checking for the latest passed version...\n";

        // Get the latest passed version
        $latest = getLatestPassedVersion();

        if ($latest) {
            $latestVersion = $latest['version_number'];
            $bundlePath = $latest['bundle_path'];

            // Pull only if it's a newer version
            if ($currentVersion !== $latestVersion) {
                if (pullVersion($latestVersion, $bundlePath)) {
                    $currentVersion = $latestVersion;

                    // Log the pulled version locally
                    file_put_contents("/home/yashmandal/test/rabbitmqphp_new/currentPassedVersion.txt", $latestVersion . PHP_EOL, FILE_APPEND);
                }
            } else {
                echo "Already running the latest version ($currentVersion). No action needed.\n";
            }
        }

        // Wait before checking again
        sleep(60); // Check every 60 seconds
    }
}

// Start listening
listenForLatestPassedVersion();
