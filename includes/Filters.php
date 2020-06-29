<?php
/**
 * Class SubscribeWithGoogle\WordPress\Filters
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/**
 * Adds filters.
 */
final class Filters {

	/** Adds WordPress filters. */
	public function __construct() {
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'the_content', array( __CLASS__, 'the_content' ) );
		add_filter( 'wp_nav_menu_items', array( __CLASS__, 'wp_nav_menu_items' ) );
		add_filter( 'manage_posts_columns', array( __CLASS__, 'swg_columns_head' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'swg_columns_content' ), 1, 2 );
	}

	/**
	 * Filters body classes on Post view pages.
	 *
	 * @param string[] $classes already assigned to body.
	 */
	public static function body_class( $classes ) {
		// Check if we're inside the main loop in a single post page.
		if ( ! is_single() || ! is_main_query() ) {
			return $classes;
		}

		// Check that we're in AMP mode.
		if ( ! Plugin::is_amp() ) {
			return $classes;
		}

		$classes[] = 'swg--is-amp';
		return $classes;
	}

	/**
	 * Filters content on Post view pages.
	 *
	 * @param string $content Initial content of Post.
	 * @return string Filtered content of Post.
	 */
	public static function the_content( $content ) {
		// Check if we're inside the main loop in a single post page.
		if ( ! is_single() || ! is_main_query() ) {
			return $content;
		}

		// Verify this post is supposed to be locked, even.
		// If it's free, just bail.
		$free_key = Plugin::key( 'free' );
		$free     = get_post_meta( get_the_ID(), $free_key, true );
		if ( 'true' === $free ) {
			return $content;
		}

		$more_tag         = '<span id="more-' . get_the_ID() . '"></span>';
		$content_segments = explode( $more_tag, $content );

		// Add Paywall wrapper & prompt.
		if ( count( $content_segments ) > 1 ) {
			$content_segments[1] = '
<p class="swg--paywall-prompt" subscriptions-section="content-not-granted">
	🔒 <span>Subscribe to unlock the rest of this article.</span>
	<br />
	<br />
	<button
		class="swg-button swg-subscribe-button"
		subscriptions-action="subscribe"
		subscriptions-display="true"
		subscriptions-service="subscribe.google.com">
	</button>
</p>

<div class="swg--locked-content" subscriptions-section="content">
' . $content_segments[1] . '
</div>
';
		}

		$content = implode( $more_tag, $content_segments );

		return $content;
	}

	/**
	 * Filters menu items HTML.
	 *
	 * @param string $menu_html HTML of a menu.
	 */
	public static function wp_nav_menu_items( $menu_html ) {
		$swg_signin_link = 'href="#swg-signin"';
		$menu_html       = str_replace(
			$swg_signin_link,
			$swg_signin_link . ' subscriptions-action="login" subscriptions-display="NOT data.isLoggedIn"',
			$menu_html
		);
		return $menu_html;
	}

	// ADD NEW COLUMN
	public static function swg_columns_head( $defaults ) {
		$defaults['swg_product'] = 'SwG Product';
		return $defaults;
	}

	// SHOW THE FEATURED IMAGE
	public static function swg_columns_content( $column_name, $post_ID ) {
		if ( $column_name == 'swg_product' ) {
			$product_key = Plugin::key( 'product' );
			$product     = get_post_meta( $post_ID, $product_key );

			if ( $product ) {
				echo implode( ',', $product );
			}
		}
	}
}
