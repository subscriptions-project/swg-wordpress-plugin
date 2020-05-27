<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use PHPUnit_Framework_TestCase;
use SubscribeWithGoogle\WordPress\Admin;
use SubscribeWithGoogle\WordPress\Plugin;

class AdminTest extends PHPUnit_Framework_TestCase {

	public function test__adds_admin_page() {
		global $admin_page_hooks;

		new Admin();
		do_action( 'admin_menu' );

		$this->assertContains(
			'subscribe_with_google',
			array_keys( $admin_page_hooks )
		);
	}

	public function test__adds_admin_menu_item() {
		new Admin();
		do_action( 'admin_menu' );

		$this->assertNotEmpty( menu_page_url( 'subscribe_with_google', false ) );
	}

	public function test__admin_page__settings_sections() {
		global $wp_settings_sections;
		$this->assertEmpty( $wp_settings_sections );

		Admin::setup_sections();
		$this->assertEquals( 1, count(
			$wp_settings_sections['subscribe_with_google']
		) );
	}

	public function test__admin_page__renders_settings_form() {
		$this->expectOutputRegex(
			"/\<input type='hidden' name='option_page' value='subscribe_with_google' \/\>/"
		);

		Admin::plugin_settings_page_content();
	}

	public function test__admin_page__renders_settings_fields__text() {
		$this->expectOutputString(
			'<input name="SubscribeWithGoogle_publication_id" id="SubscribeWithGoogle_publication_id" type="text" placeholder="Add products here" value="example.com" /><p class="description">Extra text</p>'
		);

		// Define publication ID.
		update_option( 'SubscribeWithGoogle_publication_id', 'example.com' );

		Admin::field_callback( array(
			'uid' => 'SubscribeWithGoogle_publication_id',
			'type' => 'text',
			'placeholder' => 'Add products here',
			'supplemental' => 'Extra text',
		) );
	}

	public function test__admin_page__renders_settings_fields__textarea() {
		$this->expectOutputString(
			'<textarea style="min-height: 96px;" name="SubscribeWithGoogle_publication_id" id="SubscribeWithGoogle_publication_id" placeholder="Add products here">example.com</textarea>'
		);

		// Define publication ID.
		update_option( 'SubscribeWithGoogle_publication_id', 'example.com' );

		Admin::field_callback( array(
			'uid' => 'SubscribeWithGoogle_publication_id',
			'type' => 'textarea',
			'placeholder' => 'Add products here',
		) );
	}

	public function test__admin_page__renders_settings_fields__chart() {
		$this->expectOutputString('📊 📈');

		Admin::field_callback( array(
			'type' => 'chart',
			'uid' => 'TODO: Make actual charts 😂',
		) );
	}

	public function test__admin_page__registers_settings_fields() {
		global $wp_registered_settings;

		Admin::setup_fields();

		$keys = array_keys( $wp_registered_settings );
		$this->assertContains( 'SubscribeWithGoogle_products', $keys );
		$this->assertContains( 'SubscribeWithGoogle_publication_id', $keys );
		$this->assertContains( 'SubscribeWithGoogle_oauth_client_id', $keys );
		$this->assertContains( 'SubscribeWithGoogle_oauth_client_secret', $keys );
	}
}
