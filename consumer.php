#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php'; // Include Composer autoloader
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Load configuration from the .ini file
$config = parse_ini_file("testRabbitMQ.ini", true);
$queue_name = $config['testServer']['QUEUE'];

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

echo "Consumer is running and waiting for messages from the queue: $queue_name...\n";

// Callback function to process incoming messages
function processMessage($msg) {
    global $conn, $channel;

    $request = json_decode($msg->body, true); // Assuming the request is sent as JSON

    echo "Received request: ";
    var_dump($request);

    $response = null;
    $username = $request['username'] ?? null;
    $password = $request['password'] ?? null;

    switch ($request['type']) {
        case "register":
            echo "Processing registration...\n";
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO accounts (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $hashedPassword);

            if ($stmt->execute()) {
                echo "User $username registered successfully.\n";
                $response = array("success" => true, "message" => "User registered successfully.");
            } else {
                echo "Error: " . $conn->error . "\n";
                $response = array("success" => false, "message" => "Registration failed.");
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
                    $response = array("success" => true, "message" => "Login successful.");
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

    // Send the response back to RabbitMQ
    $responseQueue = 'response_queue'; // You can change this to the actual response queue name
    $msgResponse = new AMQPMessage(json_encode($response), array('delivery_mode' => 2));
    $channel->basic_publish($msgResponse, '', $responseQueue);

    // Acknowledge the original message
    $msg->ack();
}

// Set up a consumer that uses the callback function for processing
$channel->basic_consume($queue_name, '', false, false, false, false, 'processMessage');

// Keep the script running and waiting for messages
while ($channel->is_consuming()) {
    $channel->wait();
}

// Clean up
$channel->close();
$connection->close();
$conn->close();

?>
