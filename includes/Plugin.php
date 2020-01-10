<?php
/**
 * Class SubscribeWithGoogle\WordPress\Plugin
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2019 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/** Main class for the plugin. */
final class Plugin {

	/** Main instance of the plugin. */
	public static $instance = null;

	/** Creates the plugin. */
	public function __construct() {
		$this->add_actions();
	}

	/** Adds WordPress actions. */
	private function add_actions() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu_item' ) );
	}

	/** Adds admin menu item. */
	public function add_admin_menu_item() {
		$page_title = 'Subscribe with Google';
		$menu_title = 'Subscribe with Google';
		$capability = 'manage_options';
		$slug = 'subscribe_with_google';
		$callback = array( $this, 'plugin_settings_page_content' );
		$icon = 'dashicons-megaphone';
		$position = 100;

		add_menu_page(
			$page_title,
			$menu_title,
			$capability,
			$slug,
			$callback,
			$icon,
			$position
		);
	}

	/**
	 * Loads the plugin main instance and initializes it.
	 *
	 * @return bool True if the plugin main instance could be loaded, false otherwise.
	 */
	public static function load() {
		if ( null !== static::$instance ) {
			return false;
		}

		static::$instance = new static();
		return true;
	}
}
