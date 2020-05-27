<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use PHPUnit_Framework_TestCase;
use SubscribeWithGoogle\WordPress\Plugin;

class PluginTest extends PHPUnit_Framework_TestCase {

	public function test__load() {
		Plugin::$instance = null;
		Plugin::load();
		$this->assertNotNull( Plugin::$instance );
	}
}
