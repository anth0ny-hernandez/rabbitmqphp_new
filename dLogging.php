#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

$config = parse_ini_file('testLogging.ini');

$params = [
    'host' => $config['BROKER_HOST'],
    'port' => $config['BROKER_PORT'],
    'login' => $config['USER'],
    'password' => $config['PASSWORD'],
    'vhost' => $config['VHOST']
];

// Establish RabbitMQ connection
$conn = new AMQPConnection($params);
$conn->connect();

$channel = new AMQPChannel($conn);
$exchange = new AMQPExchange($channel);
$exchange->setName($config['logExchange']);
$exchange->setType($config['Fanout']);
$exchange->declare();

// Create a unique queue for this consumer (or static queues if multiple consumers use the same queue)
$logQueue = new AMQPQueue($channel);
$logQueue->setName('logQueue_' . uniqid());  // You can use a static name for shared queue
$logQueue->declare();
$logQueue->bind($exchange->getName());  // Bind to the Fanout exchange

// Callback function for receiving error messages
function consumeLogMessages($msg) {
    $logData = json_decode($msg->getBody(), true);
    
    // Prepare log message in readable format
    $logMessage = "Error: " . $logData['error'] . " | Timestamp: " . date('Y-m-d H:i:s', $logData['timestamp']) . "\n";
    
    // Write log to a file
    file_put_contents('errorLog.txt', $logMessage, FILE_APPEND);

    $msg->ack();  // Acknowledge the message
}

// Start listening for error messages
echo "Waiting for error messages...\n";
$logQueue->consume('consumeLogMessages');

// Simulate sending an error message
function sendErrorMessage() {
    // Error message to be sent
    $errorMessage = json_encode(['error' => 'VM-specific error occurred!', 'timestamp' => time()]);
    
    // Publish the error message to RabbitMQ (broadcast to all queues bound to the exchange)
    $exchange->publish($errorMessage, '', AMQP_NOPARAM);
    echo "Error message sent to RabbitMQ.\n";
}

// Simulate sending an error message after some time (could be an event like a failed operation)
sendErrorMessage();

// Close connection (not reached if listening indefinitely)
$channel->close();
$conn->close();
?>
