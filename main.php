<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Pusher.php';

use Innova\Pusher as Pusher;

$host = "http://localhost";
$port = "8001";

$app = "1278690433";
$key = "98f0b777783fe1bca391";
$secret = "none";

$channels = array(
	'/messages'
);

$event = "my-event";

$data = array(
	'title' => "This is the title",
	'body' => "This is the body!"
);

$pusher = new Pusher($host, $port, $app, $key, $secret);

$pusher->trigger($channels, $event, $data);