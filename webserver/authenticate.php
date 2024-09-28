<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');
include('testRabbitMQServer.php');
include('../testRabbitMQClient.php');


$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$request['username'] = $username;
$request['password'] = $password;


$request = array();


?>