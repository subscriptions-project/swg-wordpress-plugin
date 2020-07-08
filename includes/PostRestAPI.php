<?php
/**
 * Class SubscribeWithGoogle\WordPress\PostRestAPI
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

use Exception;
use WP_REST_Request;
use WP_Error;

/**
 * Override the REST API route that leads directly to the WP Post Content
 */
final class PostRestAPI {


	/**
	 * Identifier of GoogleSignIn class.
	 * Tests can override this.
	 *
	 * @var string
	 */
	public static $google_sign_in_class =
	'SubscribeWithGoogle\WordPress\GoogleSignIn';

	/** Adds action handlers. */
	public function __construct() {
		if ( ! is_admin() ) {
			add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
		}
	}

	/** Registers custom REST routes. */
	public static function register_rest_routes() {
		register_rest_route(
			'wp/v2',
			'/posts/(?P<id>\d+)',
			array(
				'methods'  => 'GET',
				'callback' => array( __CLASS__, 'get_post' ),
			)
		);
	}

	/**
	 * Check with the Get Entitlement API to see if this user has content before delivering it to them
	 *
	 * @param WP_REST_Request $request with `post ID`.
	 */
	public static function get_post( $request ) {

		$google_sign_in_client = new self::$google_sign_in_class();

		$post_ID = $request['id'];
		$query   = get_post( $post_ID );
		$content = apply_filters( 'the_content', $query->post_content );

		try {
			$entitlements_response = $google_sign_in_client::get_entitlements();
		} catch ( Exception $e ) {
			return new WP_Error( 'entitlement-check-error', __( 'You are not signed in to Google' ), array( 'status' => 500 ) );
		}

		$entitled_products_for_user = self::get_entitled_products_for_entitlements( $entitlements_response );

		$product_key = Plugin::key( 'product' );
		$product     = get_post_meta( $post_ID, $product_key, true );

		$free_key = Plugin::key( 'free' );
		$free     = get_post_meta( $post_ID, $free_key, true );
		$query    = get_post( $post_ID );
		$content  = apply_filters( 'the_content', $query->post_content );

		if ( $free ) {
			return $content;
		}

		$publication_id      = get_option( Plugin::key( 'publication_id' ) );
		$product_id_for_post = $publication_id . ':' . $product;

		if ( in_array( $product_id_for_post, $entitled_products_for_user, true ) || current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
			return $content;
		}

		return new WP_Error( 'missing_entitlements', __( 'You are not entitled to this content' ), array( 'status' => 401 ) );
	}

	/**
	 * Collect the products from all of the entitlements
	 *
	 * @param Object $entitlements The entitlements returned by the SwG API.
	 * @return String[] Array of strings representing product IDs, i.e. "example.com:basic".
	 */
	protected static function get_entitled_products_for_entitlements( $entitlements ) {
		$products = array();
		if ( isset( $entitlements->entitlements ) ) {
			foreach ( $entitlements->entitlements as $ent ) {
				$products = array_merge( $ent->products );
			}
		}

		return $products;
	}
}
