<?php
/**
 * Class SubscribeWithGoogle\WordPress\PostEdit
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2019 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/** Supports editing of posts. */
final class PostEdit {

	/** Creates the plugin. */
	public function __construct() {
		// Render meta box on Post Edit page.
		add_action( 'add_meta_boxes', array( $this, 'setup_post_edit_fields' ) );

		// Handle Posts being saved.
		add_action( 'save_post', array( $this, 'handle_save_post' ) );
	}

	/** Adds fields to Post edit page. */
	public function setup_post_edit_fields() {
		add_meta_box(
			Plugin::key( 'post-edit-metabox' ),
			'📰 Subscribe with Google',
			array( $this, 'render_post_edit_fields' ),
			'post',
			'advanced',
			'high'
		);
	}

	/** Renders post edit fields. */
	public function render_post_edit_fields() {
		$free_key     = Plugin::key( 'free' );
		$product_key  = Plugin::key( 'product' );
		$products_key = Plugin::key( 'products' );
		$free         = get_post_meta( get_the_ID(), $free_key, true ) == 'true';
		$products_str = trim( get_option( $products_key ) );

		if ( ! $products_str ) {
			echo 'Please define products on the SwG setup page 😄. ';
			echo '<a href="';
			echo esc_url( admin_url( 'admin.php?page=subscribe_with_google' ) );
			echo '">Link</a>';
			return;
		}

		// Products dropdown.
		echo 'Product&nbsp; ';
		echo '<select';
		echo ' name="' . esc_attr( $product_key ) . '"';
		echo ' id="' . esc_attr( $product_key ) . '"';
		echo '>';
		$selected_product = get_post_meta( get_the_ID(), $product_key, true );
		$products         = explode( "\n", $products_str );
		$this::render_post_edit_product_options( $products, $selected_product );
		echo '</select>';
		echo '<br />';
		echo '<br />';

		// Free checkbox.
		echo 'Is Free&nbsp; ';
		echo '<input';
		echo ' id="' . esc_attr( $free_key ) . '"';
		echo ' name="' . esc_attr( $free_key ) . '"';
		echo ' type="checkbox"';
		echo ' value="true"';
		if ( $free ) {
			echo ' checked';
		}
		echo '/>';

		wp_nonce_field( Plugin::key( 'saving_settings' ), Plugin::key( 'nonce' ) );
	}

	/**
	 * Renders options for the post edit page's products dropdown.
	 * 
	 * @param array[string] $products that are rendered as options.
	 * @param string        $selected_product that is the initial selected option.
	 */
	private static function render_post_edit_product_options( $products, $selected_product ) {
		foreach ( $products as $product ) {
			$product = trim( $product );
			echo '<option';
			echo ' value="' . esc_attr( $product ) . '"';
			if ( $product == $selected_product ) {
				echo ' selected';
			}
			echo '>';
			echo esc_html( $product );
			echo '</option>';
		}
	}

	/**
	 * Saves additional metadata when a Post is saved.
	 *
	 * @param string $post_id ID of the post being saved.
	 */
	public function handle_save_post( $post_id ) {
		$product_key = Plugin::key( 'product' );
		$free_key    = Plugin::key( 'free' );
		$nonce_key   = Plugin::key( 'nonce' );
		// phpcs:disable
		// There might be a bug in one of the WP linters.
		// We can't upgrade them while supporting older versions of PHP it seems.
		if (
			! isset( $_POST[ $nonce_key ] ) ||
			! isset( $_POST[ $product_key ] )
		) {
			return;
		}
		$product   = $_POST[ $product_key ];
		$free      = isset( $_POST[ $free_key ] ) ? $_POST[ $free_key ] : 'false';
		$swg_nonce = $_POST[ $nonce_key ];
		// phpcs:enable

		// Verify settings nonce.
		if ( ! wp_verify_nonce( sanitize_key( $swg_nonce ), Plugin::key( 'saving_settings' ) ) ) {
			return;
		}

		// Product field.
		$value = sanitize_text_field( wp_unslash( $product ) );
		update_post_meta(
			$post_id,
			$product_key,
			$value
		);

		// Free field.
		$value = sanitize_text_field( wp_unslash( $free ) );
		update_post_meta(
			$post_id,
			$free_key,
			$value
		);
	}
}
