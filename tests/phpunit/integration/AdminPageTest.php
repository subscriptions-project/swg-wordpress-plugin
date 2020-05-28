<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use PHPUnit_Framework_TestCase;
use SubscribeWithGoogle\WordPress\AdminPage;

class AdminPageTest extends PHPUnit_Framework_TestCase {

	public function test__adds_admin_page() {
		global $admin_page_hooks;

		new AdminPage();
		do_action( 'admin_menu' );

		$this->assertContains(
			'subscribe_with_google',
			array_keys( $admin_page_hooks )
		);
	}

	public function test__adds_admin_menu_item() {
		new AdminPage();
		do_action( 'admin_menu' );

		$this->assertNotEmpty( menu_page_url( 'subscribe_with_google', false ) );
	}

	public function test__admin_page__settings_sections() {
		global $wp_settings_sections;
		$this->assertEmpty( $wp_settings_sections );

		AdminPage::prepare();
		$this->assertEquals( 1, count(
			$wp_settings_sections['subscribe_with_google']
		) );
	}

	public function test__admin_page__renders_settings_form() {
		$this->expectOutputRegex(
			"/\<input type='hidden' name='option_page' value='subscribe_with_google' \/\>/"
		);

		AdminPage::render();
	}

	public function test__admin_page__renders_settings_fields__text() {
		$this->expectOutputString(
			'<input id="SubscribeWithGoogle_publication_id" name="SubscribeWithGoogle_publication_id" value="example.com"/><p class="description">Extra text</p>'
		);

		// Define publication ID.
		update_option( 'SubscribeWithGoogle_publication_id', 'example.com' );

		AdminPage::render_text_setting( array(
			'uid' => 'SubscribeWithGoogle_publication_id',
			'description' => 'Extra text',
			'value' => 'example.com'
		) );
	}

	public function test__admin_page__renders_settings_fields__textarea() {
		$this->expectOutputString(
			'<textarea id="SubscribeWithGoogle_publication_id" name="SubscribeWithGoogle_publication_id" style="min-height: 96px;">example.com</textarea><p class="description">Extra text</p>'
		);

		// Define publication ID.
		update_option( 'SubscribeWithGoogle_publication_id', 'example.com' );

		AdminPage::render_textarea_setting( array(
			'uid' => 'SubscribeWithGoogle_publication_id',
			'description' => 'Extra text',
			'value' => 'example.com'
		) );
	}

	public function test__admin_page__registers_settings_fields() {
		global $wp_registered_settings;

		AdminPage::prepare_settings();

		$keys = array_keys( $wp_registered_settings );
		$this->assertContains( 'SubscribeWithGoogle_products', $keys );
		$this->assertContains( 'SubscribeWithGoogle_publication_id', $keys );
		$this->assertContains( 'SubscribeWithGoogle_oauth_client_id', $keys );
		$this->assertContains( 'SubscribeWithGoogle_oauth_client_secret', $keys );
	}
}
