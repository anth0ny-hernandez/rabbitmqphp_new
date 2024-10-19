#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Parse the .ini file for RabbitMQ connection settings
$config = parse_ini_file('testRabbitMQ.ini');

// Establish a connection using the configuration
$connection = new AMQPStreamConnection(
    $config['host'],      // Host from .ini file
    $config['port'],      // Port from .ini file
    $config['user'],      // Username from .ini file
    $config['password'],  // Password from .ini file
    $config['vhost']      // Virtual host from .ini file
);

// Create a channel
$channel = $connection->channel();

// Declare the registration queue
$channel->queue_declare('registration_queue', false, true, false, false);

// Send a test registration message
$data = [
    'username' => 'test_user',      // You can replace with actual input
    'password' => 'test_password'   // Replace with actual password
];
$msg_body = json_encode($data);
$msg = new AMQPMessage($msg_body);

$channel->basic_publish($msg, '', 'registration_queue');

echo "Registration request sent to RabbitMQ.\n";

// Close the channel and the connection
$channel->close();
$connection->close();
?>

