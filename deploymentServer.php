#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Database configuration
$dbHost = 'localhost';
$dbName = 'deployment_system';
$dbUser = 'root';
$dbPassword = 'password';

function executeDeployment($bundlePath, $versionNumber) {
    global $dbHost, $dbName, $dbUser, $dbPassword;

    // Save the bundle and update the version in the database
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $stmt = $db->prepare("INSERT INTO deployment_history (version_number, bundle_path) VALUES (:version_number, :bundle_path)");
        $stmt->bindParam(':version_number', $versionNumber);
        $stmt->bindParam(':bundle_path', $bundlePath);
        $stmt->execute();

        return ["success" => true, "message" => "Deployment successful"];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

function rollbackDeployment($versionNumber) {
    global $dbHost, $dbName, $dbUser, $dbPassword;

    // Connect to the database and find the bundle path for the specified version
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $stmt = $db->prepare("SELECT bundle_path FROM deployment_history WHERE version_number = :version_number");
        $stmt->bindParam(':version_number', $versionNumber);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Perform rollback using the bundle path
            $bundlePath = $result['bundle_path'];
            // Code to deploy the bundle at $bundlePath
            return ["success" => true, "message" => "Rollback successful to version $versionNumber"];
        } else {
            return ["success" => false, "message" => "Version not found"];
        }
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
            // Execute the deployment process
            $bundlePath = $request['bundle_path'];
            $versionNumber = $request['version_number'];
            return executeDeployment($bundlePath, $versionNumber);

        case "rollback":
            // Execute the rollback process
            $versionNumber = $request['version_number'];
            return rollbackDeployment($versionNumber);

        default:
            return ["error" => "Unsupported request type"];
    }
}

$server = new rabbitMQServer("deploymentServer.ini", "deploymentServer");
echo "Deployment Server is running...\n";
$server->process_requests('handleRequest');
?>
