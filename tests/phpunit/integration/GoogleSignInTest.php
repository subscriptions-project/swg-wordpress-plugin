<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use PHPUnit_Framework_TestCase;
use SubscribeWithGoogle\WordPress\GoogleSignIn;
use WP_REST_Request;
use WP_REST_Server;

class GoogleSignInTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		parent::setUp();

		// Start REST server.
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		// Mock the Google Client.
		GoogleClientMock::reset();
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

	public function test__entitlements__returns_entitlements() {
		$_COOKIE['swg_refresh_token'] = 'refresh_token';
		GoogleClientMock::$access_token_response = array(
			'access_token' => 'access_token',
		);

		add_filter('pre_http_request', function() {
			return array(
				'body' => '{"entitlements":[{"products":["premium"]}]}',
			);
		});

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/entitlements'
		);

		$response = $this->server->dispatch( $request );
		$this->assertEquals(
			'{"entitlements":[{"products":["premium"]}]}',
			$response->data
		);
	}
}
