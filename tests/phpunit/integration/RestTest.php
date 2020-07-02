<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use PHPUnit_Framework_TestCase;
use SubscribeWithGoogle\WordPress\Rest;
use WP_REST_Server;

class RestTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		parent::setUp();

		// Set site URL.
		update_option('siteurl', 'https://do.ma.in/pa/th');
	}

	public function test__verify_request_origin__no_referer__throws()
	{
		$_SERVER['HTTP_REFERER'] = null;

		$this->expectExceptionMessage('Request has no referer');
		Rest::verify_request_origin();
	}

	public function test__verify_request_origin__invalid_scheme__throws()
	{
		$_SERVER['HTTP_REFERER'] = 'http://do.ma.in/pa/th';

		$this->expectExceptionMessage('Request scheme was not valid');
		Rest::verify_request_origin();
	}

	public function test__verify_request_origin__invalid_host__throws()
	{
		$_SERVER['HTTP_REFERER'] = 'https://d0.ma.in/pa/th';

		$this->expectExceptionMessage('Request host was not valid');
		Rest::verify_request_origin();
	}
}
