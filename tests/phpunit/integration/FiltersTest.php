<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Filters;
use WP_UnitTestCase;

class FiltersTest extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		// Reset AMP var.
		global $is_amp;
		$is_amp = false;
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
		$result = Filters::the_content( $post_content );

		$this->assertEquals(
			$result,
			$post_content
		);
	}

	public function test__free_post__does_not_modify_content() {
		$post_content = $this->create_post('true');
		$result = Filters::the_content( $post_content );

		$this->assertEquals(
			$result,
			$post_content
		);
	}

	public function test__implicitly_paid_post__returns_filtered_content() {
		$post_content = $this->create_post('');
		$result = Filters::the_content( $post_content );

		$this->assertNotEquals(
			$result,
			$post_content
		);
		$this->assertContains(
			'swg--paywall-prompt',
			$result
		);
	}

	public function test__explicitly_paid_post__returns_filtered_content() {
		$post_content = $this->create_post('false');
		$result = Filters::the_content( $post_content );

		$this->assertNotEquals(
			$result,
			$post_content
		);
		$this->assertContains(
			'swg--paywall-prompt',
			$result
		);
	}

	public function test__signin_menu_link__gets_amp_attributes_added() {
		$result = Filters::wp_nav_menu_items(
			'<li><a href="#swg-signin">Sign in</a></li>'
		);

		$this->assertContains(
			'subscriptions-action=',
			$result
		);
	}

	public function test__body_class__on_non_single_post__does_not_modify() {
		$classes = array( 'sample-class' );
		$result = Filters::body_class( $classes );

		$this->assertEquals(
			$classes,
			$result
		);
	}

	public function test__body_class__on_non_amp_url__does_not_modify() {
		$this->create_post('');

		$classes = array( 'sample-class' );
		$result = Filters::body_class( $classes );

		$this->assertEquals(
			$classes,
			$result
		);
	}

	public function test__body_class__adds_amp_class() {
		global $is_amp;
		$is_amp = true;
		$this->create_post('');

		$classes = array( 'sample-class' );
		$result = Filters::body_class( $classes );

		$this->assertEquals(
			array_merge($classes, ['swg--is-amp']),
			$result
		);
	}
}
