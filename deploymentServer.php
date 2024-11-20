<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Database configuration
$dbHost = 'localhost';
$dbName = 'testdb';
$dbUser = 'testUser';
$dbPassword = '12345';

// Function to add a new version to the database
function addVersionToDatabase($versionNumber, $bundlePath) {
    global $dbHost, $dbName, $dbUser, $dbPassword;

    try {
        // Connect to the database
        $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the version information
        $stmt = $db->prepare("INSERT INTO deployment_history (version_number, bundle_path) VALUES (:version, :path)");
        $stmt->bindParam(':version', $versionNumber);
        $stmt->bindParam(':path', $bundlePath);
        $stmt->execute();

        return ["success" => true, "message" => "Version $versionNumber added to deployment history."];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

function handleRequest($request) {
    echo "Received request: ";
    var_dump($request);

    if (!isset($request['type'])) {
        return ["error" => "Invalid request type"];
    }

    switch ($request['type']) {
        case "deploy":
            // Handle deployment
            $versionNumber = $request['version_number'];
            $bundlePath = $request['bundle_path'];

            // Add the version to the database
            return addVersionToDatabase($versionNumber, $bundlePath);

        default:
            return ["error" => "Unsupported request type"];
    }
}

$server = new rabbitMQServer("deploymentServer.ini", "deploymentServer");
echo "Deployment Server is running...\n";
$server->process_requests('handleRequest');
?>
