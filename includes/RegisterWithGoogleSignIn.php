<?php
/**
 * Class SubscribeWithGoogle\WordPress\RegisterWithGoogleSignIn
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

use WP_REST_Request;
use WP_User;
use WP_User_Query;

/**
 * An implementation for one-click Google Sign In account creation and lookup.
 */
final class RegisterWithGoogleSignIn {

	/**
	 * Identifier of GoogleSignIn class.
	 * Tests can override this.
	 *
	 * @var string
	 */
	public static $google_sign_in_class =
	'SubscribeWithGoogle\WordPress\GoogleSignIn';

	/** Inject the login head scripts and initialize the rest routes */
	public function __construct() {
		add_action( 'login_head', array( __CLASS__, 'add_header_scripts' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
	}

	/** Registers custom REST routes. */
	public static function register_rest_routes() {
		register_rest_route(
			'subscribe-with-google/v1',
			'/register-user',
			array(
				'methods'  => 'POST',
				'callback' => array( __CLASS__, 'register_or_login_user' ),
			)
		);
	}

	/**
	 * Register the user with the Google ID.
	 * If the user already exists, update their data.
	 * Once the user has been found or created, log them in.
	 *
	 * @param WP_REST_Request $request with the incoming user data.
	 */
	public static function register_or_login_user( $request ) {

		Rest::verify_request_origin();

		$google_sign_in_client = new self::$google_sign_in_class();

		$request_body = json_decode( $request->get_body() );
		$id_token     = $request_body->google_id_token;
		$response     = $google_sign_in_client::verify_google_id_token( $id_token );

		$google_id = $response->user_id;

		$user_query     = new WP_User_Query(
			array(
				'meta_key'   => 'google_id',
				'meta_value' => $google_id,
			)
		);
		$existing_users = $user_query->get_results();

		if ( count( $existing_users ) ) {
			self::login_user( ( $existing_users[0] ) );
			return array( 'success' => true );
		}

		$userdata = array(
			'user_pass'            => wp_generate_password(),
			'user_login'           => $request_body->username,
			'user_nicename'        => $request_body->name,
			'user_email'           => $request_body->email,
			'show_admin_bar_front' => 'false',
			'role'                 => 'Subscriber',
		);

		$new_user_id = wp_insert_user( $userdata );
		update_user_meta( $new_user_id, 'google_id', $google_id );
		$new_user = get_userdata( $new_user_id );
		self::login_user( $new_user );

		return array( 'success' => true );
	}

	/**
	 * Log the provided user into the WP authentication system.
	 *
	 * @param WP_User $user The user to be logged in.
	 */
	public static function login_user( $user ) {
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
	}

	/**
	 * Render the HTML+JS required to display and handle the Google Sign In button.
	 *
	 * @return String HTML to display the button with proper handlers.
	 */
	public static function google_sign_in_button_html() {

		wp_enqueue_script(
			'google-gsi-platform',
			'https://apis.google.com/js/platform.js?onload=renderButton',
			null,
			1,
			true
		);

		wp_enqueue_script(
			'subscribe-with-google',
			plugins_url( '../dist/assets/js/main.js', __FILE__ ),
			null,
			1,
			true
		);

		return <<<HTML
			<div id="gsi-button" onclick="onClick()" data-onsuccess="onSignIn"></div>
			<br/>
HTML;

	}

	/** Handler for enqueuing the the scripts in the login page head we'll need to implement GSI */
	public static function add_header_scripts() {

		$oauth_client_id = esc_attr( get_option( Plugin::key( 'oauth_client_id' ) ) );
		?>
			<meta name="google-signin-client_id" content="<?php echo esc_html( $oauth_client_id ); ?>">

			<script>
				var wasClicked = false;

				function onClick(){
					wasClicked = true;
				}

				function onSuccess(googleUser) {
					if ( ! wasClicked) return;

					var google_id_token = googleUser.getAuthResponse().id_token;
					let profile = googleUser.getBasicProfile();
					let data = {
						google_id_token,
						username : profile.getEmail(),
						email : profile.getEmail(),
						name : profile.getName()
					}

					let url = "/wp-json/subscribe-with-google/v1/register-user"
					postData(url, data)
					.then(data => {
						console.log(data);
						let urlParams = new URLSearchParams(window.location.search);
						var continueUrl = urlParams.get('continue');
						if ( ! continueUrl) {
							continueUrl = "/";
						}
						window.location = continueUrl;

					});
					wasClicked = false;
				}

				async function postData(url = '', data = {}) {
					const response = await fetch(url, {
							method: 'POST',
							cache: 'no-cache',
							body: JSON.stringify(data)
						});
					return response.json();
				}

				function onFailure(error) {
					console.log(error);
				}
				function renderButton() {
				gapi.signin2.render('gsi-button', {
					'scope': 'profile email',
					'width': 270,
					'height': 50,
					'longtitle': true,
					'theme': 'light',
					'onsuccess': onSuccess,
					'onfailure': onFailure
				});
				}
			</script>
		<?php
	}
}
