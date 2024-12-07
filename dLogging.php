#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Parse configuration
$config = parse_ini_file('testLogging.ini');

// Define the source identifier for this VM
$vmId = 'VM1'; // Change this for each VM (e.g., VM2, VM3, etc.)

// Connection parameters
$params = [
    'host' => $config['BROKER_HOST'],
    'port' => $config['BROKER_PORT'],
    'login' => $config['USER'],
    'password' => $config['PASSWORD'],
    'vhost' => $config['VHOST']
];

try {
    $conn = new AMQPConnection($params);
    $conn->connect();
    echo "Connected to RabbitMQ.\n";

    $channel = new AMQPChannel($conn);
    $exchange = new AMQPExchange($channel);
    $exchange->setName($config['EXCHANGE']);
    $exchange->setType($config['EXCHANGE_TYPE']);
    $exchange->setFlags(AMQP_DURABLE);
    $exchange->declare();
    echo "Exchange declared: {$config['EXCHANGE']}.\n";

    $logQueue = new AMQPQueue($channel);
    $logQueue->setName('logQueue_' . uniqid());
    $logQueue->declare();
    $logQueue->bind($exchange->getName());
    echo "Queue declared and bound: {$logQueue->getName()}.\n";

} catch (Exception $e) {
    echo "ERROR: Could not connect to RabbitMQ - " . $e->getMessage() . "\n";
    exit(1);
}

// Function to log error messages locally
function logErrorMessage($errorMessage, $source = null) {
    $logType = $source ? "Error Received" : "Error Sent";
    $logMessage = "{$logType}: {$errorMessage} | Timestamp: " . date('Y-m-d H:i:s') . "\n";

    // Log the error to a file
    file_put_contents('errorLog.txt', $logMessage, FILE_APPEND);
    echo "Logged: {$logMessage}\n";
}

// Function to send error messages to RabbitMQ
function sendErrorMessage($errorMessage) {
    global $exchange, $vmId;

    $errorData = json_encode([
        'error' => $errorMessage,
        'timestamp' => time(),
        'source' => $vmId // Include the source VM identifier
    ]);

    try {
        $exchange->publish($errorData, '', AMQP_NOPARAM);
        logErrorMessage($errorMessage); // Log locally after sending
    } catch (Exception $e) {
        echo "ERROR: Failed to publish message - " . $e->getMessage() . "\n";
    }
}

// Callback function for receiving error messages
function consumeLogMessages($msg) {
    global $logQueue, $vmId;

    try {
        $logData = json_decode($msg->getBody(), true);

        // Ignore messages originating from this VM
        if ($logData['source'] === $vmId) {
            $logQueue->ack($msg->getDeliveryTag());
            return;
        }

        $logMessage = $logData['error'];
        logErrorMessage($logMessage, $logData['source']); // Log the received error locally
        $logQueue->ack($msg->getDeliveryTag()); // Acknowledge message
    } catch (Exception $e) {
        echo "ERROR: Failed to process message - " . $e->getMessage() . "\n";
    }
}

// Start listening for error messages
function startLogListener() {
    global $logQueue;

    echo "Waiting for error messages...\n";
    try {
        $logQueue->consume('consumeLogMessages');
    } catch (Exception $e) {
        echo "ERROR: Failed to consume messages - " . $e->getMessage() . "\n";
    }
}

// Simulate application errors
function performDatabaseOperation() {
    try {
        $result = false;

        if (!$result) {
            throw new Exception('Database query failed: Could not fetch data.');
        }
    } catch (Exception $e) {
        sendErrorMessage($e->getMessage());
    }
}

function performFileOperation() {
    try {
        $file = fopen('nonexistent_file.txt', 'r');

        if (!$file) {
            throw new Exception('File operation failed: File does not exist.');
        }
    } catch (Exception $e) {
        sendErrorMessage($e->getMessage());
    }
}

// Example Application Logic
performDatabaseOperation();
performFileOperation();

// Start the listener for error messages
startLogListener();

// Close connection (not typically reached if running indefinitely)
$channel->close();
$conn->disconnect();
?>

