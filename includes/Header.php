<?php
/**
 * Class SubscribeWithGoogle\WordPress\Header
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/**
 * Adds to the <head> element on Post view pages.
 */
final class Header {

	/** Registers action. */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'modify' ) );
	}

	/** Adds to the <head> element on Post view pages. */
	public static function modify() {
		// Styles for SwgPress.
		wp_enqueue_style(
			'subscribe-with-google',
			plugins_url( '../dist/assets/css/main.css', __FILE__ ),
			null,
			1
		);

		if ( ! Plugin::is_amp() ) {
			// Google's API JavaScript library (https://github.com/google/google-api-javascript-client).
			wp_enqueue_script(
				'gapi-js',
				'https://apis.google.com/js/client:platform.js',
				null,
				1,
				true
			);

			// SwG's open-source JavaScript library (https://github.com/subscriptions-project/swg-js).
			wp_enqueue_script(
				'swg-js',
				'https://news.google.com/swg/js/v1/swg.js',
				null,
				1,
				true
			);

			// JavaScript for SwgPress.
			wp_enqueue_script(
				'subscribe-with-google',
				plugins_url( '../dist/assets/js/main.js', __FILE__ ),
				null,
				1,
				true
			);

			// Make WP URLs available to SwgPress' JavaScript.
			$api_base_url = get_option( 'siteurl' ) . '/wp-json/subscribewithgoogle/v1';
			wp_localize_script(
				'subscribe-with-google',
				'SubscribeWithGoogleWpGlobals',
				array( 'API_BASE_URL' => $api_base_url )
			);
		} else {
			// Add SwG's AMP extension.
			?>
			<script
				async
				custom-element="amp-subscriptions-google"
				src="https://cdn.ampproject.org/v0/amp-subscriptions-google-0.1.js"
			></script>
			<?php
		}

		// Add meta tags.
		$publication_id  = get_option( Plugin::key( 'publication_id' ) );
		$product         = get_post_meta( get_the_ID(), Plugin::key( 'product' ), true );
		$product_id      = $publication_id . ':' . $product;
		$oauth_client_id = get_option( Plugin::key( 'oauth_client_id' ) );
		$is_free         = get_post_meta( get_the_ID(), Plugin::key( 'free' ), true );
		$is_free         = $is_free ? $is_free : 'false';
		?>
		<meta name="subscriptions-product-id" content="<?php echo esc_attr( $product_id ); ?>" />
		<meta name="subscriptions-accessible-for-free" content="<?php echo esc_attr( $is_free ); ?>" />
		<meta name="google-signin-client_id" content="<?php echo esc_attr( $oauth_client_id ); ?>">

		<?php
		// Add JSON for AMP.
		$site_url          = get_option( 'siteurl' );
		$authorization_url = $site_url . '/wp-json/subscribewithgoogle/v1/grant-status?product=' . $product_id;
		$actions_login     = $site_url . '/wp-login.php';
		$actions_subscribe = $site_url;
		?>
		<script type="application/json" id="amp-subscriptions">
		{
			"services": [
				{
					"authorizationUrl": "<?php echo esc_js( $authorization_url ); ?>",
					"actions":{
						"login": "<?php echo esc_js( $actions_login ); ?>",
						"subscribe": "<?php echo esc_js( $actions_subscribe ); ?>"
					}
				},
				{
					"serviceId": "subscribe.google.com"
				}
			],
			"fallbackEntitlement": {
				"source": "fallback",
				"granted": true,
				"grantReason": "METERING"
			}
		}
		</script>
		<?php
	}
}
