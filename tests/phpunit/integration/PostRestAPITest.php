<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use WP_UnitTestCase;
use SubscribeWithGoogle\WordPress\GoogleSignIn;
use SubscribeWithGoogle\WordPress\PostRestAPI;
use WP_REST_Request;
use WP_REST_Server;

class PostRestAPITest extends WP_UnitTestCase
{

	public $post_id;

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

		// Always set a refresh token
		$_COOKIE['swg_refresh_token'] = 'refresh_token';
		GoogleClientMock::$access_token_response = array(
			'access_token' => 'access_token',
		);

		// Mock the Google Sign In class
		GoogleSignInMock::reset();
		PostRestAPI::$google_sign_in_class =
			'SubscribeWithGoogle\WordPress\Tests\GoogleSignInMock';

		// Set site URL.
		update_option('siteurl', 'https://do.ma.in/pa/th');

		// Define publication ID.
		update_option('SubscribeWithGoogle_publication_id', 'example.com');

		// Set referer.
		$_SERVER['HTTP_REFERER'] = 'https://do.ma.in/pa/th/y';

		// Create a post.
		$this->post_id = $this->factory->post->create();
		wp_update_post(array(
			'ID' => $this->post_id,
			'post_content' => '<h1>Hello world!</h1>',
			'post_name' => 'paid-post',
			'post_title' => 'Paid post',
		));

		// Make it premium
		update_post_meta($this->post_id, 'SubscribeWithGoogle_product', 'premium');
	}

	public function tearDown()
	{
		$_COOKIE = [];
		parent::tearDown();
	}

	public function test__prevent_fetching_of_content_if_the_user_is_missing_entitlements()
	{
		// Set our mock entitlements
		GoogleSignInMock::$entitlements = [];

		$request = new WP_REST_Request(
			'GET',
			'/wp/v2/posts/' . $this->post_id
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals(
			401,
			$response->data['data']['status']
		);
	}

	public function test__allow_fetching_of_content_if_the_user_has_proper_entitlements()
	{
		// Set our mock entitlements
		$entitlementsJson = '{"entitlements" : [ {"products" : ["example.com:premium"]} ]}';
		GoogleSignInMock::$entitlements = json_decode($entitlementsJson);

		$request = new WP_REST_Request(
			'GET',
			'/wp/v2/posts/' . $this->post_id
		);

		$response = $this->server->dispatch($request);
		$this->assertEquals(
			'<h1>Hello world!</h1>',
			trim($response->data)
		);
	}

	public function test__prevents_fetching_of_content_if_the_user_has_improper_entitlements()
	{
		// Set our mock entitlements
		$entitlementsJson = '{"entitlements" : [ {"products" : ["example.com:basic"]} ]}';
		GoogleSignInMock::$entitlements = json_decode($entitlementsJson);

		$request = new WP_REST_Request(
			'GET',
			'/wp/v2/posts/' . $this->post_id
		);

		$response = $this->server->dispatch($request);
		$this->assertEquals(
			401,
			$response->data['data']['status']
		);
	}
}
