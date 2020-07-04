<?php

namespace SubscribeWithGoogle\WordPress\Tests;


/** Mock of the GoogleSignIn class. */
class GoogleSignInMock
{

	public static $entitlements = [];

	public static function reset()
	{
		self::$entitlements = [];
	}

	public static function get_entitlements()
	{
		return self::$entitlements;
	}

	public static function verify_google_id_token($token){
		return json_decode(
			json_encode([
				'user_id' => '123456789'
			])
		);
	}
}
