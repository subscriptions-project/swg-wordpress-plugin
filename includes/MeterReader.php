<?php
/**
 * Class SubscribeWithGoogle\WordPress\MeterReader
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/**
 * Determines how many free pageloads a user has left
 */
final class MeterReader {

	/**
	 * Attempt to add a "read" record for the url to the user's record.
	 * If this url has already been added for this user, do nothing
	 *
	 * @param String $url The url of the content resource being added.
	 * @param Int    $user_id The ID of the Subscriber user.
	 */
	public static function add_read_record_for_url_to_user( $url, $user_id ) {
		$url_record = get_user_meta( $user_id, Plugin::key( 'free_urls_accessed' ), true );
		if ( ! $url_record ) {
			$url_record = array();
		}

		if ( ! self::url_read_record_exists_for_user( $url, $user_id ) ) {
			$url_record[] = $url;
		}

		update_user_meta( $user_id, Plugin::key( 'free_urls_accessed' ), $url_record );

	}

	/**
	 * Retrieve the number of total read records for the user
	 *
	 * @param Int $user_id The ID of the Subscriber user.
	 */
	public static function get_read_record_count_for_user( $user_id ) {
		$url_record = get_user_meta( $user_id, Plugin::key( 'free_urls_accessed' ), true );
		return $url_record ? count( $url_record ) : 0;
	}

	/**
	 * Attempt to add a "read" record for the url to the user's record.
	 * If this url has already been added for this user, do nothing
	 *
	 * @param String $url The url of the content resource being added.
	 * @param Int    $user_id The ID of the Subscriber user.
	 * @return Bool True/false response to the question.
	 */
	public static function url_read_record_exists_for_user( $url, $user_id ) {
		$url_record = get_user_meta( $user_id, Plugin::key( 'free_urls_accessed' ), true );
		return in_array( $url, $url_record, true );
	}

}
