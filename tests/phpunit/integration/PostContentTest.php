<?php

namespace SubscribeWithGoogle\WordPress\Tests;


use SubscribeWithGoogle\WordPress\Filters;
use WP_UnitTestCase;

class PostContentTest extends WP_UnitTestCase
{

	private $post_id = null;

	public function setUp()
	{
		parent::setUp();

		if ($this->post_id == null) {
			// Set publication ID site-wide.
			update_option('SubscribeWithGoogle_publication_id', 'example.com');

			// Create a post.
			$this->post_id = $this->factory->post->create();

			wp_update_post([
				'ID' => $this->post_id,
				'post_content' => '<h1>Hello world</h1> <span id="more-' . $this->post_id . '"></span> <p>Premium Only.</p>'
			]);
		}
	}


	public function test__does_not_include_hidden_content_if_post_is_not_free()
	{
		update_post_meta(
			$this->post_id,
			'SubscribeWithGoogle_product',
			'premium'
		);
		$this->go_to("/?p=" . $this->post_id);
		$content = Filters::the_content(get_post($this->post_id)->post_content);

		$this->assertNotRegexp('/\bPremium Only\b/', $content);
	}

	public function test__does_include_hidden_content_if_post_is_free()
	{
		update_post_meta(
			$this->post_id,
			'SubscribeWithGoogle_free',
			'true'
		);
		$this->go_to("/?p=" . $this->post_id);
		$content = Filters::the_content(get_post($this->post_id)->post_content);

		$this->assertRegexp('/\bPremium Only\b/', $content);
	}
}
