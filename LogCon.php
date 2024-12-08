#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Parse the .ini file for RabbitMQ connection settings
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


$channel = $connection->channel();

// Declare the fanout exchange to receive messages from
$channel->exchange_declare($exchange, 'fanout', false, true, false);


list($queue_name, , ) = $channel->queue_declare('', false, false, true, false);

// Bind the temporary queue to the fanout exchange
$channel->queue_bind($queue_name, $exchange);

// Define the callback function to handle the incoming message
$callback = function($msg) {

    $logFile = __DIR__ . '/errorLog.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] " . $msg->body . "\n";
    
    // Append the message to the log file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo "Logged: " . $msg->body . "\n";
};

// Start consuming messages from the queue
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

// Wait for messages and handle them
while($channel->is_consuming()) {
    $channel->wait();
}

// Close the channel and the connection
$channel->close();
$connection->close();
?>
