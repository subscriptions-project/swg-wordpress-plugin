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

final class RegisterWithGoogleSignIn {
	
	public function __construct()
	{
		add_action('login_head', array(__CLASS__, 'add_gsi_meta_tag'));
		add_action('rest_api_init', array(__CLASS__, 'register_rest_routes'));
	}
	
	/** Registers custom REST routes. */
	public static function register_rest_routes()
	{
		register_rest_route(
			'subscribe-with-google/v1',
			'/register-user',
			array(
				'methods'  => 'POST',
				'callback' => array(__CLASS__, 'register_or_login_user'),
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
		public static function register_or_login_user( $request ){
			
			Rest::verify_request_origin();
			
			$request_body = json_decode($request->get_body());
			$google_id = $request_body->google_id;
			
			$user_query = new WP_User_Query(array('meta_key' => 'google_id', 'meta_value' => $google_id));
			$existing_users = $user_query->get_results();
			
			if (count($existing_users)){
				self::loginUser(($existing_users[0]));
				return ['success' => true];
			}
			
			$userdata = array(
				'user_pass'             => $request_body->password,
				'user_login'            => $request_body->username,
				'user_nicename'         => $request_body->username,
				'user_email'            => $request_body->email,
				'show_admin_bar_front'  => 'false',
				'role'                  => 'Subscriber'
			);
			
			$new_user_id = wp_insert_user($userdata);
			update_user_meta($new_user_id, 'google_id', $google_id);
			$newUser = get_userdata($new_user_id);
			self::loginUser($newUser);
			
			return ['success' => true];
		}
		
		public static function loginUser($user){
			
			wp_set_current_user($user->id);
			wp_set_auth_cookie($user->id);
		}
		
		public static function googleSignInButtonHtml(){
			

			wp_enqueue_script(
				'subscribe-with-google',
				plugins_url('../dist/assets/js/main.js', __FILE__),
				null,
				1,
				true
			);
			
			$generatedPassword = wp_generate_password();
			$oauth_client_id = esc_attr(get_option(Plugin::key('oauth_client_id')));

			
			return <<<HTML
			<div id="g-signin2"></div>
			<hr/>
			HTML;
			
		}
		
		public static function add_gsi_meta_tag(){
			// Add meta tag for Google Sign In.
			$oauth_client_id = get_option( Plugin::key( 'oauth_client_id' ) );
			
			echo '<meta name="google-signin-client_id" content="'.esc_attr( $oauth_client_id ).'">';
		}
	}