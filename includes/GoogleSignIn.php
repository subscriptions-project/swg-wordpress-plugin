<?php
/**
 * Class SubscribeWithGoogle\WordPress\GoogleSignIn
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

use Exception;
use WP_REST_Response;

/**
 * Supports signing in with Google.
 */
final class GoogleSignIn {

	/**
	 * Identifier of Google Client class.
	 * Tests can override this.
	 *
	 * @var string
	 */
	public static $google_client_class =
		'SubscribeWithGoogle\WordPress_Dependencies\Google_Client';

	/** Adds action handlers. */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/** Registers custom REST routes. */
	public function register_rest_routes() {
		register_rest_route(
			'subscribewithgoogle/v1',
			'/create-1p-cookie',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'create_1p_cookie' ),
			)
		);

		register_rest_route(
			'subscribewithgoogle/v1',
			'/entitlements',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_entitlements' ),
			)
		);
	}

	/**
	 * Sets a cookie containing a refresh token.
	 *
	 * The cookie will be SECURE and HTTP-only, for Safari's sake.
	 *
	 * @param WP_REST_Request $request with `gsi_auth_code`.
	 *
	 * @throws Exception When refresh token can't be fetched.
	 */
	public function create_1p_cookie( $request ) {
		// Auth code is needed to get the refresh token.
		if ( ! isset( $request['gsi_auth_code'] ) ) {
			throw new Exception( 'gsi_auth_code POST param is missing' );
		}

		// Get refresh token.
		$client   = $this->create_client();
		$response = $client->fetchAccessTokenWithAuthCode( $request['gsi_auth_code'] );
		if ( ! isset( $response['refresh_token'] ) ) {
			throw new Exception(
				'Refresh token could not be fetched. ' .
				wp_json_encode( $response )
			);
		}
		$refresh_token = $response['refresh_token'];

		// Set cookie.
		$expires = date( 'D, j F Y H:i:s', time() + 3600 * 24 * 365 * 100 );
		return new WP_REST_Response(null, 200, array(
			'Set-Cookie' =>
				'swg_refresh_token=' . $refresh_token . '; ' .
				'expires=' . $expires . ' GMT; ' .
				'path=/; ' .
				'secure; ' .
				'HttpOnly; ',
		));
	}

	/**
	 * Fetches entitlements based on GSI auth code.
	 *
	 * @return string Entitlements response.
	 */
	public function get_entitlements() {
		$access_token = $this->fetch_access_token();

		// Get entitlements.
		$entitlements_url = 'https://subscribewithgoogle.googleapis.com/v1/publications/scenic-2017.appspot.com/entitlements?access_token=' . $access_token;
		$response         = wp_remote_get( $entitlements_url );
		return json_decode( $response['body'] );
	}

	/**
	 * Gets an access token from Google.
	 *
	 * @throws Exception When access token can't be fetched.
	 * @return string Access token from Google.
	 */
	private function fetch_access_token() {
		// Refresh token is needed to get the access token.
		if (
			! isset( $_COOKIE['swg_refresh_token'] ) ||
			! $_COOKIE['swg_refresh_token']
		) {
			throw new Exception( 'swg_refresh_token COOKIE was missing' );
		}

		// Get access token.
		$client   = $this->create_client();
		$response = $client->fetchAccessTokenWithRefreshToken( $_COOKIE['swg_refresh_token'] );
		if ( ! isset( $response['access_token'] ) ) {
			throw new Exception(
				'Access token could not be fetched. ' .
				wp_json_encode( $response )
			);
		}
		return $response['access_token'];
	}

	/** Creates a Google API client. */
	private function create_client() {
		$oauth_client_id     = get_option( Plugin::key( 'oauth_client_id' ) );
		$oauth_client_secret = get_option( Plugin::key( 'oauth_client_secret' ) );
		$client              = new $this::$google_client_class();
		$client->setClientId( $oauth_client_id );
		$client->setClientSecret( $oauth_client_secret );
		$client->setRedirectUri( 'postmessage' );
		return $client;
	}
}
