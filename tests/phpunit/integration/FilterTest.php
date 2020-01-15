<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class FilterTest extends \WP_UnitTestCase {

	const FILTERED_CONTENT = '<p>In this post we&#8217;ll be revealing the best Alice in Chains album.</p>
	<p><!--more--></p>
	<p>Dirt, hands down.</p>
	';

	public function setUp() {
		parent::setUp();

		// Instantiate plugin.
		Plugin::load();

		// Enables pretty permalinks.
		$this->set_permalink_structure( '%postname%' );

		// Create posts.
		$post_id = $this->factory->post->create();
		$post_content = $this->generate_post_content( $post_id );
		wp_update_post( array(
			'ID' => $post_id,
			'post_content' => $post_content,
			'post_name' => 'free-post',
			'post_title' => 'Free post',
		) );
		update_post_meta(
			$post_id,
			'SubscribeWithGoogle_free',
			'true'
		);

		$post_id = $this->factory->post->create();
		$post_content = $this->generate_post_content( $post_id );
		wp_update_post( array(
			'ID' => $post_id,
			'post_content' => $post_content,
			'post_name' => 'paid-post',
			'post_title' => 'Paid post',
		) );
		update_post_meta(
			$post_id,
			'SubscribeWithGoogle_free',
			'false'
		);

		$post_id = $this->factory->post->create();
		$post_content = $this->generate_post_content( $post_id );
		wp_update_post( array(
			'ID' => $post_id,
			'post_content' => $post_content,
			'post_name' => 'paid-post2',
			'post_title' => 'Paid post 2',
		) );

		// Visit the post.
		$this->go_to("/?p={$post_id}");
	}

	private function generate_post_content( $id ) {
		return '<p>In this post we&#8217;ll be revealing the best Alice in Chains album.</p>
<p><span id="more-' . $id . '"></span></p>
<p>Dirt, hands down.</p>
';
	}

	public function test__index_page__returns_initial_content() {
		$this->go_to("/posts");

		$initial_content = $this->generate_post_content(1);
		$result = Plugin::$instance->filter_the_content( $initial_content );

		$this->assertEquals(
			$result,
			$initial_content
		);
	}

	public function test__free_post__returns_initial_content() {
		$this->go_to("/free-post");

		$initial_content = get_post()->post_content;
		$result = Plugin::$instance->filter_the_content( $initial_content );

		$this->assertEquals(
			$result,
			$initial_content
		);
	}

	public function test__paid_post__returns_filtered_content() {
		$this->go_to("/paid-post");

		$initial_content = get_post()->post_content;
		$result = Plugin::$instance->filter_the_content( $initial_content );

		$this->assertNotEquals(
			$result,
			$initial_content
		);
		$this->assertContains(
			'swg-paywall-prompt',
			$result
		);
	}
}
