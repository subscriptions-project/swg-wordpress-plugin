<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use WP_UnitTestCase;
use SubscribeWithGoogle\WordPress\GoogleSignIn;
use SubscribeWithGoogle\WordPress\RegisterWithGoogleSignIn;
use WP_REST_Request;
use WP_REST_Server;

class RegisterUserText extends WP_UnitTestCase
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

		// Always set a refresh token
		$_COOKIE['swg_refresh_token'] = 'refresh_token';
		GoogleClientMock::$access_token_response = array(
			'access_token' => 'access_token',
		);

		// Mock the Google Sign In class
		GoogleSignInMock::reset();
		RegisterWithGoogleSignIn::$google_sign_in_class =
			'SubscribeWithGoogle\WordPress\Tests\GoogleSignInMock';

		// Set site URL.
		update_option('siteurl', 'https://do.ma.in/pa/th');

		// Define publication ID.
		update_option('SubscribeWithGoogle_publication_id', 'example.com');

		// Set referer.
		$_SERVER['HTTP_REFERER'] = 'https://do.ma.in/pa/th/y';
	}

	public function test__it_finds_a_user_when_looking_them_up_by_google_id_and_logs_them_in(){
		$mockToken = '123456789';
		$user_id = wp_create_user('Test Name', 'password', 'email@example.com');
		update_user_meta($user_id, 'google_id', $mockToken);
		$user = get_user_by('id', $user_id);

		$request = new WP_REST_Request(
			'POST',
			'/subscribe-with-google/v1/register-user'
		);

		$request->set_body(json_encode([
			"google_id_token" => $mockToken,
		]));

		//Send the request
		$this->server->dispatch($request);

		$currentUser = wp_get_current_user();

		$this->assertEquals($currentUser->ID, $user_id);
	}

	public function test__it_creates_a_new_user_and_logs_them_in_if_they_dont_exist(){
		$mockToken = '20934857209348';
		$user_id = wp_create_user('Test Name', 'password', 'email@example.com');
		update_user_meta($user_id, 'google_id', $mockToken);
		$user = get_user_by('id', $user_id);

		$request = new WP_REST_Request(
			'POST',
			'/subscribe-with-google/v1/register-user'
		);

		$request->set_body(json_encode([
			"google_id_token" => $mockToken,
			"username" => "Test",
			"email" => "test@place.com",
			"name" => "Test"
		]));

		//Send the request
		$this->server->dispatch($request);

		$currentUser = wp_get_current_user();

		$this->assertNotEquals($currentUser->ID, $user_id);
		$this->assertEquals($currentUser->user_email, "test@place.com");
	}
}
