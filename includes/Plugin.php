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

	/** Namespace for SwG variables. */
	const SWG_NAMESPACE = 'SubscribeWithGoogle_';

	/**
	 * Main instance of the plugin.
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	/** Creates the plugin. */
	public function __construct() {
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

		// Post create/edit pages.
		add_action( 'add_meta_boxes', array( $this, 'setup_post_edit_fields' ) );
		add_action( 'save_post', array( $this, 'setup_post_save' ) );

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
	}

	/**
	 * Shortcode for rendering a Subscribe button.
	 *
	 * @param array[string]string $atts Attributes affecting shortcode.
	 */
	public function shortcode_subscribe( $atts = [] ) {
		$play_offers = isset( $atts['play-offers'] ) ? $atts['play-offers'] : '';

		return '<button class="swg-button" data-play-offers="' . $play_offers . '"></button>';
	}

	/**
	 * Filters content on Post view pages.
	 *
	 * @param string $content Initial content of Post.
	 * @return string Filtered content of Post.
	 */
	public function filter_the_content( $content ) {
		// Check if we're inside the main loop in a single post page.
		if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Verify this post is supposed to be locked, even.
		// If it's free, just bail.
		$free_key = $this::SWG_NAMESPACE . 'free';
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
	<button class="swg-button"></button>
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
			'swgpress',
			plugins_url( 'swgpress.css', __FILE__ ),
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
			'swgpress',
			plugins_url( 'swgpress.js', __FILE__ ),
			null,
			1,
			true
		);

		$publication_id = get_option( $this::SWG_NAMESPACE . 'publication_id' );
		$products_str   = trim( get_option( $this::SWG_NAMESPACE . 'products' ) );
		if ( ! $products_str || ! $publication_id ) {
			return;
		}

		$products = array_map(
			function( $product ) {
				// TODO: Create a utility method that does this.
				return trim( $product );
			},
			explode( "\n", $products_str )
		);

		$product    = get_post_meta( get_the_ID(), $this::SWG_NAMESPACE . 'product', true );
		$product_id = $publication_id . ':' . $product;

		$is_free = get_post_meta( get_the_ID(), $this::SWG_NAMESPACE . 'free', true );
		$is_free = $is_free ? $is_free : 'false';

		// TODO: Add encrypted document key to head, once it's saved.
		?>
		<meta name="subscriptions-product-id" content="<?php echo esc_attr( $product_id ); ?>" />
		<meta name="subscriptions-accessible-for-free" content="<?php echo esc_attr( $is_free ); ?>" />
		<?php
	}

	/**
	 * Saves additional metadata when a Post is saved.
	 *
	 * @param string $post_id ID of the post being saved.
	 */
	public function setup_post_save( $post_id ) {
		// TODO: Can these key strings be saved somewhere more central? As class consts?
		$product_key = $this::SWG_NAMESPACE . 'product';
		$free_key    = $this::SWG_NAMESPACE . 'free';
		// phpcs:disable -- Might be a bug in one of the outdated WP linters?
		$product = $_POST[ $product_key ];
		$free = $_POST[ $free_key ] ? $_POST[ $free_key ] : 'false';
		$swg_nonce   = $_POST[ $this::SWG_NAMESPACE . '_nonce' ];
    // phpcs:enable

		// Verify settings nonce.
		if ( ! wp_verify_nonce( sanitize_key( $swg_nonce ), $this::SWG_NAMESPACE . '_saving_settings' ) ) {
			return;
		}

		// Product field.
		if ( isset( $product ) && '' !== $product ) {
			$value = sanitize_text_field( wp_unslash( $product ) );
			update_post_meta(
				$post_id,
				$product_key,
				$value
			);
		}

		// Free field.
		if ( isset( $free ) && '' !== $free ) {
			$value = sanitize_text_field( wp_unslash( $free ) );
			update_post_meta(
				$post_id,
				$free_key,
				$value
			);
		}
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
		// TODO: Should the admin settings page get its own class?
		add_settings_section( $this::SWG_NAMESPACE . 'configuration', 'Configuration', false, 'subscribe_with_google' );
		add_settings_section( $this::SWG_NAMESPACE . 'report', 'Statistics', false, 'subscribe_with_google' );
	}

	/** Adds fields to admin settings page. */
	public function setup_fields() {
		$fields = array(
			array(
				'uid'          => $this::SWG_NAMESPACE . 'publication_id',
				'label'        => 'Publication ID',
				'section'      => $this::SWG_NAMESPACE . 'configuration',
				'type'         => 'text',
				'options'      => false,
				'placeholder'  => 'your.publication.id',
				'helper'       => '',
				'supplemental' => 'Unique indentifier for your publication.',
				'default'      => '',
			),

			array(
				'uid'          => $this::SWG_NAMESPACE . 'products',
				'label'        => 'Product Names',
				'section'      => $this::SWG_NAMESPACE . 'configuration',
				'type'         => 'textarea',
				'options'      => false,
				'placeholder'  => "basic\npremium",
				'helper'       => '',
				'supplemental' => 'Products, one per line.',
				'default'      => '',
			),

			array(
				'uid'          => $this::SWG_NAMESPACE . 'chart',
				'label'        => 'Sample chart',
				'section'      => $this::SWG_NAMESPACE . 'report',
				'type'         => 'chart',
				'options'      => false,
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => 'TODO: Create sample chart.',
				'default'      => '',
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
		// Get the current value, if there is one.
		$value = get_option( $arguments['uid'] );
		if ( ! $value ) {
			$value = $arguments['default'];
		}

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

		// If there is help text.
		if ( $arguments['helper'] ) {
			printf( '<span class="helper"> %s</span>', esc_attr( $arguments['helper'] ) );
		}

		// If there is supplemental text.
		if ( $arguments['supplemental'] ) {
			printf( '<p class="description">%s</p>', esc_attr( $arguments['supplemental'] ) );
		}
	}

	/** Adds fields to Post edit page. */
	public function setup_post_edit_fields() {
		add_meta_box(
			$this::SWG_NAMESPACE . 'post-edit-metabox',
			'📰 Subscribe with Google',
			function() {

				$free_key     = $this::SWG_NAMESPACE . 'free';
				$product_key  = $this::SWG_NAMESPACE . 'product';
				$products_key = $this::SWG_NAMESPACE . 'products';
				$free         = get_post_meta( get_the_ID(), $free_key, true ) == 'true';
				$products_str = trim( get_option( $products_key ) );

				if ( $products_str ) {
					$selected_product = get_post_meta( get_the_ID(), $product_key, true );
					$products         = explode( "\n", $products_str );
					?>
					Product&nbsp;
					<select name="<?php echo esc_attr( $product_key ); ?>" id="<?php echo esc_attr( $product_key ); ?>">
						<?php
						foreach ( $products as $product ) {
							$product = trim( $product );
							?>
								<option value="<?php echo esc_attr( $product ); ?>"
								<?php echo ( $product == $selected_product ? 'selected' : '' ); ?>
								>
								<?php echo esc_html( $product ); ?>
								</option>
								<?php
						}
						?>
					</select>
					<br />
					<br />
					Is Free&nbsp;
					<?php
					if ( $free ) {
						?>
						<input id="<?php echo esc_attr( $free_key ); ?>" name="<?php echo esc_attr( $free_key ); ?>" type="checkbox" value="true" checked />
						<?php
					} else {
						?>
						<input id="<?php echo esc_attr( $free_key ); ?>" name="<?php echo esc_attr( $free_key ); ?>" type="checkbox" value="true" />
						<?php
					}
				} else {
					?>
					Lmao define some products bruh. <a href="<?php echo esc_url( admin_url( 'admin.php?page=subscribe_with_google' ) ); ?>">Link</a>
					<?php
				}
				// TODO: How can we generate the nonce to make it impossible to guess? What if we generated a random number when the plugin is activated, and saved it to the DB?
				wp_nonce_field( $this::SWG_NAMESPACE . '_saving_settings', $this::SWG_NAMESPACE . '_nonce' );
			},
			'post',
			'advanced',
			'high'
		);
	}

	/** Loads the plugin main instance and initializes it. */
	public static function load() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
	}
}
