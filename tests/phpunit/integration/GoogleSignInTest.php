<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use PHPUnit_Framework_TestCase;
use SubscribeWithGoogle\WordPress\GoogleSignIn;
use WP_REST_Request;
use WP_REST_Server;

class GoogleSignInTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		parent::setUp();

		// Start REST server.
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action('rest_api_init');

		// Mock the Google Client.
		GoogleClientMock::reset();
		GoogleSignIn::$google_client_class = 'SubscribeWithGoogle\WordPress\Tests\GoogleClientMock';

		// Clear cookies.
		$_COOKIE = [];

		// Set site URL.
		update_option('siteurl', 'https://do.ma.in/pa/th');

		// Set referer.
		$_SERVER['HTTP_REFERER'] = 'https://do.ma.in/pa/th/y';
	}

	public function test__get_entitlements__invalid_referer__throws()
	{
		$_SERVER['HTTP_REFERER'] = null;

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/entitlements'
		);

		$this->expectExceptionMessage('Request has no referer');
		$this->server->dispatch($request);
	}

	public function test__get_entitlements__missing_cookie__throws()
	{
		unset($_COOKIE['swg_refresh_token']);

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/entitlements'
		);

		$this->expectExceptionMessage('swg_refresh_token COOKIE was missing');
		$this->server->dispatch($request);
	}

	public function test__get_entitlements__could_not_fetch_access_token__throws()
	{
		$_COOKIE['swg_refresh_token'] = 'token';

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/entitlements'
		);

		$this->expectExceptionMessage('Access token could not be fetched');
		$this->server->dispatch($request);
	}

	public function test__get_entitlements__returns_entitlements()
	{
		$_COOKIE['swg_refresh_token'] = 'refresh_token';
		GoogleClientMock::$access_token_response = array(
			'access_token' => 'access_token',
		);

		add_filter('pre_http_request', function () {
			return array(
				'body' => '{"entitlements":[{"products":["premium"]}]}',
			);
		});

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/entitlements'
		);

		$response = $this->server->dispatch($request);
		$this->assertEquals(
			json_decode('{"entitlements":[{"products":["premium"]}]}'),
			$response->data
		);
	}

	public function test__get_grant_status__invalid_referer__returns_grant_false()
	{
		$_SERVER['HTTP_REFERER'] = null;
		$_COOKIE['swg_refresh_token'] = 'refresh_token';
		GoogleClientMock::$access_token_response = array(
			'access_token' => 'access_token',
		);

		add_filter('pre_http_request', function () {
			return array(
				'body' => '{"entitlements":[{"products":["premium"]}]}',
			);
		});

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/grant-status'
		);

		$response = $this->server->dispatch($request);
		$this->assertFalse($response->data['granted']);
	}

	public function test__get_grant_status__returns_grant_true()
	{
		$_COOKIE['swg_refresh_token'] = 'refresh_token';
		GoogleClientMock::$access_token_response = array(
			'access_token' => 'access_token',
		);

		add_filter('pre_http_request', function () {
			return array(
				'body' => '{"entitlements":[{"products":["premium"]}]}',
			);
		});

		$request = new WP_REST_Request(
			'GET',
			'/subscribewithgoogle/v1/grant-status'
		);
		$request->set_query_params(array('product' => 'premium'));

		$response = $this->server->dispatch($request);
		$this->assertTrue($response->data['granted']);
	}

	public function test__create_1p_gsi_cookie__invalid_referer__throws()
	{
		$_SERVER['HTTP_REFERER'] = null;

		$request = new WP_REST_Request(
			'POST',
			'/subscribewithgoogle/v1/create-1p-gsi-cookie'
		);

		$this->expectExceptionMessage('Request has no referer');
		$this->server->dispatch($request);
	}

	public function test__create_1p_gsi_cookie__missing_param__throws()
	{
		$request = new WP_REST_Request(
			'POST',
			'/subscribewithgoogle/v1/create-1p-gsi-cookie'
		);

		$this->expectExceptionMessage('gsi_auth_code POST param is missing');
		$this->server->dispatch($request);
	}

	public function test__create_1p_gsi_cookie__could_not_fetch_refresh_token__throws()
	{
		$request = new WP_REST_Request(
			'POST',
			'/subscribewithgoogle/v1/create-1p-gsi-cookie'
		);
		$request->set_body_params(array(
			'gsi_auth_code' => '123',
		));

		$this->expectExceptionMessage('Refresh token could not be fetched');
		$this->server->dispatch($request);
	}

	public function test__create_1p_gsi_cookie__sets_cookie()
	{
		$request = new WP_REST_Request(
			'POST',
			'/subscribewithgoogle/v1/create-1p-gsi-cookie'
		);
		$request->set_body_params(array(
			'gsi_auth_code' => '123',
		));
		$refresh_token = 'refresh_token_1234567';
		GoogleClientMock::$access_token_response = array(
			'refresh_token' => $refresh_token,
		);

		$response = $this->server->dispatch($request);
		$this->assertContains('swg_refresh_token=' . $refresh_token, $response->headers['Set-Cookie']);
		$this->assertContains(' secure;', $response->headers['Set-Cookie']);
		$this->assertContains(' HttpOnly;', $response->headers['Set-Cookie']);
	}
}
