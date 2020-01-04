<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class SampleTest extends \WP_UnitTestCase {
    public function setUp() {
        parent::setUp();

        $this->plugin = new Plugin( SUBSCRIBEWITHGOOGLE_PLUGIN_MAIN_FILE );
    }

    public function test_main_file_value() {
        $this->assertEquals( $this->plugin->main_file, SUBSCRIBEWITHGOOGLE_PLUGIN_MAIN_FILE );
    }
}
