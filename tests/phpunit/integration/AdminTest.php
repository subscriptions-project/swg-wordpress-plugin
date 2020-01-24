<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class AdminTest extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		// Instantiate plugin.
		Plugin::load();
	}

	public function test__adds_admin_page() {
		global $admin_page_hooks;

		do_action( 'admin_menu' );

		$this->assertContains(
			'subscribe_with_google',
			array_keys( $admin_page_hooks )
		);
	}

	public function test__adds_admin_menu_item() {
		do_action( 'admin_menu' );

		$this->assertNotEmpty( menu_page_url( 'subscribe_with_google', false ) );
	}

	public function test__admin_page__settings_sections() {
		global $wp_settings_sections;
		$this->assertEmpty( $wp_settings_sections );

		Plugin::$instance->setup_sections();
		$this->assertEquals( 2, count(
			$wp_settings_sections['subscribe_with_google']
		) );
	}
}
