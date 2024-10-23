#!/usr/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc'); // Include the RabbitMQ library
require_once('get_host_info.inc');
require_once('path.inc');

// Database connection setup
$conn = new mysqli('localhost', 'testUser', '12345', 'testdb');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "Consumer is running and waiting for messages...\n";

// Function to process incoming messages
function processMessage($request) {
    global $conn;

    echo "Received request: ";
    var_dump($request);

    $response = null;
    $username = $request['username'] ?? null;
    $password = $request['password'] ?? null;

    // Database operation handling
    switch ($request['type']) {
        case "register":
            echo "Processing username registration...\n";
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO accounts (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $hashedPassword);

            if ($stmt->execute()) {
                echo "User $username registered successfully!\n";
                $response = array("success" => true, "message" => "User registered successfully.");
            } else {
                echo "Error: " . $conn->error . "\n";
                $response = array("success" => false, "message" => "Registration failed: " . $conn->error);
            }
            break;

        case "login":
            echo "Processing login for $username...\n";
            $sql = "SELECT password FROM accounts WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    echo "Login successful for user $username.\n";
                    $session_token = bin2hex(random_bytes(16));
                    $session_expires = time() + 30;

                    $updateQuery = "UPDATE accounts SET session_token = ?, session_expires = ? WHERE username = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("sis", $session_token, $session_expires, $username);

                    if ($updateStmt->execute()) {
                        $response = array("success" => true, "session_token" => $session_token);
                    } else {
                        echo "Error updating session: " . $conn->error . "\n";
                        $response = array("success" => false, "message" => "Session update failed.");
                    }
                } else {
                    echo "Incorrect password for user $username.\n";
                    $response = array("success" => false, "message" => "Incorrect password.");
                }
            } else {
                echo "User $username not found.\n";
                $response = array("success" => false, "message" => "User not found.");
            }
            break;

        default:
            echo "Unsupported request type: " . $request['type'] . "\n";
            $response = array("success" => false, "message" => "Unsupported request type.");
            break;
    }

    return $response;
}

// Create a server that listens for requests from clients
$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");
$server->process_requests('processMessage');

// Close the database connection
$conn->close();
?>
