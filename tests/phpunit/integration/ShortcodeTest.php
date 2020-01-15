<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class ShortcodeTest extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		// Instantiate plugin.
		Plugin::load();
	}

	public function test__with_play_offers() {
		$result = Plugin::$instance->shortcode_subscribe(
			array(
				'play-offers' => 'sku1, sku2'
			)
		);

		$this->assertEquals(
			$result,
			'<button class="swg-button" data-play-offers="sku1, sku2"></button>'
		);
	}

	public function test__without_play_offers() {
		$result = Plugin::$instance->shortcode_subscribe();

		$this->assertEquals(
			$result,
			'<button class="swg-button" data-play-offers=""></button>'
		);
	}
}
