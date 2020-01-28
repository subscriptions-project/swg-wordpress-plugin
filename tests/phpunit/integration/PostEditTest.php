<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class PostEditTest extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		// Instantiate plugin.
		Plugin::load();
	}

	public function test__adds_admin_page() {
		global $wp_meta_boxes;

		Plugin::$instance->setup_post_edit_fields();

		$this->assertContains(
			'SubscribeWithGoogle_post-edit-metabox',
			array_keys( $wp_meta_boxes['post']['advanced']['high'] )
		);
	}
}
