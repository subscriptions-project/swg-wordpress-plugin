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
		new PostEdit();

		$this->add_actions();
		$this->add_filters();
		$this->add_shortcodes();
	}

	/** Adds WordPress actions. */
	private function add_actions() {
		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu_item' ) );

		// Admin page.
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );

		// Post view pages.
		add_action( 'wp_head', array( $this, 'handle_wp_head' ) );
	}

	/** Adds WordPress filters. */
	private function add_filters() {
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );
	}

	/** Adds WordPress shortcodes. */
	private function add_shortcodes() {
		add_shortcode( 'swg-subscribe', array( $this, 'shortcode_subscribe' ) );
		add_shortcode( 'swg-contribute', array( $this, 'shortcode_contribute' ) );
	}

	/**
	 * Shortcode for rendering a Subscribe button.
	 *
	 * @param array[string]string $atts Attributes affecting shortcode.
	 */
	public function shortcode_subscribe( $atts = [] ) {
		$html = '<button class="swg-button swg-subscribe-button" data-play-offers="';
		if ( isset( $atts['play-offers'] ) ) {
			$html .= $atts['play-offers'];
		}
		$html .= '"></button>';
		return $html;
	}

	/**
	 * Shortcode for rendering a Contribute button.
	 *
	 * @param array[string]string $atts Attributes affecting shortcode.
	 */
	public function shortcode_contribute( $atts = [] ) {
		$html = '<button class="swg-contribute-button" data-play-offers="';
		if ( isset( $atts['play-offers'] ) ) {
			$html .= $atts['play-offers'];
		}
		$html .= '">Contribute with Google</button>';
		return $html;
	}

	/**
	 * Filters content on Post view pages.
	 *
	 * @param string $content Initial content of Post.
	 * @return string Filtered content of Post.
	 */
	public function filter_the_content( $content ) {
		// Check if we're inside the main loop in a single post page.
		if ( ! is_single() || ! is_main_query() ) {
			return $content;
		}

		// Verify this post is supposed to be locked, even.
		// If it's free, just bail.
		$free_key = $this::key( 'free' );
		$free     = get_post_meta( get_the_ID(), $free_key, true );
		if ( 'true' == $free ) {
			return $content;
		}

		$more_tag         = '<span id="more-' . get_the_ID() . '"></span>';
		$content_segments = explode( $more_tag, $content );

		// Add Paywall wrapper & prompt.
		if ( count( $content_segments ) > 1 ) {
			$content_segments[1] = '
<p class="swg-paywall-prompt">
	🔒 <span>Subscribe to unlock the rest of this article.</span>
	<br />
	<br />
	<button class="swg-button swg-subscribe-button"></button>
</p>

<div class="swg-paywall">
' . $content_segments[1] . '
</div>
';
		}

		$content = implode( $more_tag, $content_segments );

		return $content;
	}

	/** Adds admin menu item. */
	public function add_admin_menu_item() {
		$page_title = 'Subscribe with Google';
		$menu_title = 'Subscribe with Google';
		$capability = 'manage_options';
		$slug       = 'subscribe_with_google';
		$callback   = array( $this, 'plugin_settings_page_content' );
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

	/** Adds to the <head> element on Post view pages. */
	public function handle_wp_head() {
		// Styles for SwgPress.
		wp_enqueue_style(
			'subscribe-with-google',
			plugins_url( '../dist/assets/css/main.css', __FILE__ ),
			null,
			1
		);

		// SwG's open-source JavaScript library (https://github.com/subscriptions-project/swg-js).
		wp_enqueue_script(
			'swg-js',
			'https://news.google.com/swg/js/v1/swg.js',
			null,
			1,
			true
		);

		// JavaScript for SwgPress.
		wp_enqueue_script(
			'subscribe-with-google',
			plugins_url( '../dist/assets/js/main.js', __FILE__ ),
			null,
			1,
			true
		);

		$publication_id = get_option( $this::key( 'publication_id' ) );
		$product        = get_post_meta( get_the_ID(), $this::key( 'product' ), true );
		$product_id     = $publication_id . ':' . $product;

		$is_free = get_post_meta( get_the_ID(), $this::key( 'free' ), true );
		$is_free = $is_free ? $is_free : 'false';

		// TODO: Add encrypted document key to head, once it's saved.
		?>
		<meta name="subscriptions-product-id" content="<?php echo esc_attr( $product_id ); ?>" />
		<meta name="subscriptions-accessible-for-free" content="<?php echo esc_attr( $is_free ); ?>" />
		<?php
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
		add_settings_section( $this::key( 'report' ), 'Statistics', false, 'subscribe_with_google' );
	}

	/** Adds fields to admin settings page. */
	public function setup_fields() {
		$fields = array(
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
				'uid'          => $this::key( 'products' ),
				'label'        => 'Product Names',
				'section'      => $this::key( 'configuration' ),
				'type'         => 'textarea',
				'options'      => false,
				'placeholder'  => "basic\npremium",
				'helper'       => '',
				'supplemental' => 'Products, one per line.',
				'default'      => '',
			),

			array(
				'uid'          => $this::key( 'chart' ),
				'label'        => 'Sample chart',
				'section'      => $this::key( 'report' ),
				'type'         => 'chart',
				'options'      => false,
				'placeholder'  => '',
				'supplemental' => 'TODO: Create sample chart.',
			),
		);

		foreach ( $fields as $field ) {
			add_settings_field(
				$field['uid'],
				$field['label'],
				array( $this, 'field_callback' ),
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
}
