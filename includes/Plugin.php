<?php
/**
 * Class SubscribeWithGoogle\WordPress\Plugin
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2019 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/** Main class for the plugin. */
final class Plugin {

	/**
	 * Main instance of the plugin.
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	/** Creates the plugin. */
	public function __construct() {
		new EditPost();
		new Filters();
		new GoogleSignIn();
		new Header();
		new Shortcodes();

		$this->add_actions();
	}

	/** Adds WordPress actions. */
	private function add_actions() {
		// Admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_item' ) );

		// Admin page.
		add_action( 'admin_init', array( __CLASS__, 'setup_sections' ) );
		add_action( 'admin_init', array( __CLASS__, 'setup_fields' ) );
	}

	/** Adds admin menu item. */
	public static function add_admin_menu_item() {
		$page_title = 'Subscribe with Google';
		$menu_title = 'Subscribe with Google';
		$capability = 'manage_options';
		$slug       = 'subscribe_with_google';
		$callback   = array( __CLASS__, 'plugin_settings_page_content' );
		$icon       = 'dashicons-megaphone';
		$position   = 100;

		add_menu_page(
			$page_title,
			$menu_title,
			$capability,
			$slug,
			$callback,
			$icon,
			$position
		);
	}

	/** Renders the admin settings page. */
	public function plugin_settings_page_content() {
		?>
		<div class="wrap">
		<h2>Subscribe with Google</h2>
		<form method="post" action="options.php">
		<?php
		settings_fields( 'subscribe_with_google' );
		do_settings_sections( 'subscribe_with_google' );
		submit_button();
		?>
		</form>
		</div>
		<?php
	}

	/** Adds sections to admin settings page. */
	public function setup_sections() {
		add_settings_section( $this::key( 'configuration' ), 'Configuration', false, 'subscribe_with_google' );
	}

	/** Adds fields to admin settings page. */
	public function setup_fields() {
		$fields = array(
			array(
				'uid'          => $this::key( 'products' ),
				'label'        => 'Product Names',
				'section'      => $this::key( 'configuration' ),
				'type'         => 'textarea',
				'options'      => false,
				'placeholder'  => "basic\npremium",
				'helper'       => '',
				'supplemental' => 'Product names, one per line.',
				'default'      => '',
			),

			array(
				'uid'          => $this::key( 'publication_id' ),
				'label'        => 'Publication ID',
				'section'      => $this::key( 'configuration' ),
				'type'         => 'text',
				'options'      => false,
				'placeholder'  => 'your.publication.id',
				'supplemental' => 'Unique indentifier for your publication.',
			),

			array(
				'uid'          => $this::key( 'oauth_client_id' ),
				'label'        => 'OAuth Client ID',
				'section'      => $this::key( 'configuration' ),
				'type'         => 'text',
				'options'      => false,
				'placeholder'  => '',
				'supplemental' => 'Unique identifier for your Google OAuth Client.',
			),

			array(
				'uid'          => $this::key( 'oauth_client_secret' ),
				'label'        => 'OAuth Client Secret',
				'section'      => $this::key( 'configuration' ),
				'type'         => 'text',
				'options'      => false,
				'placeholder'  => '',
				'supplemental' => 'Secret key for your Google OAuth Client.',
			),
		);

		foreach ( $fields as $field ) {
			add_settings_field(
				$field['uid'],
				$field['label'],
				array( __CLASS__, 'field_callback' ),
				'subscribe_with_google',
				$field['section'],
				$field
			);

			register_setting( 'subscribe_with_google', $field['uid'] );
		}
	}

	/**
	 * Adds a settings field.
	 *
	 * @param array[string]string $arguments Describes how field should render.
	 */
	public function field_callback( $arguments ) {
		// Get the current value.
		$value = get_option( $arguments['uid'] );

		// Check which type of field we want.
		switch ( $arguments['type'] ) {
			case 'text':
				printf(
					'<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
					esc_attr( $arguments['uid'] ),
					esc_attr( $arguments['type'] ),
					esc_attr( $arguments['placeholder'] ),
					esc_attr( $value )
				);
				break;
			case 'textarea':
				printf(
					'<textarea style="min-height: 96px;" name="%1$s" id="%1$s" placeholder="%2$s">%3$s</textarea>',
					esc_attr( $arguments['uid'] ),
					esc_attr( $arguments['placeholder'] ),
					esc_attr( $value )
				);
				break;
			case 'chart':
				printf(
					'📊 📈'
				);
				break;
		}

		// If there is supplemental text.
		if ( isset( $arguments['supplemental'] ) ) {
			printf( '<p class="description">%s</p>', esc_attr( $arguments['supplemental'] ) );
		}
	}

	/** Loads the plugin main instance and initializes it. */
	public static function load() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
	}

	/**
	 * Returns a namespaced key.
	 *
	 * @param string $key Key to namespace.
	 * @return string Namespaced key.
	 */
	public static function key( $key ) {
		return 'SubscribeWithGoogle_' . $key;
	}

	/**
	 * Returns true if current endpoint is an AMP page.
	 *
	 * @return boolean
	 */
	public static function is_amp() {
		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
	}
}
