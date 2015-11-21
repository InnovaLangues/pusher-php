<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Pusher.php';

use Innova\Pusher as Pusher;

$url = "http://localhost:8001";

$app = "1278690433";
$key = "98f0b777783fe1bca391";
$secret = "mysupersecret";

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