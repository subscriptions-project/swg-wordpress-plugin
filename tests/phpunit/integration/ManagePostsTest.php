<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\ManagePosts;
use WP_UnitTestCase;

class ManagePostsTest extends WP_UnitTestCase {

	/**
	 * Creates a test post.
	 * 
	 * @param string $free as in beer. Ex: 'true', 'false', ''
	 * @return number Post ID.
	 */
	private function create_post( $free ) {
		$post_id = $this->factory->post->create();
		update_post_meta(
			$post_id,
			'SubscribeWithGoogle_free',
			$free
		);
		update_post_meta(
			$post_id,
			'SubscribeWithGoogle_product',
			'basic'
		);
		return $post_id;
	}

	public function test__manage_posts_columns__adds_swg_column() {
		$columns = ManagePosts::manage_posts_columns( array() );
		$this->assertEquals(
			array('swg_product' => 'SwG Product'),
			$columns
		);
	}

	public function test__manage_posts_custom_column__renders_free_swg_column() {
		$post_id = $this->create_post( 'true' );
		ManagePosts::manage_posts_custom_column( 'swg_product', $post_id );

		$this->expectOutputString( 'Free' );
	}

	public function test__manage_posts_custom_column__renders_paid_swg_column() {
		$post_id = $this->create_post( '' );
		ManagePosts::manage_posts_custom_column( 'swg_product', $post_id );

		$this->expectOutputString( 'basic' );
	}
}
