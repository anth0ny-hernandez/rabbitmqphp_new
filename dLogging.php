<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RabbitMQ setup
$Broker_Host = 'localhost';
$Broker_Port = 5672;
$User = 'test';
$Password = 'test';
$vhost = 'testHost';
$exchangeName = 'logExchange';
$exchangeType = 'fanout';

// Unique identifier for this VM (e.g., hostname or custom name)
$vmIdentifier = gethostname();


$connection = new AMQPStreamConnection($Broker_Host, $Broker_Port, $User, $Password, $vhost);
$channel = $connection->channel();

// Declare exchange
$channel->exchange_declare($exchangeName, $exchangeType, false, true, false);

// Declare a temporary queue for receiving messages
list($queueName, ,) = $channel->queue_declare('', false, false, true, false);
$channel->queue_bind($queueName, $exchangeName);

// Function to log messages locally
function logMessage($message)
{
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}" . PHP_EOL;
    file_put_contents('errorLog.txt', $logEntry, FILE_APPEND);
    echo $logEntry;
}

// Function to send error messages
function sendError($channel, $exchangeName, $errorMessage, $vmIdentifier)
{
    $messageData = json_encode([
        'vm' => $vmIdentifier,
        'error' => $errorMessage,
    ]);

    logMessage($errorMessage); // Log locally before sending
    $msg = new AMQPMessage($messageData);
    $channel->basic_publish($msg, $exchangeName);
}

// Function to handle received messages
function receiveError($message)
{
    global $vmIdentifier;

    $data = json_decode($message->body, true);
    if ($data['vm'] !== $vmIdentifier) { // Only log if the message is not from this VM
        logMessage($data['error']);
    }
}

// Attach consumer to listen for incoming messages
$channel->basic_consume($queueName, '', false, true, false, false, 'receiveError');

// Example: Simulate an error (replace this with your actual error detection logic)
if (!$databaseConnection = false) { // Example condition
    $errorMessage = "Failed to connect to the database.";
    sendError($channel, $exchangeName, $errorMessage, $vmIdentifier);
}

// Main loop to keep the consumer running
echo "Waiting for error messages. To exit, press CTRL+C\n";

while ($channel->is_consuming()) {
    $channel->wait();
}

// Cleanup
$channel->close();
$connection->close();
?>
