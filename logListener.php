#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Parse the .ini file for RabbitMQ connection settings
$config = parse_ini_file('testLogging.ini');

// Establish a connection using the configuration
$connection = new AMQPStreamConnection(
    $config['BROKER_HOST'],
    $config['BROKER_PORT'],  
    $config['USER'],          
    $config['PASSWORD'],      
    $config['VHOST']          
);


$channel = $connection->channel();

// Declare the fanout exchange for broadcasting errors
$channel->exchange_declare($config['EXCHANGE'], 'fanout', false, true, false);

// Declare a temporary queue to receive messages (you can leave this out if you're not directly consuming in this script)
list($queue_name, , ) = $channel->queue_declare('', false, false, true, false);
$channel->queue_bind($queue_name, $config['EXCHANGE']);

// Unique VM identifier
$vm_id = gethostname();

// Function to log the error locally
function logErrorToFile($errorMessage) {
    $logFile = 'errorLog.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $errorMessage\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo "Error logged locally: $errorMessage\n";
}

// Function to send error to RabbitMQ (fanout exchange)
function sendErrorToRabbitMQ($errorMessage, $vm_id, $channel, $config) {
    // Add vm_id to the message to identify the sender
    $message = [
        'vm_id' => $vm_id,
        'error' => $errorMessage
    ];
    
    $msg_body = json_encode($message);
    $msg = new AMQPMessage($msg_body);
    $channel->basic_publish($msg, $config['EXCHANGE']);
    echo "Error sent to RabbitMQ: $errorMessage\n";
}

// Callback function to handle messages (receive errors from other VMs)
function handleMessage($msg) {
    global $vm_id;
    $message = json_decode($msg->body, true);
    
    // If the message is from the same VM, ignore it
    if ($message['vm_id'] === $vm_id) {
        echo "Ignoring message from the same VM.\n";
        return;
    }

    echo "Received error from another VM: " . $message['error'] . "\n";
    // Process the error message as needed (e.g., log to file, send alerts, etc.)
    logErrorToFile("Received from another VM: " . $message['error']);
}

// Simulate an error for testing (you can replace this with your actual error condition)
$errorMessage = "Simulated error: Missing username or password!";

// Log the error locally in the 'errorLog.txt' file
logErrorToFile($errorMessage);

// Send the error message to RabbitMQ through the fanout exchange
sendErrorToRabbitMQ($errorMessage, $vm_id, $channel, $config);

// Consume messages from the queue (listening for errors from other VMs)
echo "Waiting for error messages from other VMs...\n";
$channel->basic_consume($queue_name, '', false, true, false, false, 'handleMessage');

// Run the consumer loop
while ($channel->is_consuming()) {
    $channel->wait();
}

// Close the channel and connection
$channel->close();
$connection->close();
?>
