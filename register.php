#!/usr/bin/php
<html>
<h1>hi</h1>
</html>
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');

$request = array();
$request['type'] = "register";
$request['username'] = $username ;
$request['password'] = $password;
$request['message'] = "HI";
$response = $client->send_request($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

?>