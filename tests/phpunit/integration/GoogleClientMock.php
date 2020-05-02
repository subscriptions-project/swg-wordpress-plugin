<?php

namespace SubscribeWithGoogle\WordPress\Tests;


/** Mock of the Google Client. */
class GoogleClientMock {

	public static $access_token_response;
	public static $instance;

	public static function reset() {
		GoogleClientMock::$access_token_response = null;
		GoogleClientMock::$instance = null;
	}

	public function __construct() {
		$this::$instance = $this;
	}

	public function setClientId( $client_id ) {
		$this->client_id = $client_id;
	}

	public function setClientSecret( $client_secret ) {
		$this->client_secret = $client_secret;
	}

	public function setRedirectUri( $redirect_uri ) {
		$this->redirect_uri = $redirect_uri;
	}

	public function fetchAccessTokenWithRefreshToken( $refresh_token ) {
		$this->refresh_token = $refresh_token;
		return $this::$access_token_response;
	}
}
