<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class BasicTest extends \WP_UnitTestCase {
	public function setUp() {
		error_log( '🏃 BasicTest' );
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
}
