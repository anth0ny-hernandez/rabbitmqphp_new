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
// function isAnyMachineReachable($ipAddresses) {
//     foreach ($ipAddresses as $ip) {
//         $pingResult = exec("ping -c 1 -w 1 $ip 2>&1", $output, $status);
//         if ($status === 0) {
//             echo "Machine $ip is reachable.\n"; // Debugging output
//             return true; // Return true as soon as one machine is reachable
//         } else {
//             echo "Machine $ip is not reachable.\n"; // Debugging output
//         }
//     }
//     return false; // If none of the machines are reachable, return false
// }

// Update deployment history status


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
        updateDeploymentStatus($versionNumber, "new");
        // $installer = new rabbitMQClient("installer.ini", "installer");
        // $request = [
        //     "version_number" => $versionNumber
        // ];
        // $installer->send_request($versionNumber);

        // List of remote machine IP addresses to check
        // $remoteMachineIPs = [
        //     "172.22.87.142",  // Replace with actual IPs
        //     "192.168.1.11",
        //     "192.168.1.12"
        // ];

        // // Check if any machine is reachable before proceeding
        // if (isAnyMachineReachable($remoteMachineIPs)) {
        //     // Only run these lines if at least one machine is reachable
        //     $installer = new rabbitMQClient("installer.ini", "installer");
        //     $request = [
        //         "version_number" => $versionNumber
        //     ];
        //     $installer->send_request($versionNumber);
        // } else {
        //     echo "None of the remote machines are reachable. Skipping installation trigger.\n";
        // }

        return ["success" => true, "message" => "Version $versionNumber added to deployment history."];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

function updateDeploymentStatus($versionNumber, $status) {
    global $dbHost, $dbName, $dbUser, $dbPassword;

    try {
        // Connect to the database
        $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the version exists
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM deployment_history WHERE version_number = :version");
        $checkStmt->bindParam(':version', $versionNumber);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn() == 0) {
            return ["success" => false, "message" => "Version $versionNumber not found in deployment history."];
        }

        // Update the status for the specified version
        $updateStmt = $db->prepare("UPDATE deployment_history SET status = :status WHERE version_number = :version");
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':version', $versionNumber);
        $updateStmt->execute();

        return ["success" => true, "message" => "Status for version $versionNumber updated to $status."];
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

        case "updateStatus":
            $versionNumber = $request['version_number'];
            $status = $request['status'];
            return updateDeploymentStatus($versionNumber, $status);


        case "pullLatestVersion":
            return getLatestVersion();

        case "pullSpecificVersion":
            $versionNumber = $request['version_number'];
            return getSpecificVersion($versionNumber);

        case "deploy":
            return addVersionToDatabase($request['version_number'], $request['bundle_path']);
        default:
            return ["error" => "Unsupported request type"];
    }
}

function getLatestVersion() {
    global $dbHost, $dbName, $dbUser, $dbPassword;

    try {
        $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->query("SELECT * FROM deployment_history ORDER BY id DESC LIMIT 1");
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($latest) {
            return [
                "success" => true,
                "version_number" => $latest['version_number'],
                "bundle_path" => $latest['bundle_path']
            ];
        } else {
            return ["success" => false, "message" => "No versions found in deployment history."];
        }
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

function getSpecificVersion($versionNumber) {
    global $dbHost, $dbName, $dbUser, $dbPassword;

    try {
        $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM deployment_history WHERE version_number = :version");
        $stmt->bindParam(':version', $versionNumber);
        $stmt->execute();
        $specific = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($specific) {
            return [
                "success" => true,
                "version_number" => $specific['version_number'],
                "bundle_path" => $specific['bundle_path']
            ];
        } else {
            return ["success" => false, "message" => "Version $versionNumber not found in deployment history."];
        }
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}


$server = new rabbitMQServer("deploymentServer.ini", "deploymentServer");
echo "Deployment Server is running...\n";
$server->process_requests('handleRequest');
?>
