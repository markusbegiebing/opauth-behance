<?php
/**
 * Behance strategy for Opauth
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @link         http://opauth.org
 * @package      Opauth.BehanceStrategy
 * @license      MIT License
 */

/**
 * Behance strategy for Opauth
 * 
 * @package			Opauth.Behance
 */
class BehanceStrategy extends OpauthStrategy{
	
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = array('client_id', 'client_secret');
	
	/**
	 * Optional config keys, without predefining any default values.
	 */
	public $optionals = array('redirect_uri', 'scope', 'state');
	
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}oauth2callback',
		'scope' => 'post_as|activity_read|collection_read|collection_write|wip_read|wip_write|project_read|invitations_read|invitations_write',
		'state' => null
	);
	
	/**
	 * Auth request
	 */
	public function request(){
		
		if (!$this->strategy['state']) {
			$this->strategy['state'] = String::uuid();
		}
		
		$_SESSION['Behance.state'] = $this->strategy['state'];
		
		$url = 'https://www.behance.net/v2/oauth/authenticate';
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => Router::url($this->strategy['redirect_uri'], array('full' => true)),
			'scope' => $this->strategy['scope'],
			'state' => $this->strategy['state']
		);
		
		$this->clientGet($url, $params);
	}
	
	/**
	 * Internal callback, after OAuth
	 */
	public function oauth2callback(){
		
		if (array_key_exists('state', $_GET) && !empty($_GET['state'])){
			if ($_GET['state'] == $_SESSION['Behance.state']) {	
				if (array_key_exists('code', $_GET) && !empty($_GET['code'])){
					
					$code = $_GET['code'];
					$url = 'https://www.behance.net/v2/oauth/token';
					$params = array(
						'code' => $code,
						'client_id' => $this->strategy['client_id'],
						'client_secret' => $this->strategy['client_secret'],
						'redirect_uri' => Router::url($this->strategy['redirect_uri'], array('full' => true)),
						'grant_type' => 'authorization_code'
					);
					
					// Server Post
					$query = http_build_query($params, '', '&');

					$stream = array('http' => array(
						'method' => 'POST',
						'header' => implode("\r\n", array(
							'Content-type: application/x-www-form-urlencoded',
							'User-Agent: Generic' // Behance requires User Agent to be present during all API requests
						)),
						'content' => $query
					));
					
					$response = $this->httpRequest($url, $stream, $headers);
					
					$results = json_decode($response);
					
					if (!empty($results) && !empty($results->access_token)){
						//debug($results);exit;
						
						$this->auth = array(
							'uid' => $results->user->id,
							'user' => $results->user,
							'credentials' => array(
								'token' 		=> $results->access_token,
								'behance_id'	=> $results->user->id,
								'first_name'	=> $results->user->first_name,
								'last_name'		=> $results->user->last_name,
								'username'		=> $results->user->username,
								'city'			=> $results->user->city,
								'state'			=> $results->user->state,
								'country'		=> $results->user->country,
								'company'		=> $results->user->company,
								'occupation'	=> $results->user->occupation,
								'created_on'	=> $results->user->created_on,
								'url'			=> $results->user->url,
								'display_name'	=> $results->user->display_name
							),
							'raw' => $results
						);
						
						$this->callback();
					} else{
						$error = array(
							'code' => 'access_token_error',
							'message' => 'Failed when attempting to obtain access token',
							'raw' => array(
								'response' => $response,
								'headers' => $headers
							)
						);
		
						$this->errorCallback($error);
					}
				} else{
					$error = array(
						'code' => 'oauth2callback_error',
						'raw' => $_GET
					);
					
					$this->errorCallback($error);
				}
			} else {
				$error = array(
					'code' => 'oauth2callback_error',
					'message' => 'State is not identical - Token could have been tampered with',
					'raw' => $_GET
				);
				
				$this->errorCallback($error);
			}
		} else {
			$error = array(
				'code' => 'oauth2callback_error',
				'message' => 'Authentication aborted by user',
				'raw' => $_GET
			);
			
			$this->errorCallback($error);
		}
	}
	
}