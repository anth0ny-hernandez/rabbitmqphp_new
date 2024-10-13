<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Parse the .ini file for RabbitMQ connection settings
$config = parse_ini_file('testRabbitMQ.ini');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Connect to RabbitMQ server using the parsed config
    $connection = new AMQPStreamConnection(
        $config['host'],      // Host from .ini file
        $config['port'],      // Port from .ini file
        $config['user'],      // Username from .ini file
        $config['password'],  // Password from .ini file
        $config['vhost']      // Virtual host from .ini file
    );
    
    // Create a channel
    $channel = $connection->channel();
    
    // Declare the login queue
    $channel->queue_declare('login_queue', false, true, false, false);
    
    // Create a message payload with the user data
    $msg_body = json_encode([
        'username' => $username,
        'password' => $password  // Password will be verified server-side
    ]);
    $msg = new AMQPMessage($msg_body);
    
    // Send the message to the login queue
    $channel->basic_publish($msg, '', 'login_queue');
    
    echo "Login request sent to RabbitMQ.\n";

    // Close the channel and connection
    $channel->close();
    $connection->close();
}
?>

<!-- Login Form -->
<form action="login.php" method="POST">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Login">
</form>

