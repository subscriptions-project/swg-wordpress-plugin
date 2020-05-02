<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;
use WP_UnitTestCase;

class ShortcodeTest extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		// Instantiate plugin.
		Plugin::load();
	}

	public function test__subscribe__with_play_offers() {
		$result = Plugin::$instance->shortcode_subscribe(
			array(
				'play-offers' => 'sku1, sku2'
			)
		);

		$this->assertEquals(
			$result,
			'<button class="swg-button swg-subscribe-button" data-play-offers="sku1, sku2"></button>'
		);
	}

	public function test__subscribe__without_play_offers() {
		$result = Plugin::$instance->shortcode_subscribe();

		$this->assertEquals(
			$result,
			'<button class="swg-button swg-subscribe-button" data-play-offers=""></button>'
		);
	}

	public function test__contribute__with_play_offers() {
		$result = Plugin::$instance->shortcode_contribute(
			array(
				'play-offers' => 'sku1, sku2'
			)
		);

		$this->assertEquals(
			$result,
			'<button class="swg-contribute-button" data-play-offers="sku1, sku2">Contribute with Google</button>'
		);
	}

	public function test__contribute__without_play_offers() {
		$result = Plugin::$instance->shortcode_contribute();

		$this->assertEquals(
			$result,
			'<button class="swg-contribute-button" data-play-offers="">Contribute with Google</button>'
		);
	}
}
