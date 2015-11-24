<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Pusher.php';

use Innova\Pusher as Pusher;

$url = "http://localhost:8001";

$app = "1";
$key = "efe1bba83789098f0a6c";
$secret = "b03c03dddf8983b76479";

$channels = array(
	'/messages'
);

$event = "my-evenTTTTt";

$data = array(
	'title' => "This is the title",
	'body' => "This is the body!"
);

$pusher = new Pusher($key, $secret, $app, $url);

$pusher->trigger($channels, $event, $data);