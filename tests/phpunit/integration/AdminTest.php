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

	public function test__admin_page__settings_form() {
		$this->expectOutputRegex(
			"/\<input type='hidden' name='option_page' value='subscribe_with_google' \/\>/"
		);

		Plugin::$instance->plugin_settings_page_content();
	}

	public function test__admin_page__settings_fields() {
		global $wp_registered_settings;

		Plugin::$instance->setup_fields();

		$keys = array_keys( $wp_registered_settings );
		$this->assertContains( 'SubscribeWithGoogle_publication_id', $keys );
		$this->assertContains( 'SubscribeWithGoogle_products', $keys );
		$this->assertContains( 'SubscribeWithGoogle_chart', $keys );
	}
}
