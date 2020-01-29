<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\PostEdit;

class SavePostTest extends \WP_UnitTestCase {

	private $post_id = null;
	private $post_edit = null;

	public function setUp() {
		error_log( '🏃 SavePostTest' );
		parent::setUp();

		if ( $this->post_id == null ) {
			// Set publication ID site-wide.
			update_option( 'SubscribeWithGoogle_publication_id', 'example.com' );

			// Create a post.
			$this->post_id = $this->factory->post->create();
			wp_update_post( array(
				'ID' => $this->post_id,
				'post_content' => 'Hello world',
				'post_name' => 'paid-post',
				'post_title' => 'Paid post',
			) );

			// Visit the post.
			$this->go_to("/?p={$this->post_id}");
		}

		// Reset post meta.
		delete_post_meta(
			$this->post_id,
			'SubscribeWithGoogle_free'
		);
		delete_post_meta(
			$this->post_id,
			'SubscribeWithGoogle_product'
		);

		$this->post_edit = new PostEdit();
	}

	public function tearDown() {
		global $_POST;
		$_POST = array();
	}

	private function assertPostMeta( $key, $value ) {
		$actual = get_post_meta( $this->post_id, 'SubscribeWithGoogle_' . $key, true );
		$this->assertEquals( $value, $actual );
	}

	public function test__checks_nonce() {
		global $_POST;

		// Verify initial state.
		$this->assertPostMeta( 'free', '' );
		$this->assertPostMeta( 'product', '' );

		// Trigger failing updates.
		$nonce = 'Nonsensical nonce';
		$_POST = array(
			'SubscribeWithGoogle_nonce' => $nonce,
			'SubscribeWithGoogle_product' => 'premium',
			'SubscribeWithGoogle_free' => 'false'
		);
		$this->post_edit->save_post( $this->post_id );

		// Verify updates didn't happen.
		$this->assertPostMeta( 'free', '' );
		$this->assertPostMeta( 'product', '' );
	}

	public function test__updates_post_meta__when_free_is_unset() {
		global $_POST;

		// Verify initial state.
		$this->assertPostMeta( 'free', '' );
		$this->assertPostMeta( 'product', '' );
		
		// Trigger updates.
		$nonce = wp_create_nonce('SubscribeWithGoogle_saving_settings');
		$_POST = array(
			'SubscribeWithGoogle_nonce' => $nonce,
			'SubscribeWithGoogle_product' => 'premium',
		);
		$this->post_edit->save_post( $this->post_id );

		// Verify updates.
		$this->assertPostMeta( 'free', 'false' );
		$this->assertPostMeta( 'product', 'premium' );
	}

	public function test__updates_post_meta__when_free_is_true() {
		global $_POST;

		// Verify initial state.
		$this->assertPostMeta( 'free', '' );
		$this->assertPostMeta( 'product', '' );
		
		// Trigger updates.
		$nonce = wp_create_nonce('SubscribeWithGoogle_saving_settings');
		$_POST = array(
			'SubscribeWithGoogle_nonce' => $nonce,
			'SubscribeWithGoogle_product' => 'premium',
			'SubscribeWithGoogle_free' => 'true',
		);
		$this->post_edit->save_post( $this->post_id );

		// Verify updates.
		$this->assertPostMeta( 'free', 'true' );
		$this->assertPostMeta( 'product', 'premium' );
	}
}
