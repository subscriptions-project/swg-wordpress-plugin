<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use PHPUnit_Framework_TestCase;
use SubscribeWithGoogle\WordPress\Shortcodes;

class ShortcodeTest extends PHPUnit_Framework_TestCase {

	private $shortcodes;

	public function setUp() {
		parent::setUp();

		$this->shortcodes = new Shortcodes;
	}

	public function test__subscribe__with_play_offers() {
		$result = $this->shortcodes->subscribe(
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
		$result = $this->shortcodes->subscribe();

		$this->assertEquals(
			$result,
			'<button class="swg-button swg-subscribe-button" data-play-offers=""></button>'
		);
	}

	public function test__contribute__with_play_offers() {
		$result = $this->shortcodes->contribute(
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
		$result = $this->shortcodes->contribute();

		$this->assertEquals(
			$result,
			'<button class="swg-contribute-button" data-play-offers="">Contribute with Google</button>'
		);
	}
}
