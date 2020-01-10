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
        $success = Plugin::load();
        $this->assertEquals( $success, true );
        
        // The second execution should return false, since a new instance was not created.
        $success = Plugin::load();
        $this->assertEquals( $success, false );
    }

    /** Admin menu should be added. */
    public function test_admin_menu() {
        do_action( 'admin_menu' );
        $this->assertNotEmpty( menu_page_url( 'subscribe_with_google', false ) );
    }
}
