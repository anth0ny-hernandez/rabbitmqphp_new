<?php
// Include necessary files for RabbitMQ communication
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function sendRegisterRequest($username, $password) {
    // Create a RabbitMQ client
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Prepare the request payload
    $request = array();
    $request['type'] = "register";
    $request['username'] = $username;
    $request['password'] = $password;

    // Send the request to the RabbitMQ server and get a response
    $response = $client->send_request($request);
    return $response;
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if both fields are filled
    if (empty($username) || empty($password)) {
        echo "Username and password are required";
    } else {
        // Send the registration request to RabbitMQ
        $result = sendRegisterRequest($username, $password);
        echo $result;
    }
}
?>

<!-- Simple HTML form for user registration -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A.R.A.Y Homepage</title>
    <link rel="stylesheet" href="Stylepage.css">
</head>
<body>
    <header>
        Welcome to A.R.A.Y Test page
    </header>

    <section id="home" class="section">
        <div class="content">
            <h1>Testing Testing 123</h1>
            <p>This is a simple front-end test Homepage.</p>
        </div>
    </section>

    <section id="register" class="section">
        <div class="content">
            <h1>Register Here</h1>
            <p>This is a registration</p>
            <form method="POST" action="register.php">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <br>
                <button type="submit">Register</button>
                </form>
        </div>
    </section>


    <section id="about" class="section">
        <h2>About Us</h2>
        <p>We are a team of students making and this a test page for IT 490</p>
    </section>

    <section id="services" class="section">
        <h2>Our Services</h2>
        <p>We offer a wide range of services TBA</p>
    </section>

    <section id="contact" class="section">
        <h2>Contact Us</h2>
        <p>Email us at</p>
    </section>

    <footer>
        <nav>
            <ul>
                <li>Links:</li>
                <li><a href="#home">Homepage</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#services">Our Services</a></li>
                <li><a href="#contact">Contact Us</a></li>
            </ul>
        </nav>
    </footer>
</body>
</html>


