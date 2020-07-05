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
		add_action( 'user_register', array( __CLASS__, 'user_was_created' ), 10, 1 );
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
			$content_segments[1] = self::paywallContentForSession( $content_segments );
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

	public static function user_was_created( $user_id ) {
		update_user_meta( $user_id, Plugin::key( 'free_urls_accessed' ), array() );
	}

	protected static function paywallContentForSession( $content_segments ) {
		if ( is_user_logged_in() ) {
			$url         = get_permalink();
			$user_id     = get_current_user_id();
			$remaining   = 10 - MeterReader::getReadRecordCountForUser( $user_id );
			$viewsPlural = $remaining == 1 ? 'view' : 'views';
			$loginText   = "You have ${remaining} ${viewsPlural} remaining";

			if ( $remaining > 0 || MeterReader::urlReadRecordExistsForUser( $url, $user_id ) ) {
				MeterReader::addReadRecordForUrlToUser( $url, $user_id );
				return <<<HTML
				<div class="meter-message">{$loginText}</div>
				$content_segments[1];
				HTML;
			}
		} else {
			$loginText = "<a href='/wp-login.php?action=register&continue=" . urlencode( get_permalink() ) . "'>Register an account</a> or <a href='/wp-login.php'>log in</a> to continue";
		}

		return <<<HTML
<p class="swg--paywall-checking-entitlements">
			Checking for entitlements...
</p>
<p class="swg--paywall-prompt" subscriptions-section="content-not-granted">
	🔒 <span>Subscribe to unlock the rest of this article.</span>
	<br />
	<br/>
	<span class="text-small">{$loginText}</span>
	<br />
	<br/>
	<button
		class="swg-button swg-subscribe-button"
		subscriptions-action="subscribe"
		subscriptions-display="true"
		subscriptions-service="subscribe.google.com">
	</button>
</p>
HTML;
	}
}
