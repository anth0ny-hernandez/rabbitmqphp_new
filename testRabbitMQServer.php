#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

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

// Declare the queues (for registration and login)
$channel->queue_declare('registration_queue', false, true, false, false);
$channel->queue_declare('login_queue', false, true, false, false);

// Database connection details (optional for handling registration and login)
$dbHost = 'sql5.freesqldatabase.com';
$dbName = 'sql5736071';
$dbUser = 'sql5736071';
$dbPassword = 'DCVCqclHF3';
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle registration messages
$registrationCallback = function($msg) use ($conn) {
    $data = json_decode($msg->body, true);
    $username = $data['username'];
    $password = password_hash($data['password'], PASSWORD_BCRYPT); // Hash the password

    $sql = "INSERT INTO accounts (username, password) VALUES ('$username', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "New user registered: $username\n";
    } else {
        echo "Error: " . $sql . "\n" . $conn->error;
    }
};

// Handle login messages
$loginCallback = function($msg) use ($conn) {
    $data = json_decode($msg->body, true);
    $username = $data['username'];
    $password = $data['password'];

    $sql = "SELECT password FROM accounts WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            echo "Login successful for user: $username\n";
        } else {
            echo "Incorrect password for user: $username\n";
        }
    } else {
        echo "User not found: $username\n";
    }
};

// Consume messages from the registration queue
$channel->basic_consume('registration_queue', '', false, true, false, false, $registrationCallback);

// Consume messages from the login queue
$channel->basic_consume('login_queue', '', false, true, false, false, $loginCallback);

echo "Waiting for messages...\n";

// Keep the server running and waiting for messages
while ($channel->is_consuming()) {
    $channel->wait();
}

// Close the channel and the connection when done
$channel->close();
$connection->close();
$conn->close();
?>

