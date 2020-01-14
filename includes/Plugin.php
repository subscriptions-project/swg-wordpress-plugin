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

	/** Namespace for SwG variables. */
	const SWG_NAMESPACE = 'SubscribeWithGoogle_';

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
	public function shortcode_subscribe( $atts = [] ) {
		$play_offers = $atts['play-offers'] ? $atts['play-offers'] : '';

		return '<button class="swg-button" data-play-offers="' . $play_offers . '"></button>';
	}

	/**
	 * Filters content on Post view pages.
	 *
	 * @param string $content Initial content of Post.
	 * @return string Filtered content of Post.
	 */
	public function filter_the_content( $content ) {
		// Check if we're inside the main loop in a single post page.
		if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Verify this post is supposed to be locked, even.
		// If it's free, just bail.
		$products_str = trim( get_option( $this::SWG_NAMESPACE . 'products' ) );
		if ( ! $products_str ) {
			return $content;
		}
		$products = array_map(
			function( $product ) {
				// TODO: Create a utility method that does this.
				return trim( $product );
			},
			explode( "\n", $products_str )
		);
		$meta_key = $this::SWG_NAMESPACE . 'product';
		$product  = get_post_meta( get_the_ID(), $meta_key, true );
		if ( ! $product || ! in_array( $product, $products ) ) {
			return $content;
		}

		$more_tag         = '<span id="more-' . get_the_ID() . '"></span>';
		$content_segments = explode( $more_tag, $content );

		// Add Paywall wrapper & prompt.
		if ( count( $content_segments ) > 1 ) {
			$content_segments[1] = '
<p class="swg-paywall-prompt">
	🔒 <span>Subscribe to unlock the rest of this article.</span>
	<br />
	<br />
	<button class="swg-button"></button>
</p>

<div class="swg-paywall">
' . $content_segments[1] . '
</div>
';
		}

		$content = implode( $more_tag, $content_segments );

		return $content;
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
