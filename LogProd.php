#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$config = parse_ini_file('testLogging.ini', true);

$broker_host = $config['testLogging']['BROKER_HOST'];
$broker_port = $config['testLogging']['BROKER_PORT'];
$user = $config['testLogging']['USER'];
$password = $config['testLogging']['PASSWORD'];
$vhost = $config['testLogging']['VHOST'];
$exchange = $config['testLogging']['EXCHANGE'];
$queue = $config['testLogging']['QUEUE'];

// Establish a connection using the configuration
$connection = new AMQPStreamConnection($broker_host, $broker_port, $user, $password, $vhost);

// Create a channel
$channel = $connection->channel();

// Declare the fanout exchange (no routing key needed for fanout)
$channel->exchange_declare($exchange, 'fanout', false, true, false);

// Function to send error messages to RabbitMQ
function sendErrorToRabbitMQ($errorMessage) {
    global $channel, $exchange;
    
    // Prepare the error message
    $data = [
        'errorMessage' => $errorMessage,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Convert to JSON format for RabbitMQ
    $msg_body = json_encode($data);
    $msg = new AMQPMessage($msg_body);

    // Publish the error message to RabbitMQ exchange (fanout)
    $channel->basic_publish($msg, $exchange);
}

// Function to log the error locally and send to RabbitMQ
function logErrorAndSend($errorMessage) {
    // Log the error to a local file (errorLog.txt)
    $data = [
        'errorMessage' => $errorMessage,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    file_put_contents('errorLog.txt', json_encode($data) . PHP_EOL, FILE_APPEND);

    // Send the error to RabbitMQ
    sendErrorToRabbitMQ($errorMessage);
}

// $channel->close();
// $connection->close();
?>

