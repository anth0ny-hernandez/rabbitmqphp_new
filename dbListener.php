#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader for AMQPStreamConnection
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DbListenerServer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $vhost;
    private $queue;
    private $exchange;

    public function __construct($config_file, $server_name) {
        $config = parse_ini_file($config_file, true);
        $this->host = $config[$server_name]['BROKER_HOST'];
        $this->port = $config[$server_name]['BROKER_PORT'];
        $this->username = $config[$server_name]['USER'];
        $this->password = $config[$server_name]['PASSWORD'];
        $this->vhost = $config[$server_name]['VHOST'];
        $this->queue = $config[$server_name]['QUEUE'];
        $this->exchange = $config[$server_name]['EXCHANGE'];
    }

    public function processRequests($callback) {
        // Create a connection to RabbitMQ
        $connection = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->username,
            $this->password,
            $this->vhost
        );

        $channel = $connection->channel();

        // Declare the exchange and queue
        $channel->exchange_declare($this->exchange, 'topic', false, true, false);
        $channel->queue_declare($this->queue, false, true, false, false);
        $channel->queue_bind($this->queue, $this->exchange);

        // Consume messages from the queue and process them
        $channel->basic_consume($this->queue, '', false, false, false, false, function($msg) use ($callback, $channel) {
            $response = call_user_func($callback, json_decode($msg->body, true));
            $msg->ack();
            
            // Sending response back to the reply queue
            $reply_to = $msg->get('reply_to');
            $correlation_id = $msg->get('correlation_id');
            if ($reply_to) {
                $responseMsg = new AMQPMessage(
                    json_encode($response),
                    array('correlation_id' => $correlation_id)
                );
                $channel->basic_publish($responseMsg, '', $reply_to);
            }
        });

        // Keep the connection open to process requests
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}

function processRequest($request) {

    databaseProcessor($request);

    return true; // Call databaseProcessor function and return result
}

// Function to process login and registration requests
function databaseProcessor($request) {
    echo "Received request in dbListener: ";
    var_dump($request);

    // Database connection & credential variable assignment
    $conn = new mysqli('172.22.53.55', 'testUser', '12345', 'testdb');
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $username = $request['username'] ?? null;
    $password = $request['password'] ?? null;
    $response = [];

    switch($request['type']) {
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
            echo "Unsupported request type.\n";
            $response = array("success" => false, "message" => "Unsupported request type.");
            break;
    }

    $conn->close();
    return $response;
}

// Instantiate the server and start processing requests
$dbServer = new rabbitMQServer("testRabbitMQ.ini", "testServer");
$dbServer->process_requests('databaseProcessor');
?>
