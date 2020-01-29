<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class FilterTest extends \WP_UnitTestCase {

	public function setUp() {
		error_log( '🏃 FilterTest' );
		parent::setUp();

		// Instantiate plugin.
		Plugin::load();
	}
	
	/**
	 * Creates a test post.
	 * 
	 * @param string $free as in beer. Ex: 'true', 'false', ''
	 * @return string Post content.
	 */
	private function create_post( $free ) {
		$post_id = $this->factory->post->create();
		$post_content = '<p>In this post we&#8217;ll be revealing the best Alice in Chains album.</p><p><span id="more-' . $post_id . '"></span></p><p>Dirt, hands down.</p>';
		wp_update_post( array(
			'ID' => $post_id,
			'post_content' => $post_content,
		) );
		update_post_meta(
			$post_id,
			'SubscribeWithGoogle_free',
			$free
		);
		$this->go_to( '/?p=' . $post_id );
		return $post_content;
	}

	public function test__index_page__does_not_modify_content() {
		$post_content = $this->create_post('');
		$this->go_to("/posts");
		$result = Plugin::$instance->filter_the_content( $post_content );

		$this->assertEquals(
			$result,
			$post_content
		);
	}

	public function test__free_post__does_not_modify_content() {
		$post_content = $this->create_post('true');
		$result = Plugin::$instance->filter_the_content( $post_content );

		$this->assertEquals(
			$result,
			$post_content
		);
	}

	public function test__implicitly_paid_post__returns_filtered_content() {
		$post_content = $this->create_post('');
		$result = Plugin::$instance->filter_the_content( $post_content );

		$this->assertNotEquals(
			$result,
			$post_content
		);
		$this->assertContains(
			'swg-paywall-prompt',
			$result
		);
	}

	public function test__explicitly_paid_post__returns_filtered_content() {
		$post_content = $this->create_post('false');
		$result = Plugin::$instance->filter_the_content( $post_content );

		$this->assertNotEquals(
			$result,
			$post_content
		);
		$this->assertContains(
			'swg-paywall-prompt',
			$result
		);
	}
}
