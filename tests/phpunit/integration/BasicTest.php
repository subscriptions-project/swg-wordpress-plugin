<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class BasicTest extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		// Instantiate plugin.
		Plugin::load();
	}

	public function test__load() {
		Plugin::$instance = null;

		// First load attempt should create an instance.
		Plugin::load();
		$this->assertNotNull( Plugin::$instance );

		// Second load attempt should be a no-op.
		Plugin::load();
	}

	public function test__admin_menu() {
		do_action( 'admin_menu' );

		$this->assertNotEmpty( menu_page_url( 'subscribe_with_google', false ) );
	}
}
