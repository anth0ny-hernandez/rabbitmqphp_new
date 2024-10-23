#!/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Load configuration from the .ini file
$config = parse_ini_file("testRabbitMQ.ini", true);
$queue_name = $config['testServer']['testQueue'];

// Establish a connection to RabbitMQ using the configuration
$connection = new AMQPStreamConnection(
    $config['testServer']['BROKER_HOST'],
    $config['testServer']['BROKER_PORT'],
    $config['testServer']['USER'],
    $config['testServer']['PASSWORD'],
    $config['testServer']['VHOST']
);

$channel = $connection->channel();

// Declare the queue if it does not exist
$channel->queue_declare($queue_name, false, true, false, false);

// Database connection setup
$conn = new mysqli('localhost', 'testUser', '12345', 'testdb');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "RabbitMQ DB Listener is running and waiting for messages from the queue: $queue_name...\n";

// Callback function to process incoming messages
function databaseProcessor($msg) {
    global $conn;

    $request = json_decode($msg->body, true); // Assuming the request is sent as JSON

    echo "Received request: ";
    var_dump($request);

    $username = $request['username'];
    $password = $request['password'];

    switch ($request['type']) {
        case "register":
            echo "Processing username registration...\n";
            echo "================================\n";

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO accounts (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $hashedPassword);

            if ($stmt->execute()) {
                echo "User $username registered successfully!\n";
                echo "================================\n";
                $msg->ack(); // Acknowledge the message
            } else {
                echo "Error: " . $conn->error . "\n";
                error_log("Error in registration: " . $conn->error);
                $msg->ack(); // Acknowledge even on failure to remove the message from the queue
            }
            break;

        case "login":
            echo "Processing login for $username...\n";
            echo "================================\n";

            $sql = "SELECT password FROM accounts WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    echo "Login successful for user $username!\n";
                    echo "================================\n";

                    $session_token = bin2hex(random_bytes(16));
                    $session_expires = time() + 30;

                    $updateQuery = "UPDATE accounts SET session_token = ?, session_expires = ? WHERE username = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("sis", $session_token, $session_expires, $username);
                    $updateStmt->execute();

                    $msg->ack(); // Acknowledge the message
                } else {
                    echo "Incorrect password for user $username!\n";
                    echo "================================\n";
                    $msg->ack(); // Acknowledge the message even if login failed
                }
            } else {
                echo "User $username not found!\n";
                echo "================================\n";
                $msg->ack(); // Acknowledge the message even if the user is not found
            }
            break;

        default:
            echo "Unsupported message type: " . $request['type'] . "\n";
            $msg->ack(); // Acknowledge unsupported messages to avoid re-queueing
            break;
    }
}

// Set up a consumer that uses the callback function for processing
$channel->basic_consume($queue_name, '', false, false, false, false, 'databaseProcessor');

// Keep the script running and waiting for messages
while ($channel->is_consuming()) {
    $channel->wait();
}

// Clean up
$channel->close();
$connection->close();
$conn->close();

ob_end_flush();
?>
