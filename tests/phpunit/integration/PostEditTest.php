<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\PostEdit;
use WP_UnitTestCase;

class PostEditTest extends WP_UnitTestCase {

	private $post_edit = null;

	public function setUp() {
		parent::setUp();

		$this->post_edit = new PostEdit();
	}

	public function test__adds_metabox() {
		global $wp_meta_boxes;

		$this->post_edit->add_meta_boxes();

		$this->assertContains(
			'SubscribeWithGoogle_post-edit-metabox',
			array_keys( $wp_meta_boxes['post']['advanced']['high'] )
		);
	}

	public function test__renders_metabox__no_products__asks_for_them() {
		$this->expectOutputRegex(
			"/Please define products on the SwG setup page 😄/"
		);

		$this->post_edit->render_post_edit_fields();
	}

	public function test__renders_metabox__products_dropdown() {
		$this->expectOutputRegex(
			'/\<option value="premium"\>premium\<\/option\>/'
		);

		// Define products.
		update_option( 'SubscribeWithGoogle_products', "basic\npremium" );

		$this->post_edit->render_post_edit_fields();
	}

	public function test__renders_metabox__products_dropdown__with_selection() {
		$this->expectOutputRegex(
			'/\<option value="premium" selected\>premium\<\/option\>/'
		);

		// Define products.
		update_option( 'SubscribeWithGoogle_products', "basic\npremium" );

		// Select product for post.
		$post_id = $this->factory->post->create();
		$this->go_to( "/?p=$post_id" );
		update_post_meta( $post_id, 'SubscribeWithGoogle_product', 'premium' );

		$this->post_edit->render_post_edit_fields();
	}

	public function test__renders_metabox__free_checkbox__unchecked() {
		$this->expectOutputRegex(
			'/<input id="SubscribeWithGoogle_free" name="SubscribeWithGoogle_free" type="checkbox" value="true"\/>/'
		);

		// Define products.
		update_option( 'SubscribeWithGoogle_products', "basic\npremium" );

		$this->post_edit->render_post_edit_fields();
	}

	public function test__renders_metabox__free_checkbox__checked() {
		$this->expectOutputRegex(
			'/<input id="SubscribeWithGoogle_free" name="SubscribeWithGoogle_free" type="checkbox" value="true" checked\/>/'
		);

		// Define products.
		update_option( 'SubscribeWithGoogle_products', "basic\npremium" );

		// Set product as free.
		$post_id = $this->factory->post->create();
		$this->go_to( "/?p=$post_id" );
		update_post_meta( $post_id, 'SubscribeWithGoogle_free', 'true' );

		$this->post_edit->render_post_edit_fields();
	}
}
