<?php 

namespace satusehat\integration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

class SatuSehatAuth
{
	public string $auth_url;
	
	public string $base_url;
	
	public string $client_id;
	
	public string $client_secret;
	
	public string $organization_id;

  public function __construct()
  {
	  $this->auth_url        = xxxx;
	  $this->base_url        = xxxx;
	  $this->client_id       = xxxx;
	  $this->client_secret   = xxxx;
	  $this->organization_id = xxxx;
	  
	  if ($this->organization_id == null) {
		  return 'Add your organization_id at environment first';
	  }
  }
	
	public function token()
	{
		$token = SatusehatToken::where('environment', getenv('SATUSEHAT_ENV'))->orderBy('created_at', 'desc')
		                       ->where('created_at', '>', now()->subMinutes(50))->first();
		
		if ($token) {
			return $token->token;
		}
		
		$client = new Client();
		
		$headers = [
		 'Content-Type' => 'application/x-www-form-urlencoded',
		];
		$options = [
		 'form_params' => [
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
		 ],
		];
		
		// Create session
		$url = $this->auth_url.'/accesstoken?grant_type=client_credentials';
		$request = new Request('POST', $url, $headers);
		
		try {
			$res = $client->sendAsync($request, $options)->wait();
			$contents = json_decode($res->getBody()->getContents());
			
			if (isset($contents->access_token)) {
				SatusehatToken::create([
				                        'environment' => getenv('SATUSEHAT_ENV'),
				                        'token' => $contents->access_token,
				                       ]);
				
				return $contents->access_token;
			} else {
				// return $this->respondError($oauth2);
				return null;
			}
		} catch (ClientException $e) {
			// error.
			$res = json_decode($e->getResponse()->getBody()->getContents());
			$issue_information = $res->issue[0]->details->text;
			
			return $issue_information;
		}
	}

}