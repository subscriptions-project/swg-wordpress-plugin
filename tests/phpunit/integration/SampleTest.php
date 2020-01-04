<?php

namespace SubscribeWithGoogle\WordPress\Tests;

use SubscribeWithGoogle\WordPress\Plugin;

class SampleTest extends \WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
    }

    public function test_load_method() {
        // First we'll clear the global instance.
        Plugin::$instance = null;

        // The first execution should return true, since a new instance was created.
        $success = Plugin::load( SUBSCRIBEWITHGOOGLE_PLUGIN_MAIN_FILE );
        $this->assertEquals( $success, true );
        
        // The second execution should return false, since a new instance was not created.
        $success = Plugin::load( SUBSCRIBEWITHGOOGLE_PLUGIN_MAIN_FILE );
        $this->assertEquals( $success, false );
    }

    public function test_main_file_value() {
        $this->assertEquals(
            Plugin::$instance->main_file,
            SUBSCRIBEWITHGOOGLE_PLUGIN_MAIN_FILE
        );
    }
}
