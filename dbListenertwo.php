#!/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Database connection & credential setup (move outside the function)
$conn = new mysqli('localhost', 'testUser', '12345', 'testdb');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

function databaseProcessor($request, $msg) {
    global $conn;

    echo "Received request: ";
    var_dump($request);

    $username = $request['username'];
    $password = $request['password'];

    switch($request['type']) {
        case "register":
            echo "Processing username registration...\n";
            echo "================================\n";

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO accounts (username, password) VALUES ('$username', '$hashedPassword')";
            if ($conn->query($sql) === TRUE) {
                echo "User $username registered successfully!\n";
                echo "================================\n";
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Acknowledge the message
                return true;
            } else {
                error_log("Error in registration: " . $conn->error);
                echo "Error: " . $conn->error . "\n";
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Acknowledge even on failure
                return false;
            }

        case "login":
            echo "Processing login for $username...\n";
            echo "================================\n";

            $sql = "SELECT password FROM accounts WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $ray = $stmt->get_result();

            if ($ray->num_rows > 0) {
                $row = $ray->fetch_assoc();

                if (password_verify($password, $row['password'])) {
                    echo "Login successful for user $username!\n";
                    echo "================================\n";

                    $session_token = bin2hex(random_bytes(16));
                    $session_expires = time() + 30;

                    $updateQuery = "UPDATE accounts SET session_token = ?, session_expires = ? WHERE username = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("sis", $session_token, $session_expires, $username);

                    if ($updateStmt->execute()) {
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Acknowledge the message
                        return array("success" => true, "session_token" => $session_token);
                    } else {
                        error_log("Error updating session: " . $conn->error);
                        echo "Error updating session for $username!\n";
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Acknowledge even on failure
                        return array("success" => false, "message" => "Session update failed.");
                    }
                } else {
                    echo "Incorrect password for user $username!\n";
                    echo "================================\n";
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Acknowledge even on failure
                    return array("success" => false, "message" => "Incorrect password.");
                }
            } else {
                echo "User $username not found!\n";
                echo "================================\n";
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Acknowledge even on failure
                return array("success" => false, "message" => "User not found.");
            }

        default:
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Acknowledge even on unrecognized type
            return "Database Client-Server error";
    }
}

// Create a server that listens for requests from clients
$dbServer = new rabbitMQServer("testDB_RMQ.ini", "dbConnect");
ob_end_flush();
echo "RabbitMQ Server is running and waiting for requests...\n";

// Process incoming requests with error handling
try {
    $dbServer->process_requests('databaseProcessor');
} catch (Exception $e) {
    echo "Error processing request: " . $e->getMessage() . "\n";
}

// Close the database connection
$conn->close();
echo "Database connection closed.\n";
?>
