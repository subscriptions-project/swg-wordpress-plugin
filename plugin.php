<?php
/**
 * Plugin main file.
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2019 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 *
 * @wordpress-plugin
 * Plugin Name: Subscribe with Google
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define most essential constants.
define( 'SUBSCRIBEWITHGOOGLE_VERSION', '0.0.1' );
define( 'SUBSCRIBEWITHGOOGLE_PLUGIN_MAIN_FILE', __FILE__ );

/**
 * Handles plugin activation.
 *
 * Throws an error if the plugin is activated on an older version than PHP 5.4.
 *
 * @access private
 *
 * @param bool $network_wide Whether to activate network-wide.
 */
function subscribewithgoogle_activate_plugin( $network_wide ) {
	if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
		wp_die(
			esc_html__( 'Subscribe with Google requires PHP version 5.4.', 'subscribe-with-google' ),
			esc_html__( 'Error Activating', 'subscribe-with-google' )
		);
	}

	if ( $network_wide ) {
		return;
	}

	do_action( 'subscribewithgoogle_activation', $network_wide );
}
register_activation_hook( __FILE__, 'subscribewithgoogle_activate_plugin' );

/**
 * Handles plugin deactivation.
 *
 * @access private
 *
 * @param bool $network_wide Whether to deactivate network-wide.
 */
function subscribewithgoogle_deactivate_plugin( $network_wide ) {
	if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
		return;
	}
	if ( $network_wide ) {
		return;
	}
	do_action( 'subscribewithgoogle_deactivation', $network_wide );
}
register_deactivation_hook( __FILE__, 'subscribewithgoogle_deactivate_plugin' );

if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/loader.php';
}
