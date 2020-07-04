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
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
	}

	/** Registers custom REST routes. */
	public static function register_rest_routes() {
		register_rest_route(
			'subscribewithgoogle/v1',
			'/create-1p-gsi-cookie',
			array(
				'methods'  => 'POST',
				'callback' => array( __CLASS__, 'create_1p_gsi_cookie' ),
			)
		);

		register_rest_route(
			'subscribewithgoogle/v1',
			'/entitlements',
			array(
				'methods'  => 'GET',
				'callback' => array( __CLASS__, 'get_entitlements' ),
			)
		);

		register_rest_route(
			'subscribewithgoogle/v1',
			'/grant-status',
			array(
				'methods'  => 'GET',
				'callback' => array( __CLASS__, 'get_grant_status' ),
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
	public static function create_1p_gsi_cookie( $request ) {
		Rest::verify_request_origin();

		// Auth code is needed to get the refresh token.
		if ( ! isset( $request['gsi_auth_code'] ) ) {
			throw new Exception( 'gsi_auth_code POST param is missing.' );
		}

		// Get refresh token.
		$client   = self::create_client();
		$response = $client->fetchAccessTokenWithAuthCode( $request['gsi_auth_code'] );
		if ( ! isset( $response['refresh_token'] ) ) {
			throw new Exception(
				'Refresh token could not be fetched. ' .
					wp_json_encode( $response )
			);
		}
		$refresh_token = $response['refresh_token'];

		// Set cookie.
		$expires = gmdate( 'D, j F Y H:i:s', time() + 3600 * 24 * 365 * 100 );
		return new WP_REST_Response(
			null,
			200,
			array(
				'Set-Cookie' =>
				'swg_refresh_token=' . $refresh_token . '; ' .
					'expires=' . $expires . ' GMT; ' .
					'path=/; ' .
					'secure; ' .
					'HttpOnly; ',
			)
		);
	}

	/**
	 * Fetches entitlements based on GSI auth code.
	 *
	 * @return * Entitlements response.
	 */
	public static function get_entitlements() {
		Rest::verify_request_origin();

		$access_token   = self::fetch_access_token();
		$publication_id = get_option( Plugin::key( 'publication_id' ) );

		// Get entitlements.
		$entitlements_url = 'https://subscribewithgoogle.googleapis.com/v1/publications/' . $publication_id . '/entitlements?access_token=' . $access_token;
		$response         = wp_remote_get( $entitlements_url );
		return json_decode( $response['body'] );
	}

	/**
	 * Returns grant status for a given product.
	 *
	 * @param WP_REST_Request $request with `product`.
	 *
	 * @return * Grant status response.
	 */
	public static function get_grant_status( $request ) {
		try {
			$entitlements = self::get_entitlements()->entitlements;
		} catch ( Exception $e ) {
			$entitlements = null;
		}

		// Search for product in entitlements.
		$granted = false;
		if ( is_array( $entitlements ) ) {
			foreach ( $entitlements as $entitlement ) {
				if ( in_array( $request['product'], $entitlement->products, true ) ) {
					$granted = true;
					break;
				}
			}
		}

		return array(
			'granted'     => $granted,
			'grantReason' => 'SUBSCRIBER',
			'data'        => null,
		);
	}

	/**
	 * Gets an access token from Google.
	 *
	 * @throws Exception When access token can't be fetched.
	 * @return string Access token from Google.
	 */
	private static function fetch_access_token() {
		// Refresh token is needed to get the access token.
		if (
			! isset( $_COOKIE['swg_refresh_token'] ) ||
			! $_COOKIE['swg_refresh_token']
		) {
			throw new Exception( 'swg_refresh_token COOKIE was missing.' );
		}

		// Get access token.
		$client   = self::create_client();
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
	private static function create_client() {
		$oauth_client_id     = get_option( Plugin::key( 'oauth_client_id' ) );
		$oauth_client_secret = get_option( Plugin::key( 'oauth_client_secret' ) );
		$client              = new self::$google_client_class();
		$client->setClientId( $oauth_client_id );
		$client->setClientSecret( $oauth_client_secret );
		$client->setRedirectUri( 'postmessage' );
		return $client;
	}

	public static function verify_google_id_token($token){
		// I tried using the Google Client to validate the token, but it fails because of some
		// missing class, Math BigInteger, which doesn't seem to be fixed by the composer dependencies

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v1/tokeninfo?id_token='.$token);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);

		// stop if fails
		if (!$response) {
			// TODO: Add proper error handling
			die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		}

		// close curl resource to free up system resources 
		curl_close($ch);

		return json_decode($response);
	}
}
