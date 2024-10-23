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

echo "Consumer is running and waiting for messages from the queue: $queue_name...\n";

// Function to process incoming messages
function processMessage($msg) {
    global $channel;

    $request = json_decode($msg->body, true); // Assuming the request is sent as JSON
    echo "Received request: ";
    var_dump($request);

    $response = null;

    // Process the request
    switch ($request['type']) {
        case "login":
            echo "Processing login...\n";
            // Simulate a login response (for demonstration purposes)
            $response = array("success" => true, "message" => "Login successful.");
            break;

        case "register":
            echo "Processing registration...\n";
            // Simulate a registration response (for demonstration purposes)
            $response = array("success" => true, "message" => "User registered successfully.");
            break;

        default:
            echo "Unsupported request type: " . $request['type'] . "\n";
            $response = array("success" => false, "message" => "Unsupported request type.");
            break;
    }

    // Send the response back to the reply_to queue specified in the original message
    $reply_to = $msg->get('reply_to');
    $correlation_id = $msg->get('correlation_id');
    if ($reply_to) {
        $responseMsg = new AMQPMessage(
            json_encode($response),
            array('correlation_id' => $correlation_id)
        );
        $channel->basic_publish($responseMsg, '', $reply_to);
        echo "Response sent to the queue: $reply_to\n";
    } else {
        echo "No reply_to queue specified in the message.\n";
    }

    // Acknowledge the original message
    $msg->ack();
}

// Set up a consumer that uses the processMessage function for processing
$channel->basic_consume($queue_name, '', false, false, false, false, 'processMessage');

// Keep the script running and waiting for messages
while ($channel->is_consuming()) {
    $channel->wait();
}

// Clean up
$channel->close();
$connection->close();
?>
