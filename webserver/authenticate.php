<?php
require_once('login.php.inc');
include('testRabbitMQServer.php');
include('testRabbitMQClient.php');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

echo "hi";
$username = filter_input(INPUT_POST,'username');
$password = filter_input(INPUT_POST, 'password');

$request = array();
// $request['type'] = "login";
$request['username'] = $username; 
$request['password'] = $password;
$response = $client->send_request($request);

echo "received request".PHP_EOL;
print_r($response);
echo $response;

// function doLogin($username, $password){

//     $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');


//     $query = "select * FROM users WHERE username=?";
//     $statement = $db->prepare($query);
//     $statement->bind_param('s', $username);
//     $statement->execute();

//     $thepassword = $statement->get_result();
// }
?>
