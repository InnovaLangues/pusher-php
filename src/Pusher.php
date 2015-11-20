<?php

namespace Innova;

class Pusher
{
	
	protected $host;
	protected $port;
	protected $app;
	protected $key;
	protected $timeout;
	protected $url;

	public function __construct($host, $port, $app, $key, $secret, $timeout = null) {
		$this->host = $host;
		$this->port = $port;
		$this->app  = $app;
		$this->key  = $key;

		if($port !== null) {
			$this->url = $this->host . ":" . $this->port;
		} else {
			$this->url = $this->host;
		}
	}

	public function trigger($channels, $event, $data, $alreadyEncoded = false) {

		if(is_string($channels) === true ) {
			$channels = array($channels);
		}

		$this->validate_channels($channels);

		$dataEncoded = $alreadyEncoded ? $data : json_encode($data);

		print_r($dataEncoded);





		$hash = $this->buildHash($this->key, $this->secret, 'POST', $this->url, $params = array(), $timestamp = null);







		$client = new \GuzzleHttp\Client();

		# TODO check channels array etc...

		# TODO multi channel in one post ?
		foreach ($channels as $channel) {
			$response = $client->request(
				'POST', 
				$this->url, 
				[
				    'json' => [
				        'channel' => '/' . $this->app . $channel,
				        'data'    => [
				        	'hash'  => $hash,
				        	'key'   => $this->key,
				        	'event' => $event,
				        	'data'  => $dataEncoded
				        ]
			    ]
		    ]);
		}
		
		if ($response->getStatusCode() === 200)
		{
			echo 'OK';
			return true;
		} 
		else 
		{
			echo 'NOK';
			return false;
		}

	}

	public function getChannels() {

	}

	/**
	 * validate number of channels and channel name format.
	 */
	private function validate_channels($channels) 
	{
		if( count( $channels ) > 100 ) {
			throw new PusherException('An event can be triggered on a maximum of 100 channels in a single call.');
		}
		foreach ($channels as $channel) {
			$this->validate_channel($channel);
		}
	}

	/**
	 * Ensure a channel name is valid based on our spec
	 */
	private function validate_channel($channel)
	{
		/*if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $channel)) {
			throw new PusherException('Invalid channel name ' . $channel);
		}*/
	}

	private function buildHash($key, $secret, $method, $path, $params = array(), $timestamp = null) {
		$data = array(
			'method'    => strtoupper($method),
			'path'      => $path,
			'timestamp' => time()
		);

		ksort($data);

		$data = json_encode($data);

		echo $data;
		echo "+++";
		$hash = hash_hmac( 'sha256', $data, $secret, false );
		echo $hash;
		return $hash;
	}
}

class PusherException extends \Exception
{
}