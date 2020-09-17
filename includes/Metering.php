<?php
/**
 * Class SubscribeWithGoogle\WordPress\Metering
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

use Exception;
use WP_REST_Response;

/**
 * Supports metering.
 */
final class Metering {

	/** Adds action handlers. */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
	}

	/** Registers custom REST routes. */
	public static function register_rest_routes() {
		register_rest_route(
			'subscribewithgoogle/v1',
			'/meter-user-state',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_meter_user_state' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Returns meter user state.
	 *
	 * Reads/creates a SECURE and HTTP-only cookie.
	 *
	 * @throws Exception When refresh token can't be fetched.
	 */
	public static function get_meter_user_state() {
		Rest::verify_request_origin();

		// Return meter user immediately, if possible.
		if (
			isset( $_COOKIE['swg_meter_user_id'] ) ||
			$_COOKIE['swg_meter_user_id']
			) {
			return array(
				'meter_user_id' => $_COOKIE['swg_meter_user_id'],
			);
		}

		// Create meter user.
		$meter_user_id = uniqid( 'swg-', true );

		// Set cookie.
		$expires = gmdate( 'D, j F Y H:i:s', time() + 3600 * 24 * 365 * 100 );
		return new WP_REST_Response(
			array(
				'meter_user_id' => $meter_user_id,
			),
			200,
			array(
				'Set-Cookie' =>
				'swg_meter_user_id=' . $meter_user_id . '; ' .
					'expires=' . $expires . ' GMT; ' .
					'path=/; ' .
					'secure; ' .
					'HttpOnly; ',
			)
		);
	}
}
