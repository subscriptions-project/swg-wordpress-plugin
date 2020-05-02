<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\GoogleSignIn;
use WP_REST_Request;
use WP_REST_Server;
use WP_UnitTestCase;

class GoogleSignInTest extends WP_UnitTestCase {

	public function setUp() {
		error_log( '🏃 GoogleSignInTest' );
		parent::setUp();

		// Start REST server.
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		// Mock the Google Client.
		GoogleSignIn::$google_client_class = 'SubscribeWithGoogle\WordPress\Tests\GoogleClientMock';

		// Clear cookies.
		$_COOKIE = [];
	}

	public function test__entitlements__missing_cookie__throws() {
		unset( $_COOKIE['swg_refresh_token'] );

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/entitlements'
		);

		$this->expectExceptionMessage( 'swg_refresh_token COOKIE was missing' );
		$this->server->dispatch( $request );
	}

	public function test__entitlements__could_not_fetch_access_token__throws() {
		$_COOKIE['swg_refresh_token'] = 'token';

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/entitlements'
		);

		$this->expectExceptionMessage( 'Access token could not be fetched' );
		$this->server->dispatch( $request );
	}
}


/** Mock of the Google Client. */
class GoogleClientMock {
	public static $instance;
	public static $access_token_response;

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
