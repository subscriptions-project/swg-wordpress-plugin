<?php
/**
 * PHP-Scoper configuration file.
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2019 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      https://developers.google.com/news/subscribe
 */

use Isolated\Symfony\Component\Finder\Finder;

return array(
	'prefix'                     => 'SubscribeWithGoogle\WordPress_Dependencies',
	'finders'                    => array(
		// General dependencies, except Google API services.
		Finder::create()
			->files()
			->ignoreVCS( true )
			->notName( '/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.(json|lock)/' )
			->exclude(
				array(
					'doc',
					'test',
					'test_old',
					'tests',
					'Tests',
					'vendor-bin',
				)
			)
			->path( '#^firebase/#' )
			->path( '#^google/apiclient/#' )
			->path( '#^google/auth/#' )
			->path( '#^guzzlehttp/#' )
			->path( '#^monolog/#' )
			->path( '#^psr/#' )
			->path( '#^ralouphie/#' )
			->path( '#^react/#' )
			->in( 'vendor' ),
	),
	'files-whitelist'            => array(
		// This dependency is a global function which should remain global.
		'vendor/ralouphie/getallheaders/src/getallheaders.php',
	),
	'whitelist'                  => array(),
	'whitelist-global-constants' => false,
	'whitelist-global-classes'   => false,
	'whitelist-global-functions' => false,
);
