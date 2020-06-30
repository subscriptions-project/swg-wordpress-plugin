<?php
/**
 * Class SubscribeWithGoogle\WordPress\Rest
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

use Exception;

/**
 * Supports REST APIs.
 */
final class Rest {

	/**
	 * Verifies the request originated from the WP site.
	 *
	 * @throws Exception When origin isn't valid.
	 */
	public static function verify_request_origin() {
		// Require referer.
		if (
			! isset( $_SERVER['HTTP_REFERER'] ) ||
			is_null( $_SERVER['HTTP_REFERER'] )
		) {
			throw new Exception( 'Request has no referer.' );
		}

		$request_url = wp_parse_url( $_SERVER['HTTP_REFERER'] );
		$site_url    = wp_parse_url( get_option( 'siteurl' ) );

		// Verify scheme.
		if ( $request_url['scheme'] !== $site_url['scheme'] ) {
			throw new Exception( 'Request scheme was not valid.' );
		}

		// Verify host.
		if ( $request_url['host'] !== $site_url['host'] ) {
			throw new Exception( 'Request host was not valid.' );
		}

		// Verify path.
		if ( strpos( $request_url['path'], $site_url['path'] ) !== 0 ) {
			throw new Exception( 'Request path did not belong to WP site.' );
		}
	}
}
