#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Parse the .ini file for RabbitMQ connection settings
$config = parse_ini_file('testLogging.ini', true); // `true` to parse sections in the INI file

// Extract values from the 'testLogging' section
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

// Send a test log message
$data = [
    'log_message' => 'This is a test log message',  // You can replace this with actual log data
    'timestamp' => date('Y-m-d H:i:s')
];
$msg_body = json_encode($data);
$msg = new AMQPMessage($msg_body);

// Publish the message to the fanout exchange
$channel->basic_publish($msg, $exchange);

echo "Log message sent to all VMs.\n";

// Close the channel and the connection
$channel->close();
$connection->close();
?>
