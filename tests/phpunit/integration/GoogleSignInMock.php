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
}
