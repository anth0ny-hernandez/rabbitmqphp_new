#!/usr/bin/php
<?php
require_once('../login.php.inc');
include('../testRabbitMQServer.php');
include('../testRabbitMQClient.php');
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

echo "hi";
$username = $_POST['username'];
$password = $_POST['password'];

$request = array();
$request['type'] = "login";
$request['username'] = $username; 
$request['password'] = $password;
$response = $client->send_request($request);
echo "received request".PHP_EOL;
print_r($response);


function doLogin($username, $password){

    $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');


    $query = "select * FROM users WHERE username=:username";
    $statement = $db->prepare($query);
    $statement->bind_param(':username', $username);
    $statement->execute();

    $thepassword = $statement->get_result();
}
?>
