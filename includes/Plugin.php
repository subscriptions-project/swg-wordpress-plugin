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

	/**
	 * Main instance of the plugin.
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	/** Creates the plugin. */
	public function __construct() {
		new AdminPage();
		new EditPost();
		new Filters();
		new GoogleSignIn();
		new Header();
		new ManagePosts();
		new Shortcodes();
		new PostRestAPI();
	}

	/** Loads the plugin main instance and initializes it. */
	public static function load() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
	}

	/**
	 * Returns a namespaced key.
	 *
	 * @param string $key Key to namespace.
	 * @return string Namespaced key.
	 */
	public static function key( $key ) {
		return 'SubscribeWithGoogle_' . $key;
	}

	/**
	 * Returns true if current endpoint is an AMP page.
	 *
	 * @return boolean
	 */
	public static function is_amp() {
		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
	}
}
