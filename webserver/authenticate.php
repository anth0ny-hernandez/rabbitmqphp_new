#!/usr/bin/php
<?php
require_once('../login.php.inc');
include('../testRabbitMQServer.php');
include('../testRabbitMQClient.php');


$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$request = array();
$request['type'] = "login";
$request['username'] = $username;
$request['password'] = $password;
$response = $client->send_request($request);
echo "received request".PHP_EOL;
var_dump($request);

?>
