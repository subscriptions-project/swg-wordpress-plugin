<?php
/**
 * Class SubscribeWithGoogle\WordPress\ManagePosts
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/**
 * Adds SwG column to Manage Posts page.
 */
final class ManagePosts {

	/** Adds WordPress filters. */
	public function __construct() {
		add_filter( 'manage_posts_columns', array( __CLASS__, 'manage_posts_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'manage_posts_custom_column' ), 1, 2 );
	}

	/**
	 * Filters manage posts columns.
	 *
	 * @param string[] $columns Columns to filter.
	 * @return string[]
	 */
	public static function manage_posts_columns( $columns ) {
		$columns['swg_product'] = 'SwG Product';
		return $columns;
	}

	/**
	 * Renders content for custom columns.
	 *
	 * @param string $column_name Current column name.
	 * @param number $post_ID Current post ID.
	 */
	public static function manage_posts_custom_column( $column_name, $post_ID ) {
		if ( 'swg_product' === $column_name ) {
			$product_key = Plugin::key( 'product' );
			$product     = get_post_meta( $post_ID, $product_key );

			$free_key = Plugin::key( 'free' );
			$free     = get_post_meta( get_the_ID(), $free_key, true );

			if ( $product ) {
				if ( 'true' === $free ) {
					echo 'Free';
				} else {
					echo esc_attr( implode( ',', $product ) );
				}
			}
		}
	}
}
