<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class PostEditTest extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		// Instantiate plugin.
		Plugin::load();

		// Reset site-wide options.
		delete_option( 'SubscribeWithGoogle_publication_id' );
	}

	public function test__adds_metabox() {
		global $wp_meta_boxes;

		Plugin::$instance->setup_post_edit_fields();

		$this->assertContains(
			'SubscribeWithGoogle_post-edit-metabox',
			array_keys( $wp_meta_boxes['post']['advanced']['high'] )
		);
	}

	public function test__renders_metabox__no_products__asks_for_them() {
		$this->expectOutputRegex(
			"/Please define products on the SwG setup page 😄/"
		);

		Plugin::$instance->render_post_edit_fields();
	}

	public function test__renders_metabox__products_dropdown() {
		$this->expectOutputRegex(
			'/\<option value="premium" checked\>premium\<\/option\>/'
		);

		// Define products.
		update_option( 'SubscribeWithGoogle_products', "basic\npremium" );

		Plugin::$instance->render_post_edit_fields();
	}
}
