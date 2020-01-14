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
		$this->add_actions();
		$this->add_filters();
		$this->add_shortcodes();
	}

	/** Adds WordPress actions. */
	private function add_actions() {
		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu_item' ) );

		// Admin page.
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );

		// Post create/edit pages.
		add_action( 'add_meta_boxes', array( $this, 'setup_post_edit_fields' ) );
		add_action( 'save_post', array( $this, 'setup_post_save' ) );

		// Post view pages.
		add_action( 'wp_head', array( $this, 'handle_wp_head' ) );
	}

	/** Adds WordPress filters. */
	private function add_filters() {
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );
	}

	/** Adds WordPress shortcodes. */
	private function add_shortcodes() {
		add_shortcode( 'swg-subscribe', array( $this, 'shortcode_subscribe' ) );
	}

	/**
	 * Shortcode for rendering a Subscribe button.
	 *
	 * @param array[string]string $atts Attributes affecting shortcode.
	 */
	private function shortcode_subscribe( $atts = [] ) {
		$play_offers = $atts['play-offers'] ? $atts['play-offers'] : '';

		return '<button class="swg-button" data-play-offers="' . $play_offers . '"></button>';
	}

	/** Adds admin menu item. */
	public function add_admin_menu_item() {
		$page_title = 'Subscribe with Google';
		$menu_title = 'Subscribe with Google';
		$capability = 'manage_options';
		$slug       = 'subscribe_with_google';
		$callback   = array( $this, 'plugin_settings_page_content' );
		$icon       = 'dashicons-megaphone';
		$position   = 100;

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

	/** Loads the plugin main instance and initializes it. */
	public static function load() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
	}
}
