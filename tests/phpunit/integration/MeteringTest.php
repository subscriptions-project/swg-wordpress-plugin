<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use WP_UnitTestCase;
use SubscribeWithGoogle\WordPress\Plugin;
use SubscribeWithGoogle\WordPress\Filters;
use SubscribeWithGoogle\WordPress\GoogleSignIn;
use SubscribeWithGoogle\WordPress\RegisterWithGoogleSignIn;
use WP_REST_Request;
use WP_REST_Server;

class MeteringTest extends WP_UnitTestCase
{

	protected $post_ids = [];

	public function setUp()
	{
		parent::setUp();

		if (count($this->post_ids) == 0) {

			for($i = 0; $i < 3; $i++){
				$this->post_ids[] = $this->factory->post->create();
				wp_update_post([
					'ID' => $this->post_ids[$i],
					'post_content' => '<h1>Hello world</h1> <span id="more-' . $this->post_ids[$i] . '"></span> <p>Premium Only.</p>'
				]);
				update_post_meta(
					$this->post_ids[$i],
					'SubscribeWithGoogle_product',
					'basic'
				);
				update_post_meta(
					$this->post_ids[$i],
					'SubscribeWithGoogle_free',
					false
				);
			}
		}
	}

	public function test__it_shows_the_article_count_remaining_for_a_user_who_is_logged_in(){
		
		$user_id = wp_create_user('Test Name', 'password', 'email@example.com');
		$user = get_user_by('id', $user_id);
		RegisterWithGoogleSignIn::loginUser($user);

		$this->go_to("/?p=" . $this->post_ids[0]);
		$content = Filters::the_content(get_post($this->post_ids[0])->post_content);

		$this->assertRegexp('/\bYou have 10 views remaining\b/', $content);
		
	}

	public function test__it_reduces_the_meter_when_the_user_loads_more_content(){
		
		$user_id = wp_create_user('Test Name', 'password', 'email@example.com');
		$user = get_user_by('id', $user_id);
		RegisterWithGoogleSignIn::loginUser($user);

		$this->go_to("/?p=" . $this->post_ids[0]);
		Filters::the_content(get_post($this->post_ids[0])->post_content);
		$this->go_to("/?p=" . $this->post_ids[1]);
		Filters::the_content(get_post($this->post_ids[1])->post_content);
		$this->go_to("/?p=" . $this->post_ids[2]);
		$content = Filters::the_content(get_post($this->post_ids[2])->post_content);


		$this->assertRegexp('/\bYou have 8 views remaining\b/', $content);
		
	}

	public function test__it_does_not_reduce_the_meter_when_the_user_loads_the_same_content_repeatedly(){
		
		$user_id = wp_create_user('Test Name', 'password', 'email@example.com');
		update_user_meta( $user_id, Plugin::key( 'free_articles_remaining' ), 10 );
		$user = get_user_by('id', $user_id);
		RegisterWithGoogleSignIn::loginUser($user);

		$this->go_to("/?p=" . $this->post_ids[0]);
		Filters::the_content(get_post($this->post_ids[0])->post_content);
		$this->go_to("/?p=" . $this->post_ids[0]);
		Filters::the_content(get_post($this->post_ids[0])->post_content);
		$this->go_to("/?p=" . $this->post_ids[0]);
		$content = Filters::the_content(get_post($this->post_ids[0])->post_content);


		$this->assertRegexp('/\bYou have 9 views remaining\b/', $content);
		
	}

	
}
