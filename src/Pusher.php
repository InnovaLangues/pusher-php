<?php

namespace Innova;

//TODO timeout

class Pusher
{
	protected $app;
	protected $key;
	protected $secret;
	protected $scheme;
	protected $host;
	protected $url;

	public function __construct($key, $secret, $app, $url) {
		$this->checkCompatibility();

		$this->key    = $key;
		$this->secret = $secret;
		$this->app    = $app;
		$this->url    = $url;

		$match = null;
		preg_match("/(http[s]?)\:\/\/(.*)/", $url, $match);

		if( count( $match ) === 3 ) {
			$this->scheme = $match[ 1 ];
			$this->host   = $match[ 2 ];
		}
	}

	public function checkCompatibility() {
		if (!extension_loaded( 'json' ))
		{
			throw new PusherException('There is amissing dependant extension - please ensure that JSON is installed');
		}
		if (!in_array('sha256', hash_algos()))
		{
			throw new PusherException('SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.');
		}
	}

	public function trigger($channels, $event, $data, $alreadyEncoded = false) 
	{
		if(is_string($channels) === true ) {
			$channels = array($channels);
		}

		$this->validateChannels($channels);

		$dataEncoded = $alreadyEncoded ? $data : json_encode($data);

		$client = new \GuzzleHttp\Client();

		# TODO check channels array etc...

		# TODO multi channel in one post ?
		foreach ($channels as $channel) {

			$postParams = array();
			$postParams['name']    = $event;
			$postParams['data']    = $dataEncoded;
			$postParams['channel'] = $channel;

			$jsonBody = json_encode($postParams);

			$bodyMd5 = md5($jsonBody);

			//echo $this->secret;

			$path = '/apps/' . $this->app . '/events';
			
			$queryString = $this->buildQueryString($this->key, $this->secret, 'POST', $path, $timestamp = null);

			$url = $this->url . $path . '?hash=' . $bodyMd5 . '&' .$queryString;

			$response = $client->request(
				'POST', 
				$url,
				[
				    'json' => [
				        'name'    => $event,
				        'data'    => $dataEncoded,
				        'channel' => $channel,
			    	]
		    	]
		    );
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
	private function validateChannels($channels) 
	{
		if( count( $channels ) > 100 ) {
			throw new PusherException('An event can be triggered on a maximum of 100 channels in a single call.');
		}
		foreach ($channels as $channel) {
			$this->validateChannel($channel);
		}
	}

	/**
	 * Ensure a channel name is valid based on our spec
	 */
	private function validateChannel($channel)
	{
		/*if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $channel)) {
			throw new PusherException('Invalid channel name ' . $channel);
		}*/
	}

	private function buildQueryString($key, $secret, $method, $path, $timestamp = null) 
	{
		$params = array();

		$params['key'] = $key;
		$params['timestamp'] = (is_null($timestamp)?time() : $timestamp);

		ksort($params);

		$method = strtoupper($method);

		$stringToSign = "$method\n" . $path . "\n" . $this->arrayImplode( '=', '&', $params );

		echo($stringToSign);

		$signature = hash_hmac('sha256', $stringToSign, $secret, false);

		$params['signature'] = $signature;
		ksort($params);
		$queryString = $this->arrayImplode('=', '&', $params);
		
		return $queryString;
	}

	private function arrayImplode($glue, $separator, $array) 
	{
		if (!is_array($array)) {
			return $array;
		}

		$string = array();

		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$val = implode( ',', $val );
			}

			$string[] = "{$key}{$glue}{$val}";
		}

		return implode( $separator, $string );
	}
}

class PusherException extends \Exception
{
}