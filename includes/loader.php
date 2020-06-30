<?php

/**
 * Plugin config.
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2019 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

// Define global constants.
define('SUBSCRIBEWITHGOOGLE_PLUGIN_BASENAME', plugin_basename(SUBSCRIBEWITHGOOGLE_PLUGIN_MAIN_FILE));
define('SUBSCRIBEWITHGOOGLE_PLUGIN_DIR_PATH', plugin_dir_path(SUBSCRIBEWITHGOOGLE_PLUGIN_MAIN_FILE));

/**
 * Loads generated class maps for autoloading.
 *
 * @since 1.0.0
 * @access private
 */
function autoload_classes()
{
	$class_map = array_merge(
		// SwG classes.
		include SUBSCRIBEWITHGOOGLE_PLUGIN_DIR_PATH . 'includes/vendor/composer/autoload_classmap.php',
		// Third-party classes.
		include SUBSCRIBEWITHGOOGLE_PLUGIN_DIR_PATH . 'third-party/vendor/composer/autoload_classmap.php'
	);

	spl_autoload_register(
		function ($class) use ($class_map) {
			if (isset($class_map[$class]) && file_exists($class_map[$class])) {
				require_once $class_map[$class];

				return true;
			}
		},
		true,
		true
	);
}
autoload_classes();

/**
 * Loads files containing functions from generated file map.
 *
 * @since 1.0.0
 * @access private
 */
function autoload_vendor_files()
{
	// Third-party files.
	$files = require SUBSCRIBEWITHGOOGLE_PLUGIN_DIR_PATH . 'third-party/vendor/autoload_files.php';
	foreach ($files as $file_identifier => $file) {
		if (file_exists($file)) {
			require_once $file;
		}
	}
}
autoload_vendor_files();

// Initialize the plugin.
Plugin::load();

/**
 * WP CLI Commands
 */
if (defined('WP_CLI') && WP_CLI) {
	require_once SUBSCRIBEWITHGOOGLE_PLUGIN_DIR_PATH . 'bin/authentication-cli.php';
	require_once SUBSCRIBEWITHGOOGLE_PLUGIN_DIR_PATH . 'bin/reset-cli.php';
}
